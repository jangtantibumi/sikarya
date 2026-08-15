@extends('crm.layouts.app')
@section('title', $customer->name . ' - CRM Detail')

@section('styles')
<style>
    .header-profile { display: flex; gap: 24px; align-items: center; }
    .avatar { width: 72px; height: 72px; border-radius: 20px; background: var(--crm-secondary); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; color: var(--crm-primary); border: 1px solid var(--crm-border); }
    .page-title-group h1 { margin: 0; font-size: 28px; font-weight: 700; color: var(--crm-primary); display: flex; align-items: center; gap: 12px; }
    
    /* Tabs System */
    .tabs-header { display: flex; gap: 8px; border-bottom: 1px solid var(--crm-border); margin-bottom: 24px; overflow-x: auto; padding-bottom: 0; }
    .tab-btn { padding: 14px 24px; color: #64748b; font-size: 14px; font-weight: 600; cursor: pointer; transition: var(--crm-transition); border-bottom: 2px solid transparent; white-space: nowrap; }
    .tab-btn:hover { color: var(--crm-primary); background: rgba(0,0,0,0.02); }
    .tab-btn.active { color: var(--crm-primary); border-bottom-color: var(--crm-primary); }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* Cards and Lists */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .info-card { background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow); }
    .info-card h3 { margin: 0 0 20px; font-size: 16px; font-weight: 600; color: var(--crm-primary); border-bottom: 1px solid var(--crm-border); padding-bottom: 16px; }
    .info-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
    .info-row:last-child { margin-bottom: 0; }
    .info-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 14px; color: #1e293b; font-weight: 500; }

    /* Timeline */
    .timeline { position: relative; padding-left: 28px; }
    .timeline::before { content: ''; position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: rgba(12, 53, 39, 0.1); }
    .timeline-item { position: relative; margin-bottom: 28px; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-item::before { content: ''; position: absolute; left: -26px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--crm-secondary); border: 2px solid var(--crm-primary); }
    .timeline-date { font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; }
    .timeline-title { font-size: 14px; font-weight: 600; color: var(--crm-primary); margin-bottom: 4px; }
    .timeline-desc { font-size: 14px; color: #475569; line-height: 1.5; }

    .form-input-sm { background: #fff; border: 1px solid var(--crm-border); color: #1e293b; padding: 10px 14px; border-radius: 8px; font-family: inherit; font-size: 13.5px; outline: none; width: 100%; margin-bottom: 12px; transition: var(--crm-transition); }
    .form-input-sm:focus { border-color: var(--crm-primary); box-shadow: 0 0 0 3px rgba(12, 53, 39, 0.15); }
    
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.8; }
    .empty-state h3 { border: none; margin-bottom: 8px; justify-content: center; font-size: 18px; color: var(--crm-primary); }
    .empty-state p { color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto; line-height: 1.5; }

    .tag-chip { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #fff; margin-right: 6px; }
</style>
@endsection

@section('content')
<div class="page-header" style="align-items: center;">
    <div class="header-profile">
        <div class="avatar">{{ substr($customer->name, 0, 1) }}</div>
        <div class="page-title-group">
            <h1>
                {{ $customer->name }}
                <span class="badge badge-success">{{ $customer->membership_level }}</span>
                @if($customer->is_blacklisted)
                    <span class="badge badge-danger"><i class="ph ph-prohibit"></i> Blacklisted</span>
                @endif
            </h1>
            <p>{{ $customer->customer_code }} • Terdaftar sejak {{ $customer->created_at->format('d F Y') }}</p>
        </div>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <form action="{{ route('crm.customers.blacklist', $customer->id) }}" method="POST">
            @csrf
            @if($customer->is_blacklisted)
                <button type="submit" class="btn btn-outline" style="border-color: var(--text-accent); color: var(--text-accent);"><i class="ph ph-check-circle"></i> Lepas Blacklist</button>
            @else
                <button type="submit" class="btn btn-outline" style="border-color: #dc2626; color: #dc2626;" onclick="return confirm('Apakah Anda yakin ingin memasukkan customer ini ke blacklist?');"><i class="ph ph-prohibit"></i> Set Blacklist</button>
            @endif
        </form>
        <a href="{{ route('crm.customers.edit', $customer->id) }}" class="btn btn-outline"><i class="ph ph-pencil-simple"></i> Edit Profil</a>
        <form action="{{ route('crm.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus customer ini?');" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus</button>
        </form>
    </div>
</div>

<div class="tabs-header">
    <div class="tab-btn active" onclick="switchTab('overview', this)">Overview</div>
    <div class="tab-btn" onclick="switchTab('timeline', this)">Timeline</div>
    <div class="tab-btn" onclick="switchTab('referral', this)">Referral Program</div>
    <div class="tab-btn" onclick="switchTab('reservation', this)">Reservation</div>
    <div class="tab-btn" onclick="switchTab('points', this)">Point Management</div>
    <div class="tab-btn" onclick="switchTab('feedback', this)">Feedback</div>
</div>

<!-- 1. OVERVIEW -->
<div id="tab-overview" class="tab-content active">
    <div class="grid-2">
        <div class="info-card">
            <h3>Data Profil Pribadi</h3>
            <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value">{{ $customer->name }}</span></div>
            <div class="info-row"><span class="info-label">No. Telepon / WhatsApp</span><span class="info-value">{{ $customer->phone }}</span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value">{{ $customer->email ?? '-' }}</span></div>
            <div class="info-row"><span class="info-label">Kode Referral</span><span class="info-value" style="font-family: monospace; font-weight: 700; color: var(--crm-primary);">{{ $customer->referral_code ?? '-' }}</span></div>
            <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ $customer->gender ? ucfirst($customer->gender) : '-' }}</span></div>
            <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value">{{ $customer->birth_date ? $customer->birth_date->format('d F Y') : '-' }}</span></div>
            <div class="info-row"><span class="info-label">Last Visit</span><span class="info-value">{{ $customer->last_visit ? $customer->last_visit->format('d F Y H:i') : 'Belum Pernah' }}</span></div>
            <div class="info-row"><span class="info-label">Alamat Lengkap</span><span class="info-value">{{ $customer->address ?? '-' }}</span></div>
            <div class="info-row">
                <span class="info-label">Tags Customer</span>
                <span class="info-value">
                    @forelse($customer->tags as $t)
                        <span class="tag-chip" style="background-color: {{ $t->color }};">{{ $t->name }}</span>
                    @empty
                        <span style="color: #94a3b8; font-size: 13px;">Belum ada tag</span>
                    @endforelse
                </span>
            </div>
            @if($customer->is_blacklisted)
                <div class="info-row" style="background: rgba(220,38,38,0.05); padding: 12px; border-radius: 8px; border: 1px solid rgba(220,38,38,0.2);">
                    <span class="info-label" style="color: #dc2626;">Alasan Blacklist</span>
                    <span class="info-value" style="color: #dc2626;">{{ $customer->blacklist_reason ?? 'Tidak ada alasan khusus.' }}</span>
                </div>
            @endif
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="info-card" style="background: var(--crm-primary); border-color: var(--crm-primary);">
                <h3 style="border-color: rgba(255,255,255,0.1); color: white;">Tingkat Membership</h3>
                <div style="font-size: 36px; font-weight: 800; color: var(--crm-secondary); margin-bottom: 12px; letter-spacing: -0.5px;">{{ $customer->membership_level }}</div>
                <div style="color: rgba(255,255,255,0.8); font-size: 14px;">Total Spending: <strong style="color: white; font-size: 16px;">Rp {{ number_format($customer->total_spending, 0, ',', '.') }}</strong></div>
                <div style="color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px;">Total Point: <strong style="color: white; font-size: 16px;">{{ number_format($customer->total_points) }} pts</strong></div>
            </div>
        </div>
    </div>
