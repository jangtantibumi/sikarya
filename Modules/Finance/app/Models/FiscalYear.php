<?php

declare(strict_types=1);

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\FiscalYearFactory;

class FiscalYear extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_fiscal_years';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): FiscalYearFactory
    {
        return FiscalYearFactory::new();
    }

    public function periods(): HasMany
    {
        return $this->hasMany(FiscalPeriod::class, 'fiscal_year_id');
    }
}
