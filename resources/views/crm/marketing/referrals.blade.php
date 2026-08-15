@extends('crm.layouts.app')
@section('title', 'Referral Program - Marketing CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Referral Program</h1>
        <p>Pantau performa program rujukan antar customer (Refer-a-Friend) dan klaim poin hadiah.</p>
    </div>
    <a href="{{ route('crm.marketing.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="table-wrapper">
    <div style="padding: 20px; border-bottom: 1px solid var(--crm-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--crm-primary);">Riwayat Transaksi Referral</h3>
        <span class="badge badge-success">{{ $referrals->total() }} Rujukan Berhasil</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer Pemberi Referral (Referrer)</th>
                    <th>Customer Baru (Referee)</th>
                    <th>Reward Points</th>
                    <th>Status Reward</th>
                    <th>Tanggal Join</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $ref)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $ref->referrer->name ?? 'Unknown' }}</div>
                            <div style="font-size: 12px; color: #64748b;">Kode Ref: <span style="font-family: monospace; font-weight: 700;">{{ $ref->referrer->referral_code ?? '-' }}</span></div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $ref->referee->name ?? 'Unknown' }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $ref->referee->phone ?? '-' }}</div>
                        </td>
                        <td style="color: var(--text-accent); font-weight: 700;">+{{ $ref->reward_points }} Pts</td>
                        <td><span class="badge badge-success">{{ ucfirst($ref->status) }}</span></td>
                        <td>{{ $ref->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 48px;">Belum ada data transaksi rujukan referral.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
