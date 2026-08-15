@extends('crm.layouts.app')
@section('title', 'Buat Reservasi - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Buat Reservasi</h1>
        <p>Tambahkan jadwal reservasi baru untuk customer.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="{{ route('crm.reservations.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-control" required>
                <option value="">Pilih Customer...</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->phone }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Reservasi</label>
                <input type="date" name="reservation_date" class="form-control" required value="{{ old('reservation_date') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Jam / Waktu</label>
                <input type="time" name="reservation_time" class="form-control" required value="{{ old('reservation_time') }}">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Jumlah Pax (Orang)</label>
                <input type="number" name="pax" class="form-control" required min="1" value="{{ old('pax', 2) }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Preferensi Meja (Opsional)</label>
                <input type="text" name="table_preference" class="form-control" value="{{ old('table_preference') }}" placeholder="Contoh: Dekat jendela">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Permintaan Khusus</label>
            <textarea name="special_requests" class="form-control" rows="3" placeholder="Contoh: Ulang tahun, alergi kacang">{{ old('special_requests') }}</textarea>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Reservasi</button>
            <a href="{{ route('crm.reservations.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
