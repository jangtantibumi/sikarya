<?php

declare(strict_types=1);

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\TaxMasterFactory;

class TaxMaster extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_tax_masters';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'rate',
        'tax_type',
        'calculation_type',
        'chart_of_account_id',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): TaxMasterFactory
    {
        return TaxMasterFactory::new();
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
