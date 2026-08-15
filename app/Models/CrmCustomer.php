<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_customers';

    protected $fillable = [
        'customer_code',
        'referral_code',
        'referred_by_id',
        'name',
        'phone',
        'email',
        'gender',
        'birth_date',
        'address',
        'notes',
        'membership_level',
        'segment_id',
        'total_points',
        'total_spending',
        'last_visit',
        'is_active',
        'is_blacklisted',
        'blacklist_reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_visit' => 'datetime',
        'is_active' => 'boolean',
        'is_blacklisted' => 'boolean',
        'total_points' => 'integer',
        'total_spending' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $latest = static::max('id') + 1;
                $customer->customer_code = 'CUST-'.str_pad($latest, 5, '0', STR_PAD_LEFT);
            }
            if (empty($customer->referral_code)) {
                $customer->referral_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            }
        });
    }

    public function timelines()
    {
        return $this->hasMany(CrmCustomerTimeline::class, 'customer_id');
    }

    public function pointHistories()
    {
        return $this->hasMany(CrmCustomerPointHistory::class, 'customer_id');
    }

    public function reservations()
    {
        return $this->hasMany(CrmReservation::class, 'customer_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(CrmFeedback::class, 'customer_id');
    }

    public function tags()
    {
        return $this->belongsToMany(CrmTag::class, 'crm_customer_tag', 'customer_id', 'tag_id');
    }

    public function segment()
    {
        return $this->belongsTo(CrmSegment::class, 'segment_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(CrmCustomer::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(CrmReferral::class, 'referrer_id');
    }

    public function claimedVouchers()
    {
        return $this->hasMany(CrmCustomerVoucher::class, 'customer_id');
    }

    public function broadcastLogs()
    {
        return $this->hasMany(CrmBroadcastLog::class, 'customer_id');
    }
}
