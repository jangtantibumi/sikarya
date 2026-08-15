<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInLine extends Model
{
    use HasFactory;

    protected $table = 'inv_stock_in_lines';

    protected $guarded = [];

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
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
