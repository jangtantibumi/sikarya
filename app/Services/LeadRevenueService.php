<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClientInflow;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Support\Str;

class LeadRevenueService
{
    public function sync(ClientInflow $inflow): ?Lead
    {
        $lead = $inflow->lead_id ? Lead::query()->find($inflow->lead_id) : null;
        $lead ??= $this->findMatch($inflow);

        if (! $lead) {
            return null;
        }

        if ((int) $inflow->lead_id !== (int) $lead->id) {
            $inflow->forceFill(['lead_id' => $lead->id])->saveQuietly();
        }

        $lead->forceFill([
            'status' => 'deal',
            'won_at' => $lead->won_at ?? now(),
            'project_value' => max((float) $lead->project_value, (float) $inflow->project_value),
            'budget_text' => $lead->budget_text ?: $this->currencyLabel((float) $inflow->project_value),
        ])->save();

        LeadActivity::query()->updateOrCreate(
            ['external_key' => "client-inflow:{$inflow->id}"],
            [
                'lead_id' => $lead->id,
                'type' => 'payment',
                'channel' => 'erp',
                'direction' => 'internal',
                'body' => sprintf(
                    'Finance mencatat pembayaran termin %s sebesar %s.',
                    $inflow->termin_no,
                    $this->currencyLabel((float) $inflow->payment_amount),
                ),
                'metadata' => [
                    'client_inflow_id' => $inflow->id,
                    'payment_amount' => (float) $inflow->payment_amount,
                    'project_value' => (float) $inflow->project_value,
                ],
                'occurred_at' => $inflow->date,
            ],
        );

        return $lead->refresh();
    }

    private function findMatch(ClientInflow $inflow): ?Lead
    {
        $phone = $this->normalizePhone($inflow->client_no);
        $name = Str::lower(trim((string) $inflow->client_name));

        return Lead::query()
            ->latest('id')
            ->get()
            ->first(function (Lead $lead) use ($phone, $name): bool {
                if ($phone && $this->normalizePhone($lead->phone) === $phone) {
                    return true;
                }

                return $name !== '' && Str::lower(trim((string) $lead->client_name)) === $name;
            });
    }

    private function normalizePhone(?string $phone): ?string
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

    private function currencyLabel(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
