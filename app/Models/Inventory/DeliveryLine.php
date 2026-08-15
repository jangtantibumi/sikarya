<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLine extends Model
{
    use HasFactory;

    protected $table = 'inv_delivery_lines';

    protected $guarded = [];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
