<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationLine extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'sales_quotation_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
