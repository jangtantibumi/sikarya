@extends('crm.layouts.app')
@section('title', 'Detail Feedback - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Detail Feedback #{{ $feedback->id }}</h1>
        <p>Lihat atau selesaikan masalah pelanggan.</p>
    </div>
    <div>
        <a href="{{ route('crm.feedbacks.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">
    <div class="crm-card" style="flex: 2; min-width: 300px; margin-bottom: 0;">
        <h3 style="margin: 0 0 16px; color: var(--crm-primary); font-size: 16px; border-bottom: 1px solid var(--crm-border); padding-bottom: 16px;">Pesan Customer</h3>
        
        <div style="margin-top: 16px;">
            <div style="color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Rating</div>
            <div style="color: #f59e0b; font-size: 18px; margin-top: 4px;">
                @for($i=1; $i<=5; $i++)
                    {{ $i <= $feedback->rating ? '⭐' : '☆' }}
                @endfor
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <div style="color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kategori</div>
            <div style="color: #1e293b; font-size: 14px; font-weight: 600; margin-top: 6px;">{{ $feedback->category }}</div>
        </div>
        
        <div style="margin-top: 20px;">
            <div style="color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Pesan Lengkap</div>
            <div style="color: #334155; font-size: 14px; margin-top: 6px; background: rgba(0,0,0,0.03); padding: 16px; border-radius: 8px; line-height: 1.6; border: 1px solid rgba(0,0,0,0.05);">
                {{ $feedback->message }}
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <div style="color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Waktu Kirim</div>
            <div style="color: #475569; font-size: 13px; margin-top: 6px; font-weight: 500;">
                {{ $feedback->created_at->format('d F Y, H:i') }} ({{ $feedback->created_at->diffForHumans() }})
            </div>
        </div>
    </div>

    <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 24px;">
        <!-- Profil Customer -->
        <div class="crm-card" style="margin-bottom: 0;">
            <h3 style="margin: 0 0 16px; color: var(--crm-primary); font-size: 16px; border-bottom: 1px solid var(--crm-border); padding-bottom: 16px;">Profil Customer</h3>
            <div style="margin-top: 16px;">
                <div style="color: #1e293b; font-weight: 700; font-size: 15px;">{{ optional($feedback->customer)->name ?? 'Unknown' }}</div>
                <div style="color: #64748b; font-size: 13px; margin-top: 6px; font-weight: 500;"><i class="ph ph-phone"></i> {{ optional($feedback->customer)->phone ?? '-' }}</div>
                <div style="color: #64748b; font-size: 13px; margin-top: 6px; font-weight: 500;"><i class="ph ph-envelope-simple"></i> {{ optional($feedback->customer)->email ?? '-' }}</div>
            </div>
            @if($feedback->customer)
            <div style="margin-top: 20px;">
                <a href="{{ route('crm.customers.show', $feedback->customer_id) }}" class="btn btn-outline" style="width: 100%; justify-content: center; font-size: 13px;">Lihat Profil Lengkap</a>
            </div>
            @endif
        </div>

        <!-- Form Tindakan -->
        <div class="crm-card" style="margin-bottom: 0;">
            <h3 style="margin: 0 0 16px; color: var(--crm-primary); font-size: 16px; border-bottom: 1px solid var(--crm-border); padding-bottom: 16px;">Update Status</h3>
            <form action="{{ route('crm.feedbacks.update', $feedback->id) }}" method="POST" style="margin-top: 16px;">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Status Penanganan</label>
                    <select name="status" class="form-control" required>
                        <option value="Open" {{ $feedback->status == 'Open' ? 'selected' : '' }}>Open (Baru)</option>
                        <option value="In Progress" {{ $feedback->status == 'In Progress' ? 'selected' : '' }}>In Progress (Ditangani)</option>
                        <option value="Resolved" {{ $feedback->status == 'Resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Catatan Penanganan (Opsional)</label>
                    <textarea name="resolution_notes" class="form-control" rows="4" placeholder="Tuliskan tindakan yang telah diambil tim...">{{ old('resolution_notes', $feedback->resolution_notes) }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;"><i class="ph ph-floppy-disk"></i> Simpan Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
