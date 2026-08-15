@extends('crm.layouts.app')
@section('title', 'Voucher & Promo - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Voucher & Promo</h1>
        <p>Kelola kode voucher diskon untuk pelanggan.</p>
    </div>
    <div>
        <a href="{{ route('crm.vouchers.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Voucher</a>
    </div>
</div>

<div class="table-wrapper">
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Voucher</th>
                    <th>Nilai Diskon</th>
                    <th>Masa Berlaku</th>
                    <th>Terpakai</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                <tr>
                    <td style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--crm-primary);">
                        {{ $voucher->code }}
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $voucher->name }}</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Min. Beli: Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}</div>
                    </td>
                    <td style="color: var(--text-accent); font-weight: 700;">
                        {{ $voucher->type === 'percentage' ? number_format($voucher->value, 0) . '%' : 'Rp ' . number_format($voucher->value, 0, ',', '.') }}
                    </td>
                    <td style="color: #475569; font-size: 13px;">
                        {{ $voucher->valid_from ? $voucher->valid_from->format('d/m/Y') : '-' }} - 
                        {{ $voucher->valid_until ? $voucher->valid_until->format('d/m/Y') : '-' }}
                    </td>
                    <td style="color: #475569; font-size: 13px; font-weight: 500;">
                        {{ $voucher->uses_count }} / {{ $voucher->max_uses ?? '∞' }}
                    </td>
                    <td>
                        @if($voucher->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('crm.vouchers.edit', $voucher->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data voucher.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
