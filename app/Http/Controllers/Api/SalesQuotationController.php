<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SalesQuotation;
use App\Services\SalesQuotationService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SalesQuotationController extends Controller
{
    public function __construct(
        private TenantContext $tenant,
        private SalesQuotationService $service
    ) {}

    private function authorizeCrm(Request $request): void
    {
        abort_unless(
            $request->user()?->isCEO() || $request->user()?->divisionKey() === 'marketing',
            403,
            'Anda tidak memiliki akses CRM.'
        );
    }

    public function index(Request $request, int $leadId)
    {
        $this->authorizeCrm($request);
        
        return response()->json(
            SalesQuotation::query()
                ->where('lead_id', $leadId)
                ->with('creator:id,name')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request, int $leadId)
    {
        $this->authorizeCrm($request);
        $lead = Lead::findOrFail($leadId);
        
        $data = $request->validate([
            'date' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|integer',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount' => 'nullable|numeric|min:0',
        ]);

        $quotation = $this->service->create($lead, $data, $data['lines'], $request->user());

        return response()->json($quotation, 201);
    }

    public function sendWhatsApp(Request $request, int $id)
    {
        $this->authorizeCrm($request);
        $quotation = SalesQuotation::with(['lead', 'lines'])->findOrFail($id);

        $this->service->markAsSent($quotation, $request->user());

        // Format pesan
        $linesText = $quotation->lines->map(function($line) {
            $price = number_format($line->unit_price, 0, ',', '.');
            $total = number_format($line->line_total, 0, ',', '.');
            return "- {$line->description} ({$line->quantity} x Rp{$price}) = Rp{$total}";
        })->join("\n");

        $totalFormatted = number_format($quotation->total_amount, 0, ',', '.');
        $validUntil = $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : 'Tanpa batas';

        $message = "Halo {$quotation->lead->client_name},\n\n"
                 . "Berikut adalah penawaran harga (Quotation) dari kami:\n"
                 . "*No. Ref:* {$quotation->number}\n"
                 . "*Berlaku s/d:* {$validUntil}\n\n"
                 . "*Rincian:*\n{$linesText}\n\n"
                 . "*Total Biaya: Rp {$totalFormatted}*\n\n"
                 . "Jika Anda menyetujui penawaran ini, silakan balas pesan ini.\n"
                 . "Terima kasih!";

        // Jika ada controller WhatsAppCloudController, kita bisa panggil, tapi di sini kita hanya
        // return text untuk front-end (atau mock pengiriman jika API key belum tersedia).
        return response()->json([
            'message' => 'Quotation dikirim via WhatsApp.',
            'whatsapp_message_preview' => $message,
            'whatsapp_url' => 'https://wa.me/' . $quotation->lead->phone . '?text=' . urlencode($message)
        ]);
    }

    public function accept(Request $request, int $id)
    {
        $this->authorizeCrm($request);
        $quotation = SalesQuotation::findOrFail($id);

        $this->service->accept($quotation, $request->user());

        return response()->json(['message' => 'Quotation disetujui. Lead ditutup (Won).']);
    }

    public function reject(Request $request, int $id)
    {
        $this->authorizeCrm($request);
        $quotation = SalesQuotation::findOrFail($id);
        
        $data = $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $this->service->reject($quotation, $data['reason'], $request->user());

        return response()->json(['message' => 'Quotation ditolak. Lead ditutup (Lost).']);
    }
}
