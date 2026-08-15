@extends('crm.layouts.app')
@section('title', 'Promotion Engine - Marketing CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Promotion Engine</h1>
        <p>Atur aturan promosi diskon, batas transaksi minimum, dan periode berlaku promo.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="{{ route('crm.marketing.coupons') }}" class="btn btn-outline"><i class="ph ph-ticket"></i> Coupon Engine</a>
        <a href="{{ route('crm.marketing.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Buat Promo -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Buat Promosi Baru</h3>

        <form action="{{ route('crm.marketing.promotions.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Nama Promosi</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Promo Merdeka 45%" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Kode Promo</label>
                <input type="text" name="promo_code" class="form-control" placeholder="MERDEKA45" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border); font-family: monospace; text-transform: uppercase;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Tipe Diskon</label>
                    <select name="discount_type" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Nilai Diskon</label>
                    <input type="number" name="discount_value" class="form-control" placeholder="20 atau 50000" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Minimum Transaksi (Rp)</label>
                <input type="number" name="min_spend" class="form-control" value="0" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Berlaku Dari</label>
                    <input type="date" name="valid_from" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked>
                <label for="is_active" style="font-size: 13px; color: #334155; font-weight: 500;">Aktifkan Promo Ini</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-plus"></i> Simpan Aturan Promosi</button>
        </form>
    </div>

    <!-- Tabel Daftar Promo -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Promosi Aktif</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Kode Promo</th>
                        <th>Judul Promo</th>
                        <th>Nilai Diskon</th>
                        <th>Min Spend</th>
                        <th>Periode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promo)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--crm-primary);">{{ $promo->promo_code }}</td>
                            <td><strong style="color: #1e293b;">{{ $promo->title }}</strong></td>
                            <td style="color: var(--text-accent); font-weight: 700;">
                                @if($promo->discount_type === 'percentage')
                                    {{ $promo->discount_value }}%
                                @else
                                    Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>Rp {{ number_format($promo->min_spend, 0, ',', '.') }}</td>
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $promo->valid_from ? $promo->valid_from->format('d/m/Y') : 'Tanpa Batas' }}
                                -
                                {{ $promo->valid_until ? $promo->valid_until->format('d/m/Y') : 'Tanpa Batas' }}
                            </td>
                            <td>
                                @if($promo->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada aturan promosi yang dibuat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
