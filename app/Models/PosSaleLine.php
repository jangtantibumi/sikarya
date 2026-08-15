<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PosSaleLine extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'pos_sale_id', 'product_id', 'quantity', 'unit_price', 'line_total'];
}
