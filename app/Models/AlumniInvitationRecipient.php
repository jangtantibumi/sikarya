<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniInvitationRecipient extends Model
{
    protected $fillable = [
        'invitation_id',
        'user_id',
        'email',
        'status',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function invitation()
    {
        return $this->belongsTo(AlumniEventInvitation::class, 'invitation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
