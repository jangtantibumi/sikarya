<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name',
        'phone',
        'email',
        'domicile',
        'project_value',
        'budget_text',
        'status',
        'source',
        'campaign',
        'type',
        'notes',
        'next_follow_up_at',
        'last_contacted_at',
        'first_response_at',
        'won_at',
        'lost_reason',
        'assigned_to',
        'created_by',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'project_value' => 'decimal:2',
            'next_follow_up_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'first_response_at' => 'datetime',
            'won_at' => 'datetime',
        ];
    }

    /**
     * Relationship: A lead belongs to a user (assignee).
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('occurred_at');
    }

    public function clientInflows()
    {
        return $this->hasMany(ClientInflow::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
