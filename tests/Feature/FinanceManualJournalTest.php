<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManualJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_ceo_can_create_manual_journal()
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        
        $ceo = User::factory()->create([
            'role' => 'ceo',
            'company_id' => $company->id,
            'is_active' => true
        ]);

        $this->actingAs($ceo);
        $this->withoutExceptionHandling();

        \App\Models\Account::create(['company_id' => $company->id, 'system_key' => 'expense', 'type' => 'expense', 'normal_balance' => 'debit', 'code' => '5000', 'name' => 'Beban', 'is_active' => true]);
        \App\Models\Account::create(['company_id' => $company->id, 'system_key' => 'cash_bank', 'type' => 'asset', 'normal_balance' => 'debit', 'code' => '1000', 'name' => 'Kas', 'is_active' => true]);

        $response = $this->post(route('master-demo.finance.journal'), [
            'memo' => 'Pembayaran Sewa Kantor',
            'debit_account' => 'expense',
            'credit_account' => 'cash_bank',
            'amount' => 1500000,
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('journal_entries', [
            'company_id' => $company->id,
            'source_type' => 'manual_journal',
            'description' => 'Pembayaran Sewa Kantor',
        ]);

        $journal = JournalEntry::where('source_type', 'manual_journal')->first();
        
        $expenseAccount = \App\Models\Account::where('system_key', 'expense')->first();
        $cashBankAccount = \App\Models\Account::where('system_key', 'cash_bank')->first();

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $expenseAccount->id,
            'debit' => 1500000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'account_id' => $cashBankAccount->id,
            'debit' => 0,
            'credit' => 1500000,
        ]);
    }
}
