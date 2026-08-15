<?php
namespace App\Models; use App\Models\Concerns\BelongsToCompany; use Illuminate\Database\Eloquent\Model;
class GoodsReceipt extends Model { use BelongsToCompany; protected $fillable=['company_id','purchase_order_id','warehouse_id','number','received_date','status','received_by_id','notes']; public function lines(){return $this->hasMany(GoodsReceiptLine::class);} public function purchaseOrder(){return $this->belongsTo(PurchaseOrder::class);} public function warehouse(){return $this->belongsTo(Warehouse::class);} public function returns(){return $this->hasMany(PurchaseReturn::class);} }
