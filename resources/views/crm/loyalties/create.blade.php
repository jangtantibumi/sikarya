@extends('crm.layouts.app')
@section('title', 'Tambah Loyalty Rule - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Loyalty Rule</h1>
        <p>Tentukan parameter perhitungan poin pelanggan.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="{{ route('crm.loyalties.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Aturan (Misal: Reguler Poin 10rb)</label>
            <input type="text" name="rule_name" class="form-control" required value="{{ old('rule_name') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Pembelanjaan (Rp)</label>
            <input type="number" name="spending_amount" class="form-control" required min="0" value="{{ old('spending_amount') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Poin yang Didapat</label>
            <input type="number" name="points_awarded" class="form-control" required min="0" value="{{ old('points_awarded') }}">
        </div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #1e293b; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--crm-primary);">
                Aturan Aktif
            </label>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="{{ route('crm.loyalties.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
