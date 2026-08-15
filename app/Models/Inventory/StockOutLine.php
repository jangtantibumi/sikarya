<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutLine extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_out_lines';
    protected $guarded = [];

    public function stockOut(): BelongsTo
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }
}
