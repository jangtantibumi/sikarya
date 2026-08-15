<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ClientInflow;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;

class AccountingService
{
    public function createEntry(
        User $actor,
        CarbonInterface|string $date,
        string $description,
        array $lines,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $reference = null,
        string $currency = 'IDR',
        float $exchangeRate = 1.000000
    ): JournalEntry {
        $normalized = collect($lines)->map(function (array $line): array {
            $account = isset($line['account_id'])
                ? Account::query()->whereKey($line['account_id'])->where('is_active', true)->firstOrFail()
                : Account::query()->where('system_key', $line['system_key'] ?? null)->where('is_active', true)->firstOrFail();

            return [
                'account_id' => $account->id,
                'project_id' => $line['project_id'] ?? null,
                'debit' => round((float) ($line['debit'] ?? 0), 2),
                'credit' => round((float) ($line['credit'] ?? 0), 2),
                'memo' => $line['memo'] ?? null,
            ];
        });

        $totalDebit = round((float) $normalized->sum('debit'), 2);
        $totalCredit = round((float) $normalized->sum('credit'), 2);
        if ($normalized->count() < 2 || $totalDebit <= 0 || abs($totalDebit - $totalCredit) > 0.009) {
            throw new InvalidArgumentException('Jurnal harus memiliki minimal dua baris dan total debit harus sama dengan kredit.');
        }

        return DB::transaction(function () use (
            $actor,
            $date,
            $description,
            $normalized,
            $sourceType,
            $sourceId,
            $reference,
            $currency,
            $exchangeRate
        ): JournalEntry {
            $existing = $sourceType && $sourceId
                ? JournalEntry::query()->where('source_type', $sourceType)->where('source_id', $sourceId)->first()
                : null;

            $entry = $existing ?: new JournalEntry();
            $entry->fill([
                'company_id' => $actor->company_id,
                'entry_date' => $date,
                'reference' => $existing?->reference ?? $reference ?? $this->nextReference(),
                'description' => $description,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'status' => 'posted',
                'created_by_id' => $existing?->created_by_id ?? $actor->id,
                'posted_by_id' => $actor->id,
                'posted_at' => now(),
            ])->save();

            $entry->lines()->delete();
            $entry->lines()->createMany($normalized->all());

            return $entry->fresh(['lines.account', 'lines.project']);
        });
    }

    public function recordQuickTransaction(
        User $actor,
        array $data,
        ?string $reference = null,
        ?string $sourceType = null,
    ): JournalEntry
    {
        $amount = round((float) $data['amount'], 2);
        $projectId = $data['project_id'] ?? null;
        $isRevenue = $data['kind'] === 'revenue';

        return $this->createEntry(
            $actor,
            $data['date'],
            $data['description'],
            $isRevenue
                ? [
                    ['system_key' => 'cash_bank', 'debit' => $amount, 'project_id' => $projectId],
                    ['system_key' => $data['category'], 'credit' => $amount, 'project_id' => $projectId],
                ]
                : [
                    ['system_key' => $data['category'], 'debit' => $amount, 'project_id' => $projectId],
                    ['system_key' => 'cash_bank', 'credit' => $amount, 'project_id' => $projectId],
            ],
            sourceType: $sourceType,
            reference: $reference,
        );
    }

