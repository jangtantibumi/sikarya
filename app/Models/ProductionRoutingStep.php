<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionRoutingStep extends Model
{
    protected $fillable = [
        'production_routing_id',
        'sequence',
        'work_center',
        'expected_duration_minutes',
        'instructions'
    ];

    protected $casts = [
        'sequence' => 'integer',
        'expected_duration_minutes' => 'decimal:2',
    ];

    public function routing()
    {
        return $this->belongsTo(ProductionRouting::class, 'production_routing_id');
    }
}
