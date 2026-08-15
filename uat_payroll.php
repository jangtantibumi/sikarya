<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ceo = \App\Models\User::where('role', 'ceo')->first();

$draftPayroll = \App\Models\Payroll::firstOrCreate([
    'user_id' => \App\Models\User::where('email', 'emp6@demo.com')->first()->id,
    'company_id' => 1,
    'period_start' => now()->startOfMonth(),
    'period_end' => now()->endOfMonth(),
], [
    'base_amount' => 6000000,
    'net_amount' => 6000000,
    'status' => 'draft'
]);

echo "Draft created for Emp6\n";

echo "2. Approve Payroll...\n";
if ($draftPayroll->status === 'draft') {
    $draftPayroll->update(['status' => 'approved', 'approved_by' => $ceo->id]);
    echo "Approve Status: OK\n";
}

echo "3. Pay / Disburse Payroll...\n";
if ($draftPayroll->status === 'approved') {
    app(\App\Services\PayrollDisbursementService::class)->disburse($draftPayroll, $ceo);
    echo "Pay Status: OK\n";
}

echo "4. Verify Journal Entry...\n";
$journal = \App\Models\JournalEntry::where('source_type', \App\Models\Payroll::class)->where('source_id', $draftPayroll->id)->first();
echo "Journal Created: " . ($journal ? 'Yes' : 'No') . "\n";
if ($journal) echo "Journal Lines Count: " . $journal->lines->count() . "\n";
