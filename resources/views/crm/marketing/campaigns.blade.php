@extends('crm.layouts.app')
@section('title', 'Campaign & Broadcast - Marketing CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Campaign & Broadcast</h1>
        <p>Kirim pesan massal WhatsApp & Email ke seluruh customer atau segmen terpolarisasi.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="{{ route('crm.marketing.broadcast-logs') }}" class="btn btn-outline"><i class="ph ph-receipt"></i> Log Broadcast</a>
        <a href="{{ route('crm.marketing.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Buat Campaign -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Buat Campaign Baru</h3>

        <form action="{{ route('crm.marketing.campaigns.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Judul Campaign</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Promo Weekend Diskon 20%" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Channel</label>
                    <select name="channel" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="broadcast">Semua Channel</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Target Audiens</label>
                    <select name="target_type" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="all">Semua Customer</option>
                        <option value="tag">Berdasarkan Tag</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Subjek (Khusus Email)</label>
                <input type="text" name="subject" class="form-control" placeholder="Subjek email menarik..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Isi Pesan / Template</label>
                <textarea name="message_body" rows="5" class="form-control" required placeholder="Halo {name}, dapatkan diskon 20% minggu ini karena kamu adalah member {membership}!" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border); font-family: inherit;"></textarea>
                <span style="font-size: 11px; color: #64748b;">Tag variabel: <code>{name}</code>, <code>{membership}</code>, <code>{points}</code>, <code>{customer_code}</code></span>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" name="send_now" value="1" id="send_now" checked>
                <label for="send_now" style="font-size: 13px; color: #334155; font-weight: 500;">Kirimkan Broadcast Langsung Sekarang</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-paper-plane-right"></i> Proses & Simpan Campaign</button>
        </form>
    </div>

    <!-- Tabel Daftar Campaign -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Campaign</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Judul Campaign</th>
                        <th>Channel</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Log Sent</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $camp)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $camp->title }}</div>
                                <div style="font-size: 12px; color: #64748b;">Dibuat: {{ $camp->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td><span class="badge badge-success">{{ strtoupper($camp->channel) }}</span></td>
                            <td>{{ ucfirst($camp->target_type) }}</td>
                            <td><span class="badge badge-success">{{ ucfirst($camp->status) }}</span></td>
                            <td><strong>{{ $camp->broadcast_logs_count }}</strong> logs</td>
                            <td style="text-align: right;">
                                @if($camp->status === 'draft')
                                    <form action="{{ route('crm.marketing.campaigns.send', $camp->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;">Exekusi Broadcast</button>
                                    </form>
                                @else
                                    <span style="font-size: 12px; color: var(--text-accent); font-weight: 600;">Terkirim</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada campaign broadcast.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
