<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'kpi_plan_id', 'title', 'target_value', 'unit', 'weight',
        'direction', 'aggregation_type', 'data_source', 'current_value',
    ];

    public function plan()
    {
        return $this->belongsTo(KpiPlan::class, 'kpi_plan_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
