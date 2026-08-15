<?php

namespace App\Services;

use App\Models\CrmCustomer;
use App\Models\CrmCampaign;
use App\Models\CrmBroadcastLog;
use App\Models\CrmPromotion;
use App\Models\CrmVoucher;
use App\Models\CrmCoupon;
use App\Models\CrmReferral;
use App\Models\CrmCustomerTimeline;
use App\Models\CrmCustomerPointHistory;

class CrmMarketingService
{
    // ===================================
    // CAMPAIGN & BROADCAST
    // ===================================
    public function createCampaign(array $data)
    {
        $campaign = CrmCampaign::create([
            'title' => $data['title'],
            'channel' => $data['channel'] ?? 'whatsapp',
            'target_type' => $data['target_type'] ?? 'all',
            'target_id' => $data['target_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message_body' => $data['message_body'],
            'status' => $data['status'] ?? 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if (isset($data['send_now']) && $data['send_now']) {
            $this->executeCampaign($campaign->id);
        }

        return $campaign;
    }

    public function executeCampaign($campaignId)
    {
        $campaign = CrmCampaign::findOrFail($campaignId);

        $query = CrmCustomer::where('is_active', true)->where('is_blacklisted', false);

        if ($campaign->target_type === 'segment' && $campaign->target_id) {
            $query->where('segment_id', $campaign->target_id);
        } elseif ($campaign->target_type === 'tag' && $campaign->target_id) {
            $query->whereHas('tags', function ($q) use ($campaign) {
                $q->where('crm_tags.id', $campaign->target_id);
            });
        }

        $customers = $query->get();
        $dispatchedCount = 0;

        foreach ($customers as $customer) {
            $message = str_replace(
                ['{name}', '{membership}', '{customer_code}', '{points}'],
                [$customer->name, $customer->membership_level, $customer->customer_code, $customer->total_points],
                $campaign->message_body
            );

            $recipient = ($campaign->channel === 'email') ? ($customer->email ?: $customer->phone) : $customer->phone;

            CrmBroadcastLog::create([
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
                'channel' => $campaign->channel,
                'recipient' => $recipient ?: 'N/A',
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            CrmCustomerTimeline::create([
                'customer_id' => $customer->id,
                'action' => 'CAMPAIGN_BROADCAST',
                'description' => "Pesan Campaign '{$campaign->title}' dikirimkan via {$campaign->channel}.",
                'reference_id' => $campaign->id,
            ]);

            $dispatchedCount++;
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $dispatchedCount;
    }

    // ===================================
    // BIRTHDAY REMINDER & REWARDS
    // ===================================
    public function getUpcomingBirthdays()
    {
        $today = now();
        $customers = CrmCustomer::whereNotNull('birth_date')
            ->get()
            ->filter(function ($c) use ($today) {
                return $c->birth_date->month == $today->month;
            })
            ->values();

        return $customers;
    }

    public function sendBirthdayRewards($customerId, $bonusPoints = 100)
    {
        $customer = CrmCustomer::findOrFail($customerId);

        $customer->total_points += $bonusPoints;
        $customer->save();

        CrmCustomerPointHistory::create([
            'customer_id' => $customer->id,
            'points' => $bonusPoints,
            'description' => "Bonus Ulang Tahun (+{$bonusPoints} Pts)",
        ]);

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action' => 'BIRTHDAY_REWARD',
            'description' => "Selamat Ulang Tahun! Diberikan bonus {$bonusPoints} point.",
        ]);

        return $customer;
    }

    // ===================================
    // PROMOTION ENGINE
    // ===================================
    public function applyPromotion($promoCode, $cartTotal)
    {
        $promotion = CrmPromotion::where('promo_code', strtoupper($promoCode))
            ->where('is_active', true)
            ->first();

        if (!$promotion) {
            return ['valid' => false, 'message' => 'Kode promosi tidak ditemukan atau tidak aktif.'];
        }

        if ($promotion->valid_from && now()->lt($promotion->valid_from)) {
            return ['valid' => false, 'message' => 'Promosi belum dimulai.'];
        }

        if ($promotion->valid_until && now()->gt($promotion->valid_until)) {
            return ['valid' => false, 'message' => 'Promosi telah kadaluarsa.'];
        }

        if ($cartTotal < $promotion->min_spend) {
            return ['valid' => false, 'message' => 'Minimum transaksi untuk promo ini adalah Rp ' . number_format($promotion->min_spend, 0, ',', '.')];
        }

        if ($promotion->discount_type === 'percentage') {
            $discountAmount = ($cartTotal * $promotion->discount_value) / 100;
        } else {
            $discountAmount = $promotion->discount_value;
        }

        $discountAmount = min($discountAmount, $cartTotal);

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount_amount' => $discountAmount,
            'final_total' => $cartTotal - $discountAmount,
            'message' => 'Promo berhasil dipasang!',
        ];
    }

    // ===================================
    // COUPON ENGINE
    // ===================================
    public function generateCoupon($voucherId, $customerId = null)
    {
        $voucher = CrmVoucher::findOrFail($voucherId);

        $couponCode = 'CPN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $coupon = CrmCoupon::create([
            'coupon_code' => $couponCode,
            'voucher_id' => $voucher->id,
            'customer_id' => $customerId,
            'discount_amount' => $voucher->value,
            'is_used' => false,
            'expires_at' => $voucher->valid_until ?: now()->addDays(30),
        ]);

        return $coupon;
    }

    public function validateCoupon($couponCode)
    {
        $coupon = CrmCoupon::with('voucher')
            ->where('coupon_code', strtoupper($couponCode))
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Kode kupon tidak valid.'];
        }

        if ($coupon->is_used) {
            return ['valid' => false, 'message' => 'Kupon ini sudah pernah digunakan.'];
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return ['valid' => false, 'message' => 'Kupon ini telah kadaluarsa.'];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $coupon->discount_amount,
            'message' => 'Kupon berhasil diverifikasi!',
        ];
    }

    // ===================================
    // REFERRAL PROGRAM
    // ===================================
    public function processReferral(CrmCustomer $newCustomer, $referralCode)
    {
        if (empty($referralCode)) {
            return null;
        }

        $referrer = CrmCustomer::where('referral_code', strtoupper($referralCode))->first();
        if (!$referrer || $referrer->id === $newCustomer->id) {
            return null;
        }

        $newCustomer->update(['referred_by_id' => $referrer->id]);

        $rewardPoints = 50;

        $referral = CrmReferral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $newCustomer->id,
            'reward_points' => $rewardPoints,
            'status' => 'rewarded',
        ]);

        // Tambah poin ke pemberi referral
        $referrer->total_points += $rewardPoints;
        $referrer->save();

        CrmCustomerPointHistory::create([
            'customer_id' => $referrer->id,
            'points' => $rewardPoints,
            'description' => "Reward Referral: Merekrut {$newCustomer->name}",
        ]);

        CrmCustomerTimeline::create([
            'customer_id' => $referrer->id,
            'action' => 'REFERRAL_REWARD',
            'description' => "Mendapatkan {$rewardPoints} point referral karena mengundang {$newCustomer->name}.",
        ]);

        return $referral;
    }
}
