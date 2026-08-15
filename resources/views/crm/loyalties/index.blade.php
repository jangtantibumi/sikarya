@extends('crm.layouts.app')
@section('title', 'Loyalty Rules - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Loyalty Rules</h1>
        <p>Atur cara pelanggan mendapatkan poin dari setiap transaksi (Misal: Rp 10.000 = 1 Poin).</p>
    </div>
    <div>
        <a href="{{ route('crm.loyalties.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Rule</a>
    </div>
</div>

<div class="table-wrapper">
    <table class="crm-table">
        <thead>
            <tr>
                <th>Nama Aturan</th>
                <th>Pembelanjaan</th>
                <th>Poin Didapat</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loyalties as $rule)
            <tr>
                <td style="font-weight: 600; color: #1e293b;">
                    {{ $rule->rule_name }}
                </td>
                <td style="color: #64748b; font-weight: 500;">Rp {{ number_format($rule->spending_amount, 0, ',', '.') }}</td>
                <td style="color: var(--crm-primary); font-weight: 700;">+{{ number_format($rule->points_awarded) }} Poin</td>
                <td>
                    @if($rule->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Nonaktif</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('crm.loyalties.edit', $rule->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada loyalty rule.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
