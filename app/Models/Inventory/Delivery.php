<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'inv_deliveries';
    protected $guarded = [];

    public function packing(): BelongsTo
    {
        return $this->belongsTo(Packing::class, 'packing_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryLine::class, 'delivery_id');
    }
}
