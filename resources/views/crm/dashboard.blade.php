@extends('crm.layouts.app')
@section('title', 'Dashboard CRM - F&B Portal')

@section('styles')
<style>
    .welcome-header {
        margin-bottom: 32px;
        background: var(--crm-bg-card);
        border: 1px solid var(--crm-border);
        border-radius: var(--crm-radius);
        padding: 24px;
        box-shadow: var(--crm-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .welcome-text h2 { margin: 0 0 8px; font-size: 24px; color: var(--crm-primary); }
    .welcome-text p { margin: 0; color: #64748b; font-size: 14px; }
    
    .quick-actions { display: flex; gap: 12px; }

    .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
    .metric-card {
        background: var(--crm-bg-card);
        border: 1px solid var(--crm-border);
        padding: 24px; 
        border-radius: var(--crm-radius); 
        box-shadow: var(--crm-shadow);
        transition: var(--crm-transition);
        display: flex;
        flex-direction: column;
    }
    .metric-card:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(12, 53, 39, 0.12); }
    
    .metric-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
    .metric-icon { 
        width: 48px; height: 48px; 
        border-radius: 12px; 
        background: var(--crm-secondary); 
        color: var(--crm-primary); 
        display: flex; align-items: center; justify-content: center; 
        font-size: 24px; 
    }
    .metric-title { font-size: 13px; font-weight: 600; color: #64748b; }
    .metric-value { font-size: 32px; font-weight: 700; color: var(--crm-primary); letter-spacing: -0.5px; }
    .metric-value.success { color: var(--text-accent); }
    .metric-value.info { color: var(--text-accent); }
    .metric-unit { font-size: 14px; color: #64748b; font-weight: 600; margin-left: 4px; }
    
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px; }
    .chart-card {
        background: var(--crm-bg-card);
        border: 1px solid var(--crm-border);
        padding: 24px;
        border-radius: var(--crm-radius);
        box-shadow: var(--crm-shadow);
    }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .chart-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: var(--crm-primary); }

    /* Custom Chart Bars */
    .bar-chart-container { height: 220px; display: flex; align-items: flex-end; gap: 12px; padding-top: 10px; }
    .bar-chart-col { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; height: 100%; position: relative; }
    .bar-chart-bar { 
        width: 100%; 
        background: linear-gradient(180deg, var(--crm-primary) 0%, var(--crm-secondary) 100%); 
        border-radius: 6px 6px 0 0; 
        transition: var(--crm-transition);
    }
    .bar-chart-bar:hover { filter: brightness(1.1); }
    .bar-chart-label { margin-top: 8px; font-size: 11px; color: #64748b; font-weight: 500; }

    /* Membership List */
    .membership-list { display: flex; flex-direction: column; gap: 16px; }
    .membership-item { display: flex; flex-direction: column; gap: 8px; }
    .membership-item-header { display: flex; justify-content: space-between; font-size: 13px; }
    .membership-item-label { color: #64748b; font-weight: 500; }
    .membership-item-val { color: var(--crm-primary); font-weight: 700; }
    .membership-progress { height: 8px; background: rgba(12,53,39,0.05); border-radius: 99px; overflow: hidden; }
    .membership-progress-bar { height: 100%; background: var(--crm-primary); border-radius: 99px; }
    
    .table-header { padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
    .table-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: var(--crm-primary); }
</style>
@endsection

@section('content')
<div class="welcome-header">
    <div class="welcome-text">
        <h2>Selamat Datang di CRM F&B</h2>
        <p>Pantau performa loyalitas pelanggan, reservasi, dan aktivitas terkini Anda hari ini.</p>
    </div>
    <div class="quick-actions">
        <a href="{{ route('crm.customers.create') }}" class="btn btn-primary"><i class="ph ph-user-plus"></i> Customer Baru</a>
        <a href="{{ route('crm.reservations.index') }}" class="btn btn-secondary"><i class="ph ph-calendar-plus"></i> Cek Reservasi</a>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-header">
            <h3 class="metric-title">Total Customer</h3>
            <div class="metric-icon"><i class="ph ph-users"></i></div>
        </div>
        <div class="metric-value">{{ number_format($totalCustomers) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-header">
            <h3 class="metric-title">Customer Baru (Bulan Ini)</h3>
            <div class="metric-icon"><i class="ph ph-user-circle-plus"></i></div>
        </div>
        <div class="metric-value success">+{{ number_format($newCustomers) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-header">
            <h3 class="metric-title">Repeat Customer</h3>
            <div class="metric-icon"><i class="ph ph-arrows-clockwise"></i></div>
        </div>
        <div class="metric-value info">{{ number_format($repeatCustomers) }}</div>
    </div>
    <div class="metric-card" style="background: var(--crm-primary); color: white;">
        <div class="metric-header">
            <h3 class="metric-title" style="color: rgba(255,255,255,0.7);">Total Pendapatan</h3>
            <div class="metric-icon" style="background: rgba(255,255,255,0.1); color: white;"><i class="ph ph-wallet"></i></div>
        </div>
        <div class="metric-value" style="color: white;">
            <span class="metric-unit" style="color: rgba(255,255,255,0.7); margin-left:0; margin-right:4px;">Rp</span>{{ number_format($totalSpending / 1000000, 1) }}<span class="metric-unit" style="color: rgba(255,255,255,0.7);">M</span>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3>Pertumbuhan Customer (Tahun Ini)</h3>
        </div>
        <div class="bar-chart-container">
            @php 
                $maxGrowth = $growthData->max('total') ?: 1; 
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            @endphp
            @foreach(range(1, 12) as $m)
                @php
                    $monthData = $growthData->firstWhere('month', $m);
                    $total = $monthData ? $monthData->total : 0;
                    $height = ($total / $maxGrowth) * 100;
                    if($height == 0) $height = 2; // min height
                @endphp
                <div class="bar-chart-col" title="{{ $total }} Customers">
                    <div class="bar-chart-bar" style="height: {{ $height }}%;"></div>
                    <div class="bar-chart-label">{{ $months[$m-1] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3>Distribusi Membership</h3>
        </div>
        <div class="membership-list">
            @php $totalDist = $membershipDistribution->sum('total') ?: 1; @endphp
            @foreach(['Guest', 'Silver', 'Gold', 'Platinum', 'Diamond'] as $level)
                @php 
                    $count = isset($membershipDistribution[$level]) ? $membershipDistribution[$level]->total : 0;
                    $pct = round(($count / $totalDist) * 100);
                @endphp
                <div class="membership-item">
                    <div class="membership-item-header">
                        <span class="membership-item-label">{{ $level }}</span>
                        <span class="membership-item-val">{{ $pct }}%</span>
                    </div>
                    <div class="membership-progress">
                        <div class="membership-progress-bar" style="width: {{ $pct }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="table-wrapper">
    <div class="table-header">
        <h3>Top Customer (Lifetime Value)</h3>
        <a href="{{ route('crm.customers.index') }}" class="btn btn-outline" style="padding: 6px 14px; font-size: 12px; border-radius: 8px;">Lihat Semua</a>
    </div>
    <table class="crm-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Membership</th>
                <th>Terdaftar</th>
                <th>Total Point</th>
                <th>Total Spending</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topCustomers as $c)
            <tr>
                <td>
                    <div style="font-weight: 600; color: #1e293b;">{{ $c->name }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-phone"></i> {{ $c->phone }}</div>
                </td>
                <td><span class="badge badge-success">{{ $c->membership_level }}</span></td>
                <td>{{ $c->created_at->format('d/m/Y') }}</td>
                <td style="color: var(--crm-primary); font-weight: 700;">{{ number_format($c->total_points) }} pts</td>
                <td style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($c->total_spending, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 32px; color: #94a3b8;">Belum ada data customer.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
