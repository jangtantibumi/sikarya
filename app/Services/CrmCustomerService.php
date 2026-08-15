<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmBroadcastLog;
use App\Models\CrmCustomer;
use App\Models\CrmCustomerPointHistory;
use App\Models\CrmCustomerTimeline;
use App\Models\CrmCustomerVoucher;
use App\Models\CrmFeedback;
use App\Models\CrmReservation;
use Illuminate\Support\Facades\DB;

class CrmCustomerService
{
    public function mergeCustomers($sourceId, $targetId)
    {
        return DB::transaction(function () use ($sourceId, $targetId) {
            $source = CrmCustomer::findOrFail($sourceId);
            $target = CrmCustomer::findOrFail($targetId);

            if ($source->id === $target->id) {
                throw new \InvalidArgumentException('Customer sumber dan target tidak boleh sama.');
            }

            // 1. Gabungkan total poin dan pengeluaran
            $target->total_points += $source->total_points;
            $target->total_spending += $source->total_spending;

            // Update tanggal kunjungan terakhir jika sumber lebih baru
            if ($source->last_visit && (! $target->last_visit || $source->last_visit->gt($target->last_visit))) {
                $target->last_visit = $source->last_visit;
            }

            $target->save();

            // 2. Alihkan relasi data transaksi & riwayat
            CrmCustomerTimeline::where('customer_id', $source->id)->update(['customer_id' => $target->id]);
            CrmCustomerPointHistory::where('customer_id', $source->id)->update(['customer_id' => $target->id]);
            CrmReservation::where('customer_id', $source->id)->update(['customer_id' => $target->id]);
            CrmFeedback::where('customer_id', $source->id)->update(['customer_id' => $target->id]);
            CrmCustomerVoucher::where('customer_id', $source->id)->update(['customer_id' => $target->id]);
            CrmBroadcastLog::where('customer_id', $source->id)->update(['customer_id' => $target->id]);

            // 3. Gabungkan Tags
            $sourceTagIds = $source->tags()->pluck('crm_tags.id')->toArray();
            if (! empty($sourceTagIds)) {
                $target->tags()->syncWithoutDetaching($sourceTagIds);
            }

            // 4. Catat Log Timeline di Target
            CrmCustomerTimeline::create([
                'customer_id' => $target->id,
                'action' => 'MERGE_DUPLICATE',
                'description' => "Penggabungan data dari customer {$source->name} ({$source->customer_code}). Poin (+{$source->points} pts) & Spending (+Rp ".number_format($source->total_spending, 0, ',', '.').') ditransfer.',
            ]);

            // 5. Soft delete customer sumber
            $source->delete();

            return $target;
        });
    }

    public function exportExcel($customers)
    {
        $filename = 'customers-export-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID Customer',
                'Kode Customer',
                'Kode Referral',
                'Nama',
                'Nomor HP',
                'Email',
                'Jenis Kelamin',
                'Tanggal Lahir',
                'Level Membership',
                'Total Poin',
                'Total Spending (Rp)',
                'Status',
                'Blacklist Status',
                'Alasan Blacklist',
                'Tanggal Terdaftar',
            ]);

            foreach ($customers as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->customer_code,
                    $c->referral_code,
                    $c->name,
                    $c->phone,
                    $c->email,
                    $c->gender,
                    $c->birth_date ? $c->birth_date->format('Y-m-d') : '',
                    $c->membership_level,
                    $c->total_points,
                    $c->total_spending,
                    $c->is_active ? 'Aktif' : 'Nonaktif',
                    $c->is_blacklisted ? 'Ya' : 'Tidak',
                    $c->blacklist_reason,
                    $c->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