    public function reverseEntry(
        JournalEntry $entry,
        User $actor,
        string $reason,
    ): JournalEntry {
        return DB::transaction(function () use ($entry, $actor, $reason): JournalEntry {
            /** @var JournalEntry $locked */
            $locked = JournalEntry::query()
                ->with('lines')
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if ($locked->status === 'reversed') {
                $existing = JournalEntry::query()
                    ->where('source_type', 'journal_reversal')
                    ->where('source_id', $locked->id)
                    ->first();

                if ($existing) {
                    return $existing->load('lines');
                }

                throw new InvalidArgumentException('Jurnal sudah dibalik sebelumnya.');
            }

            if ($locked->status !== 'posted') {
                throw new InvalidArgumentException('Hanya jurnal posted yang dapat dibalik.');
            }

            if ($locked->source_type === 'journal_reversal') {
                throw new InvalidArgumentException('Jurnal pembalik tidak dapat dibalik kembali.');
            }

            $reversal = $this->createEntry(
                $actor,
                // Until accounting periods are formally closed, a correction
                // must offset the source period so the period report remains
                // reconcilable. Closed-period handling will be introduced with
                // the master accounting engine.
                $locked->entry_date->toDateString(),
                "Pembalikan {$locked->reference}: {$reason}",
                $locked->lines->map(fn ($line): array => [
                    'account_id' => $line->account_id,
                    'project_id' => $line->project_id,
                    'debit' => (float) $line->credit,
                    'credit' => (float) $line->debit,
                    'memo' => "Reversal {$locked->reference}",
                ])->all(),
                'journal_reversal',
                $locked->id,
                'REV-'.$locked->reference,
            );

            $locked->forceFill(['status' => 'reversed'])->save();

            return $reversal;
        });
    }

    public function syncClientInflow(ClientInflow $inflow, ?User $actor = null): ?JournalEntry
    {
        $actor ??= User::query()->where('username', $inflow->created_by)->first()
            ?: User::query()->where('role', 'ceo')->first();

        if (!$actor || (float) $inflow->payment_amount <= 0) {
            $this->removeSource('client_inflow', $inflow->id);

            return null;
        }

        $project = $this->projectForInflow($inflow);
        $revenueKey = $this->isContractor($inflow) ? 'contractor_revenue' : 'design_revenue';

        return $this->createEntry(
            $actor,
            $inflow->date,
            "Pembayaran {$inflow->client_name} - termin {$inflow->termin_no}",
            [
                ['system_key' => 'cash_bank', 'debit' => $inflow->payment_amount, 'project_id' => $project?->id],
                ['system_key' => $revenueKey, 'credit' => $inflow->payment_amount, 'project_id' => $project?->id],
            ],
            'client_inflow',
            $inflow->id,
            "CI-{$inflow->id}",
        );
    }

