<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmBroadcastLog extends Model
{
    use HasFactory;

    protected $table = 'crm_broadcast_logs';

    protected $fillable = [
        'campaign_id',
        'customer_id',
        'channel',
        'recipient',
        'message',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(CrmCampaign::class, 'campaign_id');
    }

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
