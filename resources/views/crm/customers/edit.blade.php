@extends('crm.layouts.app')
@section('title', 'Edit Customer - CRM Portal')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Data Customer</h1>
        <p>Ubah profil dan informasi customer: <strong>{{ $customer->customer_code }}</strong></p>
    </div>
    <a href="{{ route('crm.customers.show', $customer->id) }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Batal Edit</a>
</div>

<div class="crm-card" style="max-width: 800px;">
    <form action="{{ route('crm.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                @error('name')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nomor WhatsApp / HP *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                @error('phone')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email (Opsional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                @error('email')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Gender (Opsional)</label>
                <select name="gender" class="form-control">
                    <option value="">Pilih Gender</option>
                    <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('gender')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Lahir (Opsional)</label>
                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '') }}">
                @error('birth_date')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status Akun</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', $customer->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $customer->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('is_active')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Alamat Lengkap (Opsional)</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                @error('address')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                @error('notes')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