</div>

<!-- 2. TIMELINE -->
<div id="tab-timeline" class="tab-content">
    <div class="info-card">
        <h3>Riwayat Aktivitas & Timeline Customer</h3>
        @if($customer->timelines->count() > 0)
            <div class="timeline">
                @foreach($customer->timelines as $timeline)
                    <div class="timeline-item">
                        <div class="timeline-date">{{ $timeline->created_at->format('d M Y, H:i') }}</div>
                        <div class="timeline-title">{{ $timeline->action }}</div>
                        <div class="timeline-desc">{{ $timeline->description }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 30px;">
                <p>Belum ada riwayat timeline.</p>
            </div>
        @endif
    </div>
</div>

<!-- 3. REFERRAL PROGRAM -->
<div id="tab-referral" class="tab-content">
    <div class="info-card">
        <h3>Statistik Referral Customer</h3>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
            Kode Referral Unik: <strong style="font-family: monospace; font-size: 16px; color: var(--crm-primary);">{{ $customer->referral_code }}</strong>
        </p>

        @if($customer->referrals->count() > 0)
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Customer Ditingkatkan (Referee)</th>
                        <th>Reward Point</th>
                        <th>Status</th>
                        <th>Tanggal Join</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->referrals as $ref)
                        <tr>
                            <td><strong>{{ $ref->referee->name ?? 'Unknown' }}</strong> ({{ $ref->referee->phone ?? '-' }})</td>
                            <td style="color: var(--text-accent); font-weight: 700;">+{{ $ref->reward_points }} Pts</td>
                            <td><span class="badge badge-success">{{ ucfirst($ref->status) }}</span></td>
                            <td>{{ $ref->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state" style="padding: 30px;">
                <p>Customer ini belum pernah merekrut teman melalui kode referral.</p>
            </div>
        @endif
    </div>
</div>

<!-- 4. RESERVATION -->
<div id="tab-reservation" class="tab-content">
    <div class="info-card">
        <h3>Riwayat Reservasi</h3>
        @if($customer->reservations->count() > 0)
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Jumlah Pax</th>
                        <th>Preferensi Meja</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->reservations as $res)
                        <tr>
                            <td>{{ $res->reservation_date->format('d/m/Y') }} {{ $res->reservation_time }}</td>
                            <td><strong>{{ $res->pax }} Pax</strong></td>
                            <td>{{ $res->table_preference ?? 'Bebas' }}</td>
                            <td><span class="badge badge-success">{{ $res->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state" style="padding: 30px;">
                <p>Customer belum memiliki riwayat reservasi.</p>
            </div>
        @endif
    </div>
</div>

<!-- 5. LOYALTY POINT -->
<div id="tab-points" class="tab-content">
    <div class="grid-2">
        <div class="info-card">
            <h3>Riwayat Point</h3>
            @if($customer->pointHistories->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($customer->pointHistories as $ph)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <div>
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">{{ $ph->description }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $ph->created_at->format('d M Y, H:i') }}</div>
                            </div>
                            <div style="font-weight: 700; font-size: 16px; color: {{ $ph->points > 0 ? '#0C3527' : '#dc2626' }}; background: {{ $ph->points > 0 ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.1)' }}; padding: 6px 12px; border-radius: 8px;">
                                {{ $ph->points > 0 ? '+' : '' }}{{ $ph->points }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="padding: 20px;">
                    <p>Belum ada riwayat point.</p>
                </div>
            @endif
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="info-card">
                <h3>Tambah Point Manual</h3>
                <form action="{{ route('crm.customers.points.add', $customer->id) }}" method="POST">
                    @csrf
                    <input type="number" name="points" class="form-input-sm" placeholder="Jumlah Point (Misal: 100)" required min="1">
                    <input type="text" name="description" class="form-input-sm" placeholder="Keterangan (Misal: Bonus Ultah)" required>
                    <button type="submit" class="btn btn-outline" style="width: 100%; margin-top: 12px;"><i class="ph ph-plus"></i> Tambah Point</button>
                </form>
            </div>
            <div class="info-card">
                <h3 style="color: var(--crm-danger); border-color: rgba(220,38,38,0.1);">Redeem Point Manual</h3>
                <form action="{{ route('crm.customers.points.redeem', $customer->id) }}" method="POST">
                    @csrf
                    <input type="number" name="points" class="form-input-sm" placeholder="Jumlah Point (Misal: 50)" required min="1" max="{{ $customer->total_points }}">
                    <input type="text" name="description" class="form-input-sm" placeholder="Keterangan (Misal: Ditukar Minuman)" required>
                    <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 12px;"><i class="ph ph-minus"></i> Redeem Point</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 6. FEEDBACK -->
<div id="tab-feedback" class="tab-content">
    <div class="info-card">
        <h3>Ulasan & Feedback Customer</h3>
        @if($customer->feedbacks->count() > 0)
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Rating</th>
                        <th>Kategori</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->feedbacks as $fb)
                        <tr>
                            <td style="color: #f59e0b; font-weight: 700;">★ {{ $fb->rating }}/5</td>
                            <td>{{ $fb->category }}</td>
                            <td>{{ $fb->message }}</td>
                            <td><span class="badge badge-success">{{ $fb->status }}</span></td>
                            <td>{{ $fb->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state" style="padding: 30px;">
                <p>Belum ada ulasan atau feedback dari customer ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tabId, el) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    el.classList.add('active');
}
</script>
@endsection
