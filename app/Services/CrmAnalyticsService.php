<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CrmCustomer;
use App\Models\CrmCustomerTimeline;

class CrmAnalyticsService
{
    public function getAnalyticsOverview()
    {
        $totalCustomers = CrmCustomer::count();
        $activeCustomers = CrmCustomer::where('is_active', true)->where('is_blacklisted', false)->count();
        $totalSpending = CrmCustomer::sum('total_spending') ?: 0;

        $clvData = $this->getClvMetrics();
        $rfmData = $this->getRfmAnalysis();
        $repeatData = $this->getRepeatCustomerMetrics();
        $churnData = $this->getChurnAnalysis();
        $spendingTrends = $this->getSpendingTrends();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'total_spending' => $totalSpending,
            'clv' => $clvData,
            'rfm' => $rfmData,
            'repeat' => $repeatData,
            'churn' => $churnData,
            'trends' => $spendingTrends,
        ];
    }

    public function getClvMetrics()
    {
        $totalCustomers = CrmCustomer::count();
        if ($totalCustomers == 0) {
            return [
                'avg_clv' => 0,
                'avg_order_value' => 0,
                'avg_frequency' => 0,
                'avg_lifespan_years' => 1.5,
                'top_clv_customers' => collect(),
            ];
        }

        $totalSpending = CrmCustomer::sum('total_spending') ?: 0;
        $totalOrders = CrmCustomerTimeline::whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD'])->count() ?: $totalCustomers;

        $avgOrderValue = $totalOrders > 0 ? ($totalSpending / $totalOrders) : 0;
        $avgFrequency = $totalCustomers > 0 ? ($totalOrders / $totalCustomers) : 0;
        $avgLifespanYears = 1.5; // standar perkiraan siklus hidup customer retail/F&B

        $avgClv = $avgOrderValue * $avgFrequency * $avgLifespanYears;

        $topClvCustomers = CrmCustomer::orderBy('total_spending', 'desc')->take(10)->get()->map(function ($c) use ($avgLifespanYears) {
            $orderCount = $c->timelines()->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD'])->count() ?: 1;
            $aov = $c->total_spending / max($orderCount, 1);
            $c->estimated_clv = $aov * $orderCount * $avgLifespanYears;

            return $c;
        });

        return [
            'avg_clv' => round($avgClv, 2),
            'avg_order_value' => round($avgOrderValue, 2),
            'avg_frequency' => round($avgFrequency, 2),
            'avg_lifespan_years' => $avgLifespanYears,
            'top_clv_customers' => $topClvCustomers,
        ];
    }

    public function getRfmAnalysis()
    {
        $customers = CrmCustomer::withCount(['timelines as frequency' => function ($q) {
            $q->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD', 'RESERVATION_CREATED']);
        }])->get();

        $segments = [
            'Champions' => 0,
            'Loyal' => 0,
            'Potential Loyalist' => 0,
            'At Risk' => 0,
            'Lost' => 0,
        ];

        $classifiedCustomers = $customers->map(function ($customer) use (&$segments) {
            $daysSinceVisit = $customer->last_visit ? now()->diffInDays($customer->last_visit) : 999;

            // Recency Score (1 - 5)
            if ($daysSinceVisit <= 7) {
                $rScore = 5;
            } elseif ($daysSinceVisit <= 30) {
                $rScore = 4;
            } elseif ($daysSinceVisit <= 60) {
                $rScore = 3;
            } elseif ($daysSinceVisit <= 90) {
                $rScore = 2;
            } else {
                $rScore = 1;
            }

            // Frequency Score (1 - 5)
            $freq = $customer->frequency ?: 1;
            if ($freq >= 10) {
                $fScore = 5;
            } elseif ($freq >= 5) {
                $fScore = 4;
            } elseif ($freq >= 3) {
                $fScore = 3;
            } elseif ($freq >= 2) {
                $fScore = 2;
            } else {
                $fScore = 1;
            }

            // Monetary Score (1 - 5)
            $spend = (float) $customer->total_spending;
            if ($spend >= 10000000) {
                $mScore = 5;
            } elseif ($spend >= 5000000) {
                $mScore = 4;
            } elseif ($spend >= 2000000) {
                $mScore = 3;
            } elseif ($spend >= 500000) {
                $mScore = 2;
            } else {
                $mScore = 1;
            }

            $avgScore = ($rScore + $fScore + $mScore) / 3;

            if ($avgScore >= 4.2) {
                $segmentName = 'Champions';
            } elseif ($avgScore >= 3.4) {
                $segmentName = 'Loyal';
            } elseif ($avgScore >= 2.6) {
                $segmentName = 'Potential Loyalist';
            } elseif ($avgScore >= 1.8) {
                $segmentName = 'At Risk';
            } else {
                $segmentName = 'Lost';
            }

            $segments[$segmentName]++;
            $customer->rfm_score = "R{$rScore}-F{$fScore}-M{$mScore}";
            $customer->rfm_segment = $segmentName;

            return $customer;
        });

        return [
            'segments' => $segments,
            'classified_customers' => $classifiedCustomers,
        ];
    }

    public function getRepeatCustomerMetrics()
    {
        $totalCustomers = CrmCustomer::count();
        if ($totalCustomers == 0) {
            return [
                'total_customers' => 0,
                'repeat_customers' => 0,
                'single_customers' => 0,
                'repeat_rate' => 0,
            ];
        }

        $repeatCustomers = CrmCustomer::whereHas('timelines', function ($q) {
            $q->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD', 'RESERVATION_CREATED']);
        }, '>=', 2)->count();

        // Atau berdasarkan total_spending jika > 0 dan lebih dari 1 transaksi
        if ($repeatCustomers == 0) {
            $repeatCustomers = CrmCustomer::where('total_spending', '>', 0)->count();
        }

        $singleCustomers = max(0, $totalCustomers - $repeatCustomers);
        $repeatRate = ($repeatCustomers / $totalCustomers) * 100;

        return [
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'single_customers' => $singleCustomers,
            'repeat_rate' => round($repeatRate, 1),
        ];
    }

    public function getChurnAnalysis()
    {
        $totalCustomers = CrmCustomer::count();
        if ($totalCustomers == 0) {
            return [
                'churned_count' => 0,
                'churn_rate' => 0,
                'active_count' => 0,
                'risk_customers' => collect(),
            ];
        }

        // Churned: Tidak ada visit/aktivitas > 90 hari
        $cutoffDate = now()->subDays(90);

        $churnedCustomers = CrmCustomer::where(function ($q) use ($cutoffDate) {
            $q->where('last_visit', '<', $cutoffDate)
                ->orWhereNull('last_visit');
        })->get();

        $churnedCount = $churnedCustomers->count();
        $churnRate = ($churnedCount / $totalCustomers) * 100;
        $activeCount = $totalCustomers - $churnedCount;

        // Customer berisiko churn (visit 60-90 hari lalu)
        $riskCustomers = CrmCustomer::whereBetween('last_visit', [now()->subDays(90), now()->subDays(60)])
            ->orderBy('total_spending', 'desc')
            ->take(10)
            ->get();

        return [
            'churned_count' => $churnedCount,
            'churn_rate' => round($churnRate, 1),
            'active_count' => $activeCount,
            'risk_customers' => $riskCustomers,
        ];
    }

    public function getSpendingTrends()
    {
        $months = collect();
        foreach (range(11, 0) as $i) {
            $date = now()->subMonths($i);
            $monthNum = $date->month;
            $yearNum = $date->year;
            $label = $date->format('M Y');

            $monthlySpending = CrmCustomerTimeline::whereMonth('created_at', $monthNum)
                ->whereYear('created_at', $yearNum)
                ->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD'])
                ->count() * 150000; // estimasi perkalian transaksi bila detail order di timeline

            if ($monthlySpending == 0) {
                // Fallback pencatatan agregat customer bulan ini
                $custSpending = CrmCustomer::whereMonth('created_at', $monthNum)
                    ->whereYear('created_at', $yearNum)
                    ->sum('total_spending') ?: 0;
                $monthlySpending = (float) $custSpending;
            }

            $months->push([
                'label' => $label,
                'month' => $monthNum,
                'year' => $yearNum,
                'spending' => $monthlySpending,
            ]);
        }

        return $months;
    }
}
