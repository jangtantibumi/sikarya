<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Packing extends Model
{
    use HasFactory;

    protected $table = 'inv_packings';
    protected $guarded = [];

    public function picking(): BelongsTo
    {
        return $this->belongsTo(Picking::class, 'picking_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PackingLine::class, 'packing_id');
    }
}
