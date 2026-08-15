<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSeparation extends Model
{
    protected $fillable = [
        'user_id',
        'initiated_by_id',
        'approved_by_id',
        'team_request_id',
        'completion_status',
        'converted_to_alumni',
        'backup_path',
        'separation_reason',
        'notes',
        'effective_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'converted_to_alumni' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function teamRequest()
    {
        return $this->belongsTo(TeamRequest::class);
    }
}
