<?php

declare(strict_types=1);

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\ExchangeRateFactory;

class ExchangeRate extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_exchange_rates';

    protected $fillable = [
        'company_id',
        'from_currency_id',
        'to_currency_id',
        'rate_date',
        'rate_type',
        'rate',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): ExchangeRateFactory
    {
        return ExchangeRateFactory::new();
    }

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }
}
