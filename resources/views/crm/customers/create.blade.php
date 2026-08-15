@extends('crm.layouts.app')
@section('title', 'Tambah Customer - CRM Portal')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Customer Baru</h1>
        <p>Masukkan data customer. Kode customer akan dibuat otomatis (CUST-YYYY-XXXX).</p>
    </div>
    <a href="{{ route('crm.customers.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 800px;">
    <form action="{{ route('crm.customers.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                @error('name')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nomor WhatsApp / HP *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="Contoh: 08123456789">
                @error('phone')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email (Opsional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Contoh: budi@email.com">
                @error('email')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Gender (Opsional)</label>
                <select name="gender" class="form-control">
                    <option value="">Pilih Gender</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('gender')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Lahir (Opsional)</label>
                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                @error('birth_date')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Alamat Lengkap (Opsional)</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Alamat pengiriman / domisili">{{ old('address') }}</textarea>
                @error('address')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan khusus, preferensi menu, alergi, dsb.">{{ old('notes') }}</textarea>
                @error('notes')<div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <a href="{{ route('crm.customers.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Customer</button>
        </div>
    </form>
</div>
@endsection
