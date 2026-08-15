<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\Payroll;
use Illuminate\Support\Str;

class PayrollDisbursementService
{
    public function __construct(
        private readonly TenantContext $tenant,
        private readonly SecurityAuditService $audit
    ) {}

    public function disburse(Payroll $payroll, $actor)
    {
        if ($payroll->status !== 'approved') {
            throw new \Exception("Only approved payroll can be disbursed.");
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $journal = JournalEntry::create([
            'company_id' => $payroll->company_id,
            'entry_date' => now(),
            'reference' => 'PAYROLL-' . $payroll->id . '-' . Str::random(5),
            'description' => 'Disbursement of Payroll for ' . $payroll->user->name . ' (' . $payroll->period_start->format('M Y') . ')',
            'source_type' => Payroll::class,
            'source_id' => $payroll->id,
            'status' => 'posted',
            'created_by_id' => $actor->id,
            'posted_by_id' => $actor->id,
            'posted_at' => now(),
            'currency' => 'IDR',
            'exchange_rate' => 1.0
        ]);

        $expenseAccount = \App\Models\Account::firstOrCreate(
            ['code' => '50100', 'company_id' => $payroll->company_id],
            ['name' => 'Salary Expense', 'type' => 'expense', 'normal_balance' => 'debit']
        );
        $cashAccount = \App\Models\Account::firstOrCreate(
            ['code' => '10100', 'company_id' => $payroll->company_id],
            ['name' => 'Cash in Bank', 'type' => 'asset', 'normal_balance' => 'debit']
        );

        $journal->lines()->create([
            'account_id' => $expenseAccount->id,
            'debit' => $payroll->net_amount,
            'credit' => 0,
            'memo' => 'Salary Expense'
        ]);

        $journal->lines()->create([
            'account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $payroll->net_amount,
            'memo' => 'Cash Disbursement'
        ]);

        $this->audit->record(
            'payroll.disbursed',
            actor: $actor,
            request: request(),
            metadata: ['payroll_id' => $payroll->id, 'net_amount' => $payroll->net_amount],
            subjectType: Payroll::class,
            subjectId: $payroll->id,
        );
    }
}
