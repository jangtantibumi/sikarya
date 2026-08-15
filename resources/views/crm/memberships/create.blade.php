@extends('crm.layouts.app')
@section('title', 'Tambah Membership - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Membership Tier</h1>
        <p>Buat tingkatan membership baru.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="{{ route('crm.memberships.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Tier (Misal: Gold)</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Poin</label>
            <input type="number" name="min_points" class="form-control" required min="0" value="{{ old('min_points', 0) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Diskon (%)</label>
            <input type="number" step="0.01" name="discount_percentage" class="form-control" required min="0" max="100" value="{{ old('discount_percentage', 0) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Benefit (Opsional)</label>
            <textarea name="benefits" class="form-control" rows="3">{{ old('benefits') }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Warna Badge</label>
            <select name="color_badge" class="form-control">
                <option value="guest">Guest (Abu-abu)</option>
                <option value="silver">Silver (Perak)</option>
                <option value="gold">Gold (Emas)</option>
                <option value="platinum">Platinum (Ungu)</option>
                <option value="diamond">Diamond (Biru)</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="{{ route('crm.memberships.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
