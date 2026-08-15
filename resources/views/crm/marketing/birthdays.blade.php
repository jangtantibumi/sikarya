@extends('crm.layouts.app')
@section('title', 'Birthday Reminder - Marketing CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Birthday Reminder & Automated Rewards</h1>
        <p>Identifikasi customer yang berulang tahun bulan ini dan berikan ucapan serta poin apresiasi otomatis.</p>
    </div>
    <a href="{{ route('crm.marketing.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="table-wrapper">
    <div style="padding: 20px; border-bottom: 1px solid var(--crm-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--crm-primary);">Customer Ulang Tahun Bulan Ini ({{ now()->format('F Y') }})</h3>
        <span class="badge badge-success">{{ count($upcomingBirthdays) }} Customer</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Nomor HP / WhatsApp</th>
                    <th>Tanggal Ulang Tahun</th>
                    <th>Total Poin</th>
                    <th>Level Membership</th>
                    <th style="text-align: right;">Aksi Reward</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomingBirthdays as $cust)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $cust->name }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $cust->customer_code }}</div>
                        </td>
                        <td>{{ $cust->phone }}</td>
                        <td style="font-weight: 700; color: #d97706;"><i class="ph ph-cake"></i> {{ $cust->birth_date ? $cust->birth_date->format('d F Y') : '-' }}</td>
                        <td style="font-weight: 700; color: var(--crm-primary);">{{ number_format($cust->total_points) }} pts</td>
                        <td><span class="badge badge-success">{{ $cust->membership_level }}</span></td>
                        <td style="text-align: right;">
                            <form action="{{ route('crm.marketing.birthdays.reward', $cust->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;"><i class="ph ph-gift"></i> Kirim Bonus +100 Pts</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 48px;">Tidak ada customer yang berulang tahun pada bulan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
