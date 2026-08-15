<?php

declare(strict_types=1);

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\ProfitCenterFactory;

class ProfitCenter extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_profit_centers';

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'parent_id',
        'manager_name',
        'segment',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): ProfitCenterFactory
    {
        return ProfitCenterFactory::new();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProfitCenter::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProfitCenter::class, 'parent_id');
    }
}
