<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniEventInvitation extends Model
{
    protected $fillable = [
        'created_by_id',
        'division',
        'title',
        'message',
        'event_at',
        'location',
        'registration_url',
        'sent_count',
        'failed_count',
    ];

    protected function casts(): array
    {
        return ['event_at' => 'datetime'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function recipients()
    {
        return $this->hasMany(AlumniInvitationRecipient::class, 'invitation_id');
    }
}
