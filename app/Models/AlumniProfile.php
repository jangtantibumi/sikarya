<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{
    protected $fillable = [
        'user_id',
        'former_role',
        'former_division',
        'current_employer',
        'current_position',
        'industry',
        'city',
        'linkedin_url',
        'portfolio_url',
        'bio',
        'skills',
        'available_for_opportunities',
        'receive_event_invitations',
        'last_profile_update_at',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'available_for_opportunities' => 'boolean',
            'receive_event_invitations' => 'boolean',
            'last_profile_update_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
