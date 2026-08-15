<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ClientInflow;
use App\Services\AccountingService;
use App\Services\DataDeletionRequestService;
use App\Services\LeadRevenueService;
use App\Services\MetricAggregationService;
use App\Services\ProjectCostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ClientInflowController extends Controller
{
    public function __construct(
        private readonly MetricAggregationService $metrics,
        private readonly ProjectCostingService $projects,
        private readonly AccountingService $accounting,
        private readonly LeadRevenueService $leadRevenue,
        private readonly DataDeletionRequestService $deletions,
    ) {}

    public function index(Request $request)
    {
        $query = ClientInflow::query()->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->has('month') && ! empty($request->month)) {
            // $request->month format: YYYY-MM
            $query->where('date', 'like', $request->month.'%');
        }

        $inflows = $query->get();

        // Calculate Summary Statistics
        $totalInflow = $inflows->sum('payment_amount');
        $totalOutstanding = $inflows->where('payment_status', 'Belum Lunas')->sum('remaining_balance');
        $totalProjectValue = $inflows->unique('client_name')->sum('project_value');
        $newClientsCount = $inflows->unique('client_name')->count();

        return response()->json([
            'success' => true,
            'data' => $inflows,
            'summary' => [
                'total_inflow' => $totalInflow,
                'total_outstanding' => $totalOutstanding,
                'total_project_value' => $totalProjectValue,
                'new_clients_count' => $newClientsCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|integer|exists:leads,id',
            'date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'domicile' => 'nullable|string|max:255',
            'client_no' => 'nullable|string|max:255',
            'start_project' => 'nullable|string|max:50',
            'package' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'project_value' => 'required|numeric|min:0',
            'termin_no' => 'required|string|max:50',
            'total_termin' => 'required|string|max:50',
            'payment_amount' => 'required|numeric|min:0',
            'invoice_file' => 'nullable|string',
            'pj_survey' => 'nullable|string|max:255',
        ]);

        $calc = $this->calculateBalanceAndStatus(
            $validated['client_no'] ?? null,
            $validated['client_name'],
            $validated['project_value'],
            $validated['payment_amount'],
            $validated['package'],
            $validated['termin_no']
        );

        $inflow = ClientInflow::create(array_merge($validated, [
            'remaining_balance' => $calc['remaining_balance'],
            'payment_status' => $calc['payment_status'],
            'created_by' => $request->user() ? $request->user()->username : 'mgr_finance',
        ]));
        $this->leadRevenue->sync($inflow);
        $this->projects->syncClientInflow($inflow);
        $this->accounting->syncClientInflow($inflow, $request->user());
        $this->metrics->recalculateForDataSource('client_inflows', 'finance');
        $this->metrics->recalculateForDataSource('leads', 'marketing');

        return response()->json([
            'success' => true,
            'message' => 'Data pemasukan klien berhasil ditambahkan.',
            'data' => $inflow,
        ]);
    }

    public function update(Request $request, $id)
    {
        $inflow = ClientInflow::findOrFail($id);

        $validated = $request->validate([
            'lead_id' => 'nullable|integer|exists:leads,id',
            'date' => 'sometimes|required|date',
            'client_name' => 'sometimes|required|string|max:255',
            'domicile' => 'nullable|string|max:255',
            'client_no' => 'nullable|string|max:255',
            'start_project' => 'nullable|string|max:50',
            'package' => 'sometimes|required|string|max:100',
            'notes' => 'nullable|string',
            'project_value' => 'sometimes|required|numeric|min:0',
            'termin_no' => 'sometimes|required|string|max:50',
            'total_termin' => 'sometimes|required|string|max:50',
            'payment_amount' => 'sometimes|required|numeric|min:0',
            'invoice_file' => 'nullable|string',
            'pj_survey' => 'nullable|string|max:255',
        ]);

        $inflow->fill($validated);

        $calc = $this->calculateBalanceAndStatus(
            $inflow->client_no,
            $inflow->client_name,
            $inflow->project_value,
            $inflow->payment_amount,
            $inflow->package,
            $inflow->termin_no,
            $inflow->id
        );

        $inflow->remaining_balance = $calc['remaining_balance'];
        $inflow->payment_status = $calc['payment_status'];
        $inflow->save();
        $this->leadRevenue->sync($inflow);
        $this->projects->syncClientInflow($inflow);
        $this->accounting->syncClientInflow($inflow, $request->user());
        $this->metrics->recalculateForDataSource('client_inflows', 'finance');
        $this->metrics->recalculateForDataSource('leads', 'marketing');

        return response()->json([
            'success' => true,
            'message' => 'Data pemasukan berhasil diperbarui.',
            'data' => $inflow,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $inflow = ClientInflow::findOrFail($id);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $result = $this->deletions->request(
            $request->user(),
            'client_inflow',
            $inflow->id,
            $validated['reason'],
        );

        return response()->json([
            'success' => true,
            ...$result,
        ], $result['approval'] ? 202 : 200);
    }

    public function exportCsv(Request $request)
    {
        $query = ClientInflow::query()->orderBy('date', 'asc');
        if ($request->has('month') && ! empty($request->month)) {
            $query->where('date', 'like', $request->month.'%');
        }

        $inflows = $query->get();

        $filename = 'Laporan_Data_Transfer_Klien_'.($request->month ?? date('Y-m')).'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($inflows) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['DATA TRANSFER KLIEN - SUBA ARCH STUDIO']);
            fputcsv($file, []);
            fputcsv($file, ['No', 'Tanggal', 'Nama Klien', 'Domisili', 'No. Klien', 'Start Project', 'Paket', 'Catatan', 'Nilai Project', 'Termin ke', 'Total Termin', 'Besar Pembayaran', 'Sisa Pembayaran', 'Status Pembayaran', 'File Invoice', 'PJ Survey']);

            $no = 1;
            $totalPayments = 0;
            foreach ($inflows as $row) {
                $totalPayments += $row->payment_amount;
                fputcsv($file, [
                    $no++,
                    $row->date,
                    $row->client_name,
                    $row->domicile,
                    $row->client_no,
                    $row->start_project,
                    $row->package,
                    $row->notes,
                    'Rp '.number_format($row->project_value, 0, ',', '.'),
                    $row->termin_no,
                    $row->total_termin,
                    'Rp '.number_format($row->payment_amount, 0, ',', '.'),
                    'Rp '.number_format($row->remaining_balance, 0, ',', '.'),
                    $row->payment_status,
                    $row->invoice_file,
                    $row->pj_survey,
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total Pemasukan', '', '', '', '', '', '', '', '', '', '', 'Rp '.number_format($totalPayments, 0, ',', '.')]);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');

        $rows = [];
        while (($data = fgetcsv($file, 2000, ',')) !== false) {
            $rows[] = $data;
        }
        fclose($file);

        if (count($rows) < 2) {
            return response()->json(['success' => false, 'message' => 'File CSV kosong atau tidak valid.'], 400);
        }

        $imported = [];
        $headerFound = false;

        foreach ($rows as $index => $row) {
            if (! $headerFound) {
                if (isset($row[0]) && (strtolower(trim($row[0])) === 'no' || strtolower(trim($row[1] ?? '')) === 'tanggal')) {
                    $headerFound = true;
                }

                continue;
            }

            if (empty($row[1]) && empty($row[2])) {
                continue;
            }
            if (isset($row[0]) && strtolower(trim($row[0])) === 'total') {
                continue;
            }

            $dateRaw = trim($row[1] ?? '');
            $clientName = trim($row[2] ?? '');
            if (empty($clientName)) {
                continue;
            }

            $domicile = trim($row[3] ?? '');
            $clientNo = trim($row[4] ?? '');
            $startProject = trim($row[5] ?? '');
            $package = trim($row[6] ?? 'Bronze');
            $notes = trim($row[7] ?? '');

            $cleanNum = function ($str) {
                $s = preg_replace('/[^0-9.]/', '', str_replace(',', '', $str ?? '0'));

                return (float) $s;
            };

            $projectValue = $cleanNum($row[8] ?? '0');
            $terminNo = trim($row[9] ?? '1');
            $totalTermin = trim($row[10] ?? '3');
            $paymentAmount = $cleanNum($row[11] ?? '0');
            $invoiceFile = trim($row[14] ?? '');
            $pjSurvey = trim($row[15] ?? '');

            $formattedDate = date('Y-m-d');
            if (! empty($dateRaw)) {
                $time = strtotime($dateRaw);
                if ($time !== false) {
                    $formattedDate = date('Y-m-d', $time);
                }
            }

            $calc = $this->calculateBalanceAndStatus($clientNo, $clientName, $projectValue, $paymentAmount, $package, $terminNo);

            $inflow = ClientInflow::create([
                'date' => $formattedDate,
                'client_name' => $clientName,
                'domicile' => $domicile,
                'client_no' => $clientNo,
                'start_project' => $startProject,
                'package' => $package,
                'notes' => $notes,
                'project_value' => $projectValue,
                'termin_no' => $terminNo,
                'total_termin' => $totalTermin,
                'payment_amount' => $paymentAmount,
                'remaining_balance' => $calc['remaining_balance'],
                'payment_status' => $calc['payment_status'],
                'invoice_file' => $invoiceFile,
                'pj_survey' => $pjSurvey,
                'created_by' => $request->user() ? $request->user()->username : 'mgr_finance',
            ]);

            $this->leadRevenue->sync($inflow);
            $this->projects->syncClientInflow($inflow);
            $this->accounting->syncClientInflow($inflow, $request->user());
            $imported[] = $inflow;
        }

        if ($imported !== []) {
            $this->metrics->recalculateForDataSource('client_inflows', 'finance');
            $this->metrics->recalculateForDataSource('leads', 'marketing');
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengimpor '.count($imported).' data transaksi dari file spreadsheet.',
            'imported_count' => count($imported),
        ]);
    }

    public function uploadInvoice(Request $request)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('invoice');
        $uploadDir = public_path('uploads/invoices');
        if (! file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'invoice_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($uploadDir, $fileName);

        return response()->json([
            'success' => true,
            'url' => '/uploads/invoices/'.$fileName,
        ]);
    }

    private function calculateBalanceAndStatus($clientNo, $clientName, $projectValue, $paymentAmount, $package, $terminNo, $excludeId = null)
    {
        $packageLower = strtolower($package ?? '');
        $terminLower = strtolower($terminNo ?? '');

        if ($packageLower === 'survey' || $terminLower === 'survei') {
            return [
                'remaining_balance' => 0,
                'payment_status' => 'LUNAS',
            ];
        }

        $query = ClientInflow::query();
        if (! empty($clientNo)) {
            $query->where('client_no', $clientNo);
        } else {
            $query->where('client_name', $clientName);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $otherPayments = $query->sum('payment_amount');
        $totalPaid = $otherPayments + $paymentAmount;

        $remaining = max(0, $projectValue - $totalPaid);
        $status = ($remaining <= 0) ? 'LUNAS' : 'Belum Lunas';

        return [
            'remaining_balance' => $remaining,
            'payment_status' => $status,
        ];
    }
}
