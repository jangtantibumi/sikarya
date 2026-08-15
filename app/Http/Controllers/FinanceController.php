<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(private readonly AccountingService $accounting) {}

    /**
     * Display the Finance dashboard.
     *
     * @return View
     */
    public function index(Request $request)
    {
        // Get summary data (balance sheet, profit & loss, cash flow) from AccountingService.
        $summary = $this->accounting->getFinanceSummary();

        // Recent journal entries (last 20) for display in the table.
        $journals = JournalEntry::latest()->take(20)->get();

        return view('finance.index', [
            'summary' => $summary,
            'journals' => $journals,
        ]);
    }

    public function storeJournal(Request $request)
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $request->validate([
            'memo' => 'required|string',
            'debit_account' => 'required|string',
            'credit_account' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $lines = [
            ['system_key' => $request->debit_account, 'debit' => $request->amount, 'memo' => $request->memo],
            ['system_key' => $request->credit_account, 'credit' => $request->amount, 'memo' => $request->memo],
        ];

        $this->accounting->createEntry(
            auth()->user(),
            now(),
            $request->memo,
            $lines,
            'manual_journal',
            time(), // arbitrary source_id
            'MNL-'.strtoupper(Str::random(6))
        );

        return redirect()->back()->with('success', 'Jurnal manual berhasil dicatat.');
    }
}
