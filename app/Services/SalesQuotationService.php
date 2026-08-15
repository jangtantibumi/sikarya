<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SalesQuotationService
{
    public function create(Lead $lead, array $data, array $lines, User $actor): SalesQuotation
    {
        return DB::transaction(function () use ($lead, $data, $lines, $actor) {
            $totalAmount = 0;

            $quotation = SalesQuotation::query()->create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'number' => 'SQ-'.now()->format('YmdHis').'-'.$lead->id,
                'date' => $data['date'] ?? today(),
                'valid_until' => $data['valid_until'] ?? today()->addDays(30),
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by_id' => $actor->id,
                'total_amount' => 0, // Will update below
            ]);

            foreach ($lines as $line) {
                $quantity = $line['quantity'] ?? 1;
                $unitPrice = $line['unit_price'] ?? 0;
                $discount = $line['discount'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount;
                $totalAmount += $lineTotal;

                $quotation->lines()->create([
                    'company_id' => $lead->company_id,
                    'product_id' => $line['product_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'line_total' => $lineTotal,
                ]);
            }

            $quotation->update(['total_amount' => $totalAmount]);

            // Update lead status and record activity
            if ($lead->status === 'leads') {
                $lead->update(['status' => 'penawaran', 'project_value' => $totalAmount]);
            }

            $lead->activities()->create([
                'user_id' => $actor->id,
                'type' => 'note',
                'channel' => 'internal',
                'direction' => 'internal',
                'body' => "Quotation {$quotation->number} sebesar Rp ".number_format($totalAmount, 0, ',', '.').' telah dibuat.',
                'occurred_at' => now(),
            ]);

            return $quotation->load('lines.product');
        });
    }

    public function markAsSent(SalesQuotation $quotation, User $actor): void
    {
        abort_unless($quotation->status === 'draft', 422, 'Hanya Quotation draft yang bisa dikirim.');

        $quotation->update(['status' => 'sent']);

        $quotation->lead->activities()->create([
            'user_id' => $actor->id,
            'type' => 'message',
            'channel' => 'whatsapp',
            'direction' => 'outbound',
            'body' => "Quotation {$quotation->number} dikirimkan ke klien via WhatsApp.",
            'occurred_at' => now(),
        ]);
    }

    public function accept(SalesQuotation $quotation, User $actor): void
    {
        abort_unless(in_array($quotation->status, ['draft', 'sent']), 422, 'Quotation tidak valid untuk disetujui.');

        DB::transaction(function () use ($quotation, $actor) {
            $quotation->update(['status' => 'accepted']);

            $lead = $quotation->lead;
            $lead->update([
                'status' => 'deal',
                'won_at' => now(),
                'project_value' => $quotation->total_amount,
            ]);

            $lead->activities()->create([
                'user_id' => $actor->id,
                'type' => 'stage_change',
                'channel' => 'internal',
                'direction' => 'internal',
                'body' => "Quotation {$quotation->number} disetujui. Status Lead berubah menjadi Deal.",
                'occurred_at' => now(),
            ]);

            // Dispatch webhook
            app(WebhookDispatchService::class)->dispatch($quotation->company_id, 'lead.deal', [
                'lead_id' => $lead->id,
                'client_name' => $lead->client_name,
                'quotation_number' => $quotation->number,
                'project_value' => $quotation->total_amount,
            ]);
        });
    }

    public function reject(SalesQuotation $quotation, string $reason, User $actor): void
    {
        abort_unless(in_array($quotation->status, ['draft', 'sent']), 422, 'Quotation tidak valid untuk ditolak.');

        DB::transaction(function () use ($quotation, $reason, $actor) {
            $quotation->update(['status' => 'rejected']);

            $lead = $quotation->lead;
            $lead->update([
                'status' => 'lost',
                'lost_reason' => "Quotation {$quotation->number} ditolak: {$reason}",
            ]);

            $lead->activities()->create([
                'user_id' => $actor->id,
                'type' => 'stage_change',
                'channel' => 'internal',
                'direction' => 'internal',
                'body' => "Quotation {$quotation->number} ditolak. Alasan: {$reason}",
                'occurred_at' => now(),
            ]);
        });
    }
}
