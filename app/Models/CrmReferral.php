<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmReferral extends Model
{
    use HasFactory;

    protected $table = 'crm_referrals';

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'reward_points',
        'status',
    ];

    public function referrer()
    {
        return $this->belongsTo(CrmCustomer::class, 'referrer_id');
    }

    public function referee()
    {
        return $this->belongsTo(CrmCustomer::class, 'referee_id');
    }
}
