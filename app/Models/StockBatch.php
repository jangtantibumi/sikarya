<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'batch_number',
        'serial_number',
        'lot_number',
        'expired_date',
        'quantity',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
