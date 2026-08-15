@extends('crm.layouts.app')
@section('title', 'Edit Membership - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Membership Tier</h1>
        <p>Ubah tingkatan membership.</p>
    </div>
    <a href="{{ route('crm.memberships.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="{{ route('crm.memberships.update', $membership->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama Tier (Misal: Gold)</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $membership->name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Poin</label>
            <input type="number" name="min_points" class="form-control" required min="0" value="{{ old('min_points', $membership->min_points) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Diskon (%)</label>
            <input type="number" step="0.01" name="discount_percentage" class="form-control" required min="0" max="100" value="{{ old('discount_percentage', $membership->discount_percentage) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Benefit (Opsional)</label>
            <textarea name="benefits" class="form-control" rows="3">{{ old('benefits', $membership->benefits) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Warna Badge</label>
            <select name="color_badge" class="form-control">
                <option value="guest" {{ $membership->color_badge == 'guest' ? 'selected' : '' }}>Guest (Abu-abu)</option>
                <option value="silver" {{ $membership->color_badge == 'silver' ? 'selected' : '' }}>Silver (Perak)</option>
                <option value="gold" {{ $membership->color_badge == 'gold' ? 'selected' : '' }}>Gold (Emas)</option>
                <option value="platinum" {{ $membership->color_badge == 'platinum' ? 'selected' : '' }}>Platinum (Ungu)</option>
                <option value="diamond" {{ $membership->color_badge == 'diamond' ? 'selected' : '' }}>Diamond (Biru)</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>

<form action="{{ route('crm.memberships.destroy', $membership->id) }}" method="POST" style="margin-top: 24px;" onsubmit="return confirm('Yakin ingin menghapus tier ini?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus Tier</button>
</form>
@endsection
