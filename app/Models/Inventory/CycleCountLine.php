<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleCountLine extends Model
{
    use HasFactory;

    protected $table = 'inv_cycle_count_lines';

    protected $guarded = [];

    public function cycleCount(): BelongsTo
    {
        return $this->belongsTo(CycleCount::class, 'cycle_count_id');
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
