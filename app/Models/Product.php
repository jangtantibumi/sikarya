<?php
namespace App\Models;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
class Product extends Model { use BelongsToCompany; protected $fillable=['company_id','sku','name','unit','reorder_level','standard_cost','is_active', 'min_stock', 'max_stock', 'category_id', 'brand_id', 'barcode', 'qr_code', 'has_batches', 'has_serial_numbers']; protected $casts=['reorder_level'=>'decimal:3','standard_cost'=>'decimal:2','is_active'=>'boolean', 'min_stock'=>'decimal:2', 'max_stock'=>'decimal:2', 'has_batches'=>'boolean', 'has_serial_numbers'=>'boolean']; 

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
