<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingLine extends Model
{
    use HasFactory;

    protected $table = 'inv_picking_lines';

    protected $guarded = [];

    public function picking(): BelongsTo
    {
        return $this->belongsTo(Picking::class, 'picking_id');
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
