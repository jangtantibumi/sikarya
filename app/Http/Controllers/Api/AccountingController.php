<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Services\AccountingImportService;
use App\Services\AccountingService;
use App\Services\RecordAttachmentService;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AccountingController extends Controller
{
    public function __construct(
        private readonly AccountingService $accounting,
        private readonly AccountingImportService $imports,
        private readonly RecordAttachmentService $attachments,
        private readonly SecurityAuditService $audit,
    ) {}

    public function index(Request $request)
    {
        $viewer = $this->authorizeRead($request);
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);
        $month = isset($validated['month']) ? (int) $validated['month'] : (int) now()->month;

        $companyId = $viewer->company_id;

        return response()->json([
            'can_write' => $this->canWrite($viewer),
            'year' => $year,
            'month' => $month,
            'monthly_profit_loss' => $this->accounting->profitAndLoss($year, $month, $companyId),
            'annual_evaluation' => $this->accounting->annualEvaluation($year, $companyId),
            'accounts' => Account::query()->where('is_active', true)->orderBy('code')->get(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'code', 'name', 'project_type']),
            'recent_entries' => JournalEntry::query()
                ->with([
                    'creator:id,name,username',
                    'lines.account:id,code,name,type',
                    'lines.project:id,code,name',
                    'attachments:id,attachable_type,attachable_id,original_name,mime_type,size_bytes',
                ])
                ->latest('entry_date')
                ->latest('id')
                ->limit(50)
                ->get(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function storeTransaction(Request $request)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'kind' => ['required', 'string', 'in:revenue,expense'],
            'category' => [
                'required',
                'string',
                'in:design_revenue,contractor_revenue,direct_project_cost,payroll_expense,operating_expense,marketing_expense',
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'description' => ['required', 'string', 'max:1000'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt,zip',
            ],
        ]);

        if ($validated['kind'] === 'revenue' && ! str_ends_with($validated['category'], '_revenue')) {
            throw ValidationException::withMessages(['category' => 'Kategori pendapatan tidak valid.']);
        }
        if ($validated['kind'] === 'expense' && str_ends_with($validated['category'], '_revenue')) {
            throw ValidationException::withMessages(['category' => 'Kategori biaya tidak valid.']);
        }

        $entry = DB::transaction(function () use ($actor, $validated, $request): JournalEntry {
            $entry = $this->accounting->recordQuickTransaction($actor, $validated);
            if ($request->hasFile('attachment')) {
                $this->attachments->store($entry, $request->file('attachment'), $actor);
            }

            return $entry->fresh(['lines.account', 'lines.project', 'attachments']);
        });
        $this->recordAudit($request, $actor, $entry, 'accounting.transaction_posted');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diposting sebagai jurnal double-entry yang seimbang.',
            'entry' => $entry,
        ], 201);
    }

    public function importTransactions(Request $request)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt'],
        ]);

        $summary = $this->imports->import($validated['file'], $actor);
        $this->audit->record(
            'accounting.file_imported',
            actor: $actor,
            request: $request,
            metadata: collect($summary)->except('entry_ids')->all(),
            subjectType: 'accounting_import',
            subjectId: $summary['batch'],
        );

        return response()->json([
            'success' => true,
            'message' => "{$summary['imported']} transaksi berhasil diimpor dan langsung membentuk jurnal double-entry yang seimbang.",
            'summary' => $summary,
        ], 201);
    }

    public function downloadImportTemplate(Request $request)
    {
        $this->authorizeRead($request);
        $csv = "\xEF\xBB\xBF".
            "tanggal,jenis,kategori,nilai,keterangan,kode_proyek,referensi\r\n".
            "2026-07-28,Pendapatan,Pendapatan Jasa Desain,15000000,Pembayaran termin desain,,INV-2026-001\r\n".
            "2026-07-28,Biaya,Beban Operasional,750000,Langganan internet kantor,,EXP-2026-001\r\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-impor-akuntansi-suba-arch.csv"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function storeJournal(Request $request)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:1000'],
            'reference' => ['nullable', 'string', 'max:100', 'unique:journal_entries,reference'],
            'lines' => ['required', 'array', 'min:2', 'max:50'],
            'lines.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $entry = $this->accounting->createEntry(
                $actor,
                $validated['date'],
                $validated['description'],
                $validated['lines'],
                reference: $validated['reference'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['lines' => $exception->getMessage()]);
        }

        $this->recordAudit($request, $actor, $entry, 'accounting.journal_posted');

        return response()->json([
            'success' => true,
            'message' => 'Jurnal manual berhasil diposting dan tervalidasi seimbang.',
            'entry' => $entry,
        ], 201);
    }

    private function authorizeRead(Request $request)
    {
        $viewer = $request->user();
        abort_unless($viewer->isCEO() || $viewer->divisionKey() === 'finance', 403);

        return $viewer;
    }

    private function authorizeWrite(Request $request)
    {
        $viewer = $this->authorizeRead($request);
        abort_unless($this->canWrite($viewer), 403);

        return $viewer;
    }

    private function canWrite($user): bool
    {
        return $user->isCEO() || $user->role === 'mgr_finance';
    }

    private function recordAudit(Request $request, $actor, JournalEntry $entry, string $event): void
    {
        $this->audit->record(
            $event,
            actor: $actor,
            request: $request,
            metadata: [
                'reference' => $entry->reference,
                'entry_date' => $entry->entry_date?->format('Y-m-d'),
                'total' => (float) $entry->lines->sum('debit'),
            ],
            subjectType: JournalEntry::class,
            subjectId: $entry->id,
        );
    }
}
