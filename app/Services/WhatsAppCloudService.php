<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class WhatsAppCloudService
{
    public function __construct(
        private readonly MetricAggregationService $metrics,
    ) {
    }

    public function status(): array
    {
        $verifyToken = (string) config('services.whatsapp.verify_token');
        $appSecret = (string) config('services.whatsapp.app_secret');
        $accessToken = (string) config('services.whatsapp.access_token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');

        return [
            'inbound_configured' => $verifyToken !== '' && $appSecret !== '',
            'outbound_configured' => $accessToken !== '' && $phoneNumberId !== '',
            'fully_configured' => $verifyToken !== ''
                && $appSecret !== ''
                && $accessToken !== ''
                && $phoneNumberId !== '',
            'graph_version' => (string) config('services.whatsapp.graph_version', 'v25.0'),
        ];
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        $appSecret = (string) config('services.whatsapp.app_secret');
        if ($appSecret === '' || ! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expected, $signature);
    }

    public function processWebhook(array $payload): array
    {
        if (($payload['object'] ?? null) !== 'whatsapp_business_account') {
            return ['messages' => 0, 'statuses' => 0, 'duplicates' => 0];
        }

        $result = ['messages' => 0, 'statuses' => 0, 'duplicates' => 0];
        $changedLeads = false;

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? null) !== 'messages') {
                    continue;
                }

                $value = $change['value'] ?? [];
                $profileNames = [];
                foreach ($value['contacts'] ?? [] as $contact) {
                    $waId = $this->normalizePhone($contact['wa_id'] ?? null);
                    if ($waId) {
                        $profileNames[$waId] = trim((string) ($contact['profile']['name'] ?? ''));
                    }
                }

                foreach ($value['messages'] ?? [] as $message) {
                    $processed = $this->processIncomingMessage(
                        $message,
                        $profileNames,
                        $value['metadata'] ?? [],
                    );
                    $result[$processed ? 'messages' : 'duplicates']++;
                    $changedLeads = $changedLeads || $processed;
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    if ($this->processStatus($status)) {
                        $result['statuses']++;
                    }
                }
            }
        }

        if ($changedLeads) {
            $this->metrics->recalculateForDataSource('leads', 'marketing');
        }

        return $result;
    }

    public function sendText(Lead $lead, User $actor, string $body): LeadActivity
    {
        $this->ensureOutboundConfigured();
        $this->ensureLeadPhone($lead);

        $withinSupportWindow = $lead->activities()
            ->where('channel', 'whatsapp')
            ->where('direction', 'inbound')
            ->where('occurred_at', '>=', now()->subHours(24))
            ->exists();

        if (! $withinSupportWindow) {
            throw ValidationException::withMessages([
                'body' => 'Jendela layanan 24 jam sudah berakhir. Gunakan template WhatsApp yang telah disetujui Meta.',
            ]);
        }

        $response = $this->postMessage([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $lead->phone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ]);

        return $this->recordOutbound(
            $lead,
            $actor,
            $body,
            'text',
            $response,
        );
    }

    public function sendTemplate(
        Lead $lead,
        User $actor,
        string $templateName,
        string $language,
        array $parameters = [],
    ): LeadActivity {
        $this->ensureOutboundConfigured();
        $this->ensureLeadPhone($lead);

        $template = [
            'name' => $templateName,
            'language' => ['code' => $language],
        ];

        if ($parameters !== []) {
            $template['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn (string $parameter): array => [
                        'type' => 'text',
                        'text' => $parameter,
                    ],
                    $parameters,
                ),
            ]];
        }

        $response = $this->postMessage([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $lead->phone,
            'type' => 'template',
            'template' => $template,
        ]);

        return $this->recordOutbound(
            $lead,
            $actor,
            "[Template: {$templateName}]",
            'template',
            $response,
            ['template_name' => $templateName, 'language' => $language],
        );
    }

    private function processIncomingMessage(
        array $message,
        array $profileNames,
        array $metadata,
    ): bool {
        $messageId = trim((string) ($message['id'] ?? ''));
        $phone = $this->normalizePhone($message['from'] ?? null);
        if ($messageId === '' || ! $phone) {
            return false;
        }

        $externalKey = 'whatsapp:'.$messageId;
        if (LeadActivity::query()->where('external_key', $externalKey)->exists()) {
            return false;
        }

        return DB::transaction(function () use (
            $message,
            $messageId,
            $phone,
            $profileNames,
            $metadata,
            $externalKey,
        ): bool {
            if (LeadActivity::query()->where('external_key', $externalKey)->lockForUpdate()->exists()) {
                return false;
            }

            $actor = $this->defaultAssignee();
            $lead = Lead::query()
                ->where('phone', $phone)
                ->whereIn('status', ['leads', 'penawaran'])
                ->latest('id')
                ->first();

            $referral = is_array($message['referral'] ?? null) ? $message['referral'] : [];
            $campaign = $this->campaignFromReferral($referral);
            if (! $lead) {
                $profileName = $profileNames[$phone] ?? '';
                $lead = Lead::query()->create([
                    'client_name' => $profileName !== '' ? $profileName : 'WhatsApp '.substr($phone, -4),
                    'phone' => $phone,
                    'project_value' => 0,
                    'budget_text' => 'Belum dikualifikasi',
                    'status' => 'leads',
                    'source' => 'WhatsApp',
                    'campaign' => $campaign,
                    'type' => 'Belum dikualifikasi',
                    'assigned_to' => $actor->id,
                    'created_by' => $actor->id,
                ]);
            }

            $occurredAt = $this->messageTime($message['timestamp'] ?? null);
            LeadActivity::query()->create([
                'lead_id' => $lead->id,
                'user_id' => null,
                'type' => 'message',
                'channel' => 'whatsapp',
                'direction' => 'inbound',
                'body' => $this->messageBody($message),
                'external_key' => $externalKey,
                'metadata' => array_filter([
                    'message_id' => $messageId,
                    'message_type' => $message['type'] ?? 'unknown',
                    'phone_number_id' => $metadata['phone_number_id'] ?? null,
                    'display_phone_number' => $metadata['display_phone_number'] ?? null,
                    'referral' => $referral ?: null,
                ], fn ($value): bool => $value !== null && $value !== ''),
                'occurred_at' => $occurredAt,
            ]);

            $updates = [
                'last_contacted_at' => $occurredAt,
                'source' => 'WhatsApp',
            ];
            if (! $lead->campaign && $campaign) {
                $updates['campaign'] = $campaign;
            }
            $lead->forceFill($updates)->save();

            return true;
        });
    }

    private function processStatus(array $status): bool
    {
        $messageId = trim((string) ($status['id'] ?? ''));
        if ($messageId === '') {
            return false;
        }

        $activity = LeadActivity::query()
            ->where('external_key', 'whatsapp:'.$messageId)
            ->first();
        if (! $activity) {
            return false;
        }

        $metadata = $activity->metadata ?? [];
        $metadata['delivery_status'] = $status['status'] ?? 'unknown';
        $metadata['status_timestamp'] = $status['timestamp'] ?? null;
        if (isset($status['conversation'])) {
            $metadata['conversation'] = $status['conversation'];
        }
        if (isset($status['pricing'])) {
            $metadata['pricing'] = $status['pricing'];
        }
        if (isset($status['errors'])) {
            $metadata['errors'] = $status['errors'];
        }

        $activity->forceFill(['metadata' => $metadata])->save();

        return true;
    }

    private function postMessage(array $payload): Response
    {
        $url = sprintf(
            '%s/%s/%s/messages',
            rtrim((string) config('services.whatsapp.base_url'), '/'),
            trim((string) config('services.whatsapp.graph_version'), '/'),
            rawurlencode((string) config('services.whatsapp.phone_number_id')),
        );

        $response = Http::acceptJson()
            ->withToken((string) config('services.whatsapp.access_token'))
            ->timeout((int) config('services.whatsapp.timeout', 15))
            ->post($url, $payload);

        if (! $response->successful()) {
            $message = $response->json('error.message')
                ?: 'WhatsApp Cloud API menolak permintaan pengiriman.';
            throw ValidationException::withMessages(['whatsapp' => $message]);
        }

        return $response;
    }

    private function recordOutbound(
        Lead $lead,
        User $actor,
        string $body,
        string $messageType,
        Response $response,
        array $extraMetadata = [],
    ): LeadActivity {
        $messageId = (string) ($response->json('messages.0.id') ?: '');
        if ($messageId === '') {
            throw ValidationException::withMessages([
                'whatsapp' => 'Meta menerima respons tanpa ID pesan. Pengiriman tidak dicatat agar tidak menghasilkan duplikasi.',
            ]);
        }

        $occurredAt = now();
        $activity = LeadActivity::query()->firstOrCreate(
            ['external_key' => 'whatsapp:'.$messageId],
            [
                'lead_id' => $lead->id,
                'user_id' => $actor->id,
                'type' => 'message',
                'channel' => 'whatsapp',
                'direction' => 'outbound',
                'body' => $body,
                'metadata' => [
                    'message_id' => $messageId,
                    'message_type' => $messageType,
                    'delivery_status' => 'accepted',
                    ...$extraMetadata,
                ],
                'occurred_at' => $occurredAt,
            ],
        );

        $updates = ['last_contacted_at' => $occurredAt];
        if (! $lead->first_response_at) {
            $updates['first_response_at'] = $occurredAt;
        }
        $lead->forceFill($updates)->save();

        return $activity;
    }

    private function ensureOutboundConfigured(): void
    {
        if (! $this->status()['outbound_configured']) {
            throw ValidationException::withMessages([
                'whatsapp' => 'WhatsApp Cloud API belum dikonfigurasi pada environment server.',
            ]);
        }
    }

    private function ensureLeadPhone(Lead $lead): void
    {
        if (! $this->normalizePhone($lead->phone)) {
            throw ValidationException::withMessages([
                'phone' => 'Lead belum memiliki nomor WhatsApp yang valid.',
            ]);
        }
    }

    private function defaultAssignee(): User
    {
        return User::query()->where('role', 'mgr_marketing')->first()
            ?? User::query()->where('role', 'ceo')->first()
            ?? User::query()->firstOrFail();
    }

    private function normalizePhone(mixed $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return strlen($digits) >= 9 && strlen($digits) <= 15 ? $digits : null;
    }

    private function messageTime(mixed $timestamp): CarbonImmutable
    {
        if (is_numeric($timestamp) && (int) $timestamp > 0) {
            return CarbonImmutable::createFromTimestampUTC((int) $timestamp);
        }

        return CarbonImmutable::now();
    }

    private function campaignFromReferral(array $referral): ?string
    {
        $headline = trim((string) ($referral['headline'] ?? ''));
        if ($headline !== '') {
            return $headline;
        }

        $sourceUrl = trim((string) ($referral['source_url'] ?? ''));

        return $sourceUrl !== '' ? 'Click-to-WhatsApp: '.$sourceUrl : null;
    }

    private function messageBody(array $message): string
    {
        $type = (string) ($message['type'] ?? 'unknown');

        return match ($type) {
            'text' => trim((string) ($message['text']['body'] ?? '')) ?: '[Pesan teks kosong]',
            'button' => trim((string) ($message['button']['text'] ?? '')) ?: '[Tombol WhatsApp]',
            'interactive' => trim((string) (
                $message['interactive']['button_reply']['title']
                ?? $message['interactive']['list_reply']['title']
                ?? ''
            )) ?: '[Balasan interaktif WhatsApp]',
            'image' => trim((string) ($message['image']['caption'] ?? '')) ?: '[Gambar WhatsApp]',
            'video' => trim((string) ($message['video']['caption'] ?? '')) ?: '[Video WhatsApp]',
            'document' => trim((string) (
                $message['document']['caption']
                ?? $message['document']['filename']
                ?? ''
            )) ?: '[Dokumen WhatsApp]',
            'audio' => '[Audio WhatsApp]',
            'sticker' => '[Stiker WhatsApp]',
            'location' => sprintf(
                '[Lokasi WhatsApp: %s, %s]',
                $message['location']['latitude'] ?? '-',
                $message['location']['longitude'] ?? '-',
            ),
            'contacts' => '[Kontak WhatsApp]',
            'order' => '[Pesanan WhatsApp]',
            default => '[Pesan WhatsApp: '.$type.']',
        };
    }
}
