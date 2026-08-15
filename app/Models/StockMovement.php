<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'product_id', 'warehouse_id', 'type', 'quantity', 'unit_cost', 'reference', 'notes', 'created_by_id', 'batch_id', 'rack_id', 'bin_id'];

    protected $casts = ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:2'];

    public function batch()
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }

    public function rack()
    {
        return $this->belongsTo(WarehouseRack::class, 'rack_id');
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }
}
