<?php

declare(strict_types=1);

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\PaymentTermFactory;

class PaymentTerm extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_payment_terms';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'net_days',
        'discount_days',
        'discount_percentage',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'net_days' => 'integer',
        'discount_days' => 'integer',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PaymentTermFactory
    {
        return PaymentTermFactory::new();
    }
}
