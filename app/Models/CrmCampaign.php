<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCampaign extends Model
{
    use HasFactory;

    protected $table = 'crm_campaigns';

    protected $fillable = [
        'title',
        'channel',
        'target_type',
        'target_id',
        'subject',
        'message_body',
        'status',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function broadcastLogs()
    {
        return $this->hasMany(CrmBroadcastLog::class, 'campaign_id');
    }
}
