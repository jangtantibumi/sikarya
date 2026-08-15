@extends('crm.layouts.app')
@section('title', 'Coupon Engine - Marketing CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Coupon Engine</h1>
        <p>Generasi kupon transaksi tunggal dari voucher master dan validasi kode kupon.</p>
    </div>
    <a href="{{ route('crm.marketing.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Generate Coupon -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Generate Kupon Baru</h3>

        <form action="{{ route('crm.marketing.coupons.generate') }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Pilih Voucher Master</label>
                <select name="voucher_id" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                    <option value="">-- Pilih Voucher --</option>
                    @foreach($vouchers as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} (Rp {{ number_format($v->value, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-ticket"></i> Generate Kode Kupon</button>
        </form>
    </div>

    <!-- Tabel Kupon Generated -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Kupon</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Kode Kupon</th>
                        <th>Voucher Master</th>
                        <th>Nilai Diskon</th>
                        <th>Pemilik Customer</th>
                        <th>Status Penggunaan</th>
                        <th>Tgl Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $cpn)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--crm-primary);">{{ $cpn->coupon_code }}</td>
                            <td>{{ $cpn->voucher->name ?? '-' }}</td>
                            <td style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($cpn->discount_amount, 0, ',', '.') }}</td>
                            <td>{{ $cpn->customer->name ?? 'Publik / Terbuka' }}</td>
                            <td>
                                @if($cpn->is_used)
                                    <span class="badge badge-danger">Sudah Dipakai</span>
                                @else
                                    <span class="badge badge-success">Belum Dipakai</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #64748b;">{{ $cpn->expires_at ? $cpn->expires_at->format('d/m/Y') : 'Tanpa Batas' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada kupon yang di-generate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