    public function removeSource(string $sourceType, int $sourceId): void
    {
        JournalEntry::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    public function profitAndLoss(int $year, ?int $month = null, ?int $companyId = null): array
    {
        $rows = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->whereIn('journal_entries.status', ['posted', 'reversed'])
            ->when($companyId, fn ($query) => $query->where('journal_entries.company_id', $companyId))
            ->whereYear('journal_entries.entry_date', $year)
            ->when($month, fn ($query) => $query->whereMonth('journal_entries.entry_date', $month))
            ->whereIn('accounts.type', ['revenue', 'expense'])
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type')
            ->orderBy('accounts.code')
            ->selectRaw('accounts.code, accounts.name, accounts.type, SUM(journal_lines.debit) AS debit_total, SUM(journal_lines.credit) AS credit_total')
            ->get()
            ->map(function (object $row): array {
                $amount = $row->type === 'revenue'
                    ? (float) $row->credit_total - (float) $row->debit_total
                    : (float) $row->debit_total - (float) $row->credit_total;

                return [
                    'code' => $row->code,
                    'name' => $row->name,
                    'type' => $row->type,
                    'amount' => round($amount, 2),
                ];
            });

        $revenue = round((float) $rows->where('type', 'revenue')->sum('amount'), 2);
        $expenses = round((float) $rows->where('type', 'expense')->sum('amount'), 2);
        $profit = round($revenue - $expenses, 2);

        return [
            'year' => $year,
            'month' => $month,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_profit' => $profit,
            'margin_percentage' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            'accounts' => $rows->values(),
        ];
    }

    public function annualEvaluation(int $year, ?int $companyId = null): array
    {
        $months = collect(range(1, 12))->map(fn (int $month): array => [
            'month' => $month,
            ...collect($this->profitAndLoss($year, $month, $companyId))->only([
                'revenue',
                'expenses',
                'net_profit',
                'margin_percentage',
            ])->all(),
        ]);

        $annual = $this->profitAndLoss($year, null, $companyId);
        $bestMonth = $months->sortByDesc('net_profit')->first();
        $lowestMonth = $months->sortBy('net_profit')->first();

        return [
            ...$annual,
            'months' => $months,
            'best_month' => $bestMonth,
            'lowest_month' => $lowestMonth,
        ];
    }

    private function nextReference(): string
    {
        do {
            $reference = 'JRN-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (JournalEntry::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function projectForInflow(ClientInflow $inflow): ?Project
    {
        return Project::query()
            ->where('client_inflow_id', $inflow->id)
            ->orWhere('client_name', $inflow->client_name)
            ->latest('id')
            ->first();
    }

    private function isContractor(ClientInflow $inflow): bool
    {
        $text = Str::lower(implode(' ', [
            $inflow->package,
            $inflow->notes,
        ]));

        return Str::contains($text, ['kontraktor', 'construction', 'konstruksi', 'pelaksana']);
    }
    
    /**
     * Generate jurnal untuk Purchase Order setelah disetujui CEO.
     */
    public function postPurchaseOrderJournal(User $actor, PurchaseOrder $po): JournalEntry
    {
        // Debit: Persediaan (inventory) – belum masuk karena barang belum diterima
        // Credit: Hutang Dagang (accounts_payable)
        $lines = [
            ['system_key' => 'accounts_payable', 'credit' => $po->total_amount, 'memo' => 'PO '.$po->number],
            ['system_key' => 'inventory', 'debit' => $po->total_amount, 'memo' => 'PO '.$po->number.' (menunggu barang)'],
        ];

        return $this->createEntry(
            $actor,
            now(),
            'Purchase Order '.$po->number.' - Liability recorded',
            $lines,
            'purchase_order',
            $po->id,
            'PO-'.$po->number
        );
    }

    /**
     * Generate jurnal untuk Goods Receipt setelah ACC CEO.
     */
    public function postGoodsReceiptJournal(User $actor, GoodsReceipt $gr, array $metadata): JournalEntry
    {
        // Debit: Persediaan, Credit: Hutang Dagang (accounts_payable) dihapus
        $lines = [
            ['system_key' => 'inventory', 'debit' => $metadata['value'], 'memo' => 'GR '.$gr->number],
            ['system_key' => 'accounts_payable', 'credit' => $metadata['value'], 'memo' => 'GR '.$gr->number.' (settlement)'],
        ];

        return $this->createEntry(
            $actor,
            now(),
            'Goods Receipt '.$gr->number.' - Stock inbound',
            $lines,
            'goods_receipt',
            $gr->id,
            'GR-'.$gr->number
        );
    }

    /**
     * Get a financial summary for the dashboard.
     * Returns an associative array containing balance sheet, profit & loss,
     * cash flow and equity information. This placeholder aggregates basic
     * totals from journal entries.
     */
    public function getFinanceSummary(): array
    {
        $totals = JournalEntry::query()
            ->with('lines')
            ->get()
            ->reduce(function (array $carry, $entry) {
                foreach ($entry->lines as $line) {
                    $carry['total_debit'] += (float) $line->debit;
                    $carry['total_credit'] += (float) $line->credit;
                }
                return $carry;
            }, ['total_debit' => 0, 'total_credit' => 0]);

        $balance = $totals['total_debit'] - $totals['total_credit'];
        return [
            'balance_sheet' => ['value' => $balance],
            'profit_loss' => ['value' => $balance],
            'cash_flow' => ['value' => $totals['total_debit']],
            'equity' => ['value' => $totals['total_credit']],
        ];
    }
}

