@extends('crm.layouts.app')
@section('title', 'Edit Loyalty Rule - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Loyalty Rule</h1>
        <p>Ubah parameter perhitungan poin pelanggan.</p>
    </div>
    <a href="{{ route('crm.loyalties.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="{{ route('crm.loyalties.update', $loyalty->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama Aturan (Misal: Reguler Poin 10rb)</label>
            <input type="text" name="rule_name" class="form-control" required value="{{ old('rule_name', $loyalty->rule_name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Pembelanjaan (Rp)</label>
            <input type="number" name="spending_amount" class="form-control" required min="0" value="{{ old('spending_amount', (int)$loyalty->spending_amount) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Poin yang Didapat</label>
            <input type="number" name="points_awarded" class="form-control" required min="0" value="{{ old('points_awarded', $loyalty->points_awarded) }}">
        </div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #1e293b; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ $loyalty->is_active ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--crm-primary);">
                Aturan Aktif
            </label>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>

<form action="{{ route('crm.loyalties.destroy', $loyalty->id) }}" method="POST" style="margin-top: 24px;" onsubmit="return confirm('Yakin ingin menghapus aturan ini?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus Rule</button>
</form>
@endsection
