<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CrmCustomer;
use App\Models\CrmCustomerTimeline;

class CrmCustomerObserver
{
    /**
     * Handle the CrmCustomer "creating" event.
     * Auto-generates customer_code and logs Register timeline.
     */
    public function creating(CrmCustomer $customer): void
    {
        if (empty($customer->customer_code)) {
            $lastCode = CrmCustomer::withTrashed()
                ->whereYear('created_at', now()->year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastCode) {
                $parts = explode('-', $lastCode->customer_code);
                $nextNumber = (int) end($parts) + 1;
            }

            $customer->customer_code = 'CUST-'.now()->year.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the CrmCustomer "created" event.
     * Auto-logs REGISTER timeline entry.
     */
    public function created(CrmCustomer $customer): void
    {
        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action' => 'REGISTER',
            'description' => 'Customer baru terdaftar dengan kode '.$customer->customer_code,
            'reference_id' => null,
        ]);
    }

    /**
     * Handle the CrmCustomer "updating" event.
     * Auto-upgrades/downgrades membership based on total_spending.
     */
    public function updating(CrmCustomer $customer): void
    {
        if ($customer->isDirty('total_spending')) {
            $spending = (float) $customer->total_spending;
            $newLevel = self::getMembershipLevel($spending);
            $customer->membership_level = $newLevel;
        }
    }

    /**
     * Determine membership level based on total spending.
     * Thresholds (in IDR):
     *   Guest    : < 500.000
     *   Silver   : 500.000  – 2.499.999
     *   Gold     : 2.500.000 – 9.999.999
     *   Platinum : 10.000.000 – 49.999.999
     *   Diamond  : >= 50.000.000
     */
    public static function getMembershipLevel(float $spending): string
    {
        if ($spending >= 50_000_000) {
            return 'Diamond';
        }
        if ($spending >= 10_000_000) {
            return 'Platinum';
        }
        if ($spending >= 2_500_000) {
            return 'Gold';
        }
        if ($spending >= 500_000) {
            return 'Silver';
        }

        return 'Guest';
    }

    public function updated(CrmCustomer $customer): void {}

    public function deleted(CrmCustomer $customer): void {}

    public function restored(CrmCustomer $customer): void {}

    public function forceDeleted(CrmCustomer $customer): void {}
}
