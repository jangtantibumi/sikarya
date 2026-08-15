<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\PosSession; 
use App\Models\PosSale; 
use App\Services\TenantContext; 
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller 
{ 
    public function __construct(private TenantContext $tenant, private AccountingService $accounting){} 
    
    private function company(){
        abort_unless($this->tenant->id(),422,'Akun belum dipetakan ke perusahaan.');
        return $this->tenant->id();
    } 
    
    public function sessions(){
        $this->company();
        return response()->json(PosSession::query()->latest()->get());
    } 
    
    public function open(Request $r){
        $c=$this->company();
        $d=$r->validate([
            'warehouse_id'=>'nullable|integer',
            'opening_cash'=>'required|numeric|min:0'
        ]);
        abort_if(PosSession::query()->where('cashier_id',$r->user()->id)->where('status','open')->exists(),422,'Shift kasir masih terbuka.');
        return response()->json(PosSession::query()->create($d+['company_id'=>$c,'cashier_id'=>$r->user()->id,'status'=>'open','opened_at'=>now()]),201);
    } 
    
    public function sale(Request $r,int $id){
        $c=$this->company();
        $s=PosSession::query()->findOrFail($id);
        abort_unless($s->status==='open',422,'Shift sudah ditutup.');
        
        $d=$r->validate([
            'total_amount'=>'required|numeric|gt:0',
            'payment_method'=>'required|in:cash,transfer,qris,card'
        ]);
        
        return DB::transaction(function () use ($d, $c, $s, $r) {
            $sale = PosSale::query()->create($d+[
                'company_id'=>$c,
                'pos_session_id'=>$s->id,
                'receipt_number'=>'POS-'.now()->format('YmdHis').'-'.$s->id,
                'status'=>'paid',
                'created_by_id'=>$r->user()->id
            ]);
            
            // Auto-Journaling for POS Sale
            $this->accounting->createEntry(
                $r->user(),
                now()->toDateString(),
                "Penjualan POS - {$sale->receipt_number} ({$d['payment_method']})",
                [
                    ['system_key' => 'cash_bank', 'debit' => $d['total_amount']],
                    ['system_key' => 'design_revenue', 'credit' => $d['total_amount']]
                ],
                'pos_sale',
                $sale->id,
                $sale->receipt_number
            );

            return response()->json($sale, 201);
        });
    } 
    
    public function close(Request $r,int $id){
        $s=PosSession::query()->findOrFail($id);
        abort_unless($s->cashier_id===$r->user()->id||$r->user()->isManager()||$r->user()->isCEO(),403);
        $d=$r->validate(['closing_cash'=>'required|numeric|min:0']);
        $s->update($d+['status'=>'closed','closed_at'=>now()]);
        return response()->json($s);
    }
}
