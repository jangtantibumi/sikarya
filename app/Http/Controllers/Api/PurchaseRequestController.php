<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function __construct(private TenantContext $tenant) {}
    private function companyId(): int { abort_unless($this->tenant->id(),422,'Akun belum dipetakan ke perusahaan.'); return $this->tenant->id(); }
    public function index() { $this->companyId(); return response()->json(PurchaseRequest::query()->with('lines.product')->latest()->get()); }
    public function store(Request $request) { 
        $company=$this->companyId(); 
        $data=$request->validate(['title'=>'required|string|max:255','reason'=>'nullable|string|max:2000', 'lines'=>'nullable|array', 'lines.*.product_id'=>'required|integer', 'lines.*.quantity'=>'required|numeric|gt:0']); 
        $pr = PurchaseRequest::query()->create([
            'company_id' => $company,
            'number' => 'PR-'.now()->format('Ymd').'-'.str_pad((string)(PurchaseRequest::withoutGlobalScopes()->where('company_id',$company)->count()+1),4,'0',STR_PAD_LEFT),
            'title' => $data['title'],
            'reason' => $data['reason'] ?? null,
            'status' => 'draft',
            'requested_by_id' => $request->user()->id
        ]);
        if (isset($data['lines'])) {
            foreach ($data['lines'] as $l) {
                $pr->lines()->create([
                    'company_id' => $company,
                    'product_id' => $l['product_id'],
                    'quantity' => $l['quantity']
                ]);
            }
        }
        return response()->json($pr->load('lines.product'),201); 
    }
    public function submit(int $id) { $request=PurchaseRequest::query()->findOrFail($id); abort_unless(in_array($request->status,['draft','rejected'],true),422,'Pengajuan tidak dapat diajukan kembali.'); $request->update(['status'=>'submitted']); return response()->json($request); }
    public function decide(Request $request,int $id) { $item=PurchaseRequest::query()->findOrFail($id); abort_unless($request->user()->isCEO()||$request->user()->isManager(),403); $data=$request->validate(['status'=>'required|in:approved,rejected']); abort_unless($item->status==='submitted',422,'Hanya pengajuan submitted yang dapat diputuskan.'); $item->update($data); return response()->json($item); }
}
