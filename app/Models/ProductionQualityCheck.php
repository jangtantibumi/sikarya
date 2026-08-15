<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductionQualityCheck extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'inspector_id',
        'status',
        'notes',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
