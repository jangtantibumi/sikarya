<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'client_inflow_id',
        'code',
        'name',
        'client_name',
        'project_type',
        'status',
        'start_date',
        'target_end_date',
        'contract_value',
        'budget_cost',
        'progress',
        'manager_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_end_date' => 'date',
            'contract_value' => 'decimal:2',
            'budget_cost' => 'decimal:2',
            'progress' => 'decimal:2',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function costs()
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
