<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);

        config()->set('services.whatsapp', [
            'verify_token' => 'verify-token-test',
            'app_secret' => 'app-secret-test',
            'access_token' => 'access-token-test',
            'phone_number_id' => '123456789',
            'graph_version' => 'v25.0',
            'base_url' => 'https://graph.facebook.com',
            'timeout' => 15,
        ]);
    }

    public function test_meta_can_verify_the_webhook_without_an_erp_session(): void
    {
        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=verify-token-test&hub.challenge=998877')
            ->assertOk()
            ->assertSeeText('998877');

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=wrong&hub.challenge=998877')
            ->assertForbidden();
    }

    public function test_signed_inbound_message_creates_one_lead_and_is_idempotent(): void
    {
        $payload = $this->inboundPayload();

        $this->postSignedWebhook($payload)
            ->assertOk()
            ->assertSeeText('EVENT_RECEIVED');

        $this->assertDatabaseHas('leads', [
            'client_name' => 'Ibu Nadia',
            'phone' => '6281234567890',
            'source' => 'WhatsApp',
            'campaign' => 'Iklan Renovasi Juli',
            'status' => 'leads',
        ]);
        $this->assertDatabaseHas('lead_activities', [
            'external_key' => 'whatsapp:wamid.inbound-001',
            'channel' => 'whatsapp',
            'direction' => 'inbound',
            'body' => 'Saya ingin konsultasi renovasi.',
        ]);

        $this->postSignedWebhook($payload)->assertOk();

        $this->assertSame(1, Lead::query()->where('phone', '6281234567890')->count());
        $this->assertSame(
            1,
            LeadActivity::query()
                ->where('external_key', 'whatsapp:wamid.inbound-001')
                ->count(),
        );
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $json = json_encode($this->inboundPayload(), JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid',
            ],
            $json,
        )->assertForbidden();

        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('lead_activities', 0);
    }

    public function test_delivery_status_webhook_updates_the_existing_outbound_activity(): void
    {
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $lead = $this->leadFor($marketing);
        $activity = $lead->activities()->create([
            'user_id' => $marketing->id,
            'type' => 'message',
            'channel' => 'whatsapp',
            'direction' => 'outbound',
            'body' => 'Pesan yang sedang dikirim.',
            'external_key' => 'whatsapp:wamid.status-001',
            'metadata' => ['delivery_status' => 'accepted'],
            'occurred_at' => now(),
        ]);

        $this->postSignedWebhook([
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'statuses' => [[
                            'id' => 'wamid.status-001',
                            'status' => 'delivered',
                            'timestamp' => (string) now()->timestamp,
                        ]],
                    ],
                ]],
            ]],
        ])->assertOk();

        $this->assertSame('delivered', $activity->fresh()->metadata['delivery_status']);
    }

    public function test_authorized_marketing_user_can_send_text_inside_the_support_window(): void
    {
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $lead = $this->leadFor($marketing);
        $lead->activities()->create([
            'type' => 'message',
            'channel' => 'whatsapp',
            'direction' => 'inbound',
            'body' => 'Apakah bisa konsultasi hari ini?',
            'external_key' => 'whatsapp:wamid.customer-001',
            'occurred_at' => now()->subHour(),
        ]);

        Http::fake([
            'https://graph.facebook.com/v25.0/123456789/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => $lead->phone, 'wa_id' => $lead->phone]],
                'messages' => [['id' => 'wamid.outbound-001']],
            ], 200),
        ]);

        $this->actingAs($marketing)
            ->postJson("/api/leads/{$lead->id}/whatsapp/send", [
                'mode' => 'text',
                'body' => 'Bisa, kami jadwalkan pukul 15.00.',
            ])
            ->assertCreated()
            ->assertJsonPath('activity.delivery_status', 'accepted');

        Http::assertSent(function (HttpRequest $request) use ($lead): bool {
            return $request->url() === 'https://graph.facebook.com/v25.0/123456789/messages'
                && $request->hasHeader('Authorization', 'Bearer access-token-test')
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === $lead->phone
                && $request['type'] === 'text'
                && $request['text']['body'] === 'Bisa, kami jadwalkan pukul 15.00.';
        });

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'external_key' => 'whatsapp:wamid.outbound-001',
            'direction' => 'outbound',
        ]);
        $this->assertNotNull($lead->fresh()->first_response_at);
    }

    public function test_text_is_blocked_after_24_hours_but_an_approved_template_can_be_sent(): void
    {
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $lead = $this->leadFor($marketing);
        $lead->activities()->create([
            'type' => 'message',
            'channel' => 'whatsapp',
            'direction' => 'inbound',
            'body' => 'Pesan lama',
            'external_key' => 'whatsapp:wamid.customer-old',
            'occurred_at' => now()->subHours(25),
        ]);

        $this->actingAs($marketing)
            ->postJson("/api/leads/{$lead->id}/whatsapp/send", [
                'mode' => 'text',
                'body' => 'Menindaklanjuti konsultasi sebelumnya.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');

        Http::fake([
            'https://graph.facebook.com/v25.0/123456789/messages' => Http::response([
                'messages' => [['id' => 'wamid.template-001']],
            ], 200),
        ]);

        $this->actingAs($marketing)
            ->postJson("/api/leads/{$lead->id}/whatsapp/send", [
                'mode' => 'template',
                'template_name' => 'follow_up_konsultasi',
                'language' => 'id',
                'template_parameters' => ['Ibu Nadia'],
            ])
            ->assertCreated();

        Http::assertSent(fn (HttpRequest $request): bool => $request['type'] === 'template'
            && $request['template']['name'] === 'follow_up_konsultasi'
            && $request['template']['components'][0]['parameters'][0]['text'] === 'Ibu Nadia');
    }

    public function test_non_marketing_user_cannot_read_status_or_send_messages(): void
    {
        $finance = User::query()->where('username', 'staff_finance')->firstOrFail();
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $lead = $this->leadFor($marketing);

        $this->actingAs($finance)
            ->getJson('/api/crm/whatsapp/status')
            ->assertForbidden();

        $this->actingAs($finance)
            ->postJson("/api/leads/{$lead->id}/whatsapp/send", [
                'mode' => 'text',
                'body' => 'Tidak boleh terkirim.',
            ])
            ->assertForbidden();
    }

    private function postSignedWebhook(array $payload)
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $json, 'app-secret-test');

        return $this->call(
            'POST',
            '/webhooks/whatsapp',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $json,
        );
    }

    private function inboundPayload(): array
    {
        return [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba-001',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'display_phone_number' => '15551234567',
                            'phone_number_id' => '123456789',
                        ],
                        'contacts' => [[
                            'profile' => ['name' => 'Ibu Nadia'],
                            'wa_id' => '6281234567890',
                        ]],
                        'messages' => [[
                            'from' => '6281234567890',
                            'id' => 'wamid.inbound-001',
                            'timestamp' => (string) now()->timestamp,
                            'type' => 'text',
                            'text' => ['body' => 'Saya ingin konsultasi renovasi.'],
                            'referral' => [
                                'source_type' => 'ad',
                                'headline' => 'Iklan Renovasi Juli',
                            ],
                        ]],
                    ],
                ]],
            ]],
        ];
    }

    private function leadFor(User $assignee): Lead
    {
        return Lead::query()->create([
            'client_name' => 'Ibu Nadia',
            'phone' => '6281234567890',
            'project_value' => 500000000,
            'budget_text' => 'Est: Rp 500Jt',
            'status' => 'leads',
            'source' => 'WhatsApp',
            'type' => 'Renovasi',
            'assigned_to' => $assignee->id,
            'created_by' => $assignee->id,
        ]);
    }
}
