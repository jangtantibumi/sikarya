@extends('crm.layouts.app')
@section('title', 'Marketing CRM - Dashboard')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Marketing CRM</h1>
        <p>Kelola campaign, broadcast WhatsApp & Email, promo engine, birthday reminder, dan program referral.</p>
    </div>
</div>

<!-- STATS CARDS -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Total Campaign</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--crm-primary);">{{ number_format($campaignsCount) }}</div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Broadcast Terkirim</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-accent);">{{ number_format($broadcastsCount) }}</div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Promo Aktif</div>
        <div style="font-size: 28px; font-weight: 800; color: #d97706;">{{ number_format($promotionsCount) }}</div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Total Referral</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-accent);">{{ number_format($referralsCount) }}</div>
    </div>
</div>

<!-- MARKETING NAVIGATION CARDS -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
    <a href="{{ route('crm.marketing.campaigns') }}" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(12, 53, 39, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--crm-primary); margin-bottom: 16px;">
                <i class="ph ph-megaphone"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Campaign & Broadcast</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Buat dan kirim pesan masal WhatsApp & Email ke segmen/tag customer.</p>
        </div>
    </a>

    <a href="{{ route('crm.marketing.birthdays') }}" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(217, 119, 6, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #d97706; margin-bottom: 16px;">
                <i class="ph ph-cake"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Birthday Reminder</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Otomatisasi pengiriman ucapan & poin hadiah ulang tahun customer bulan ini.</p>
        </div>
    </a>

    <a href="{{ route('crm.marketing.promotions') }}" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(22, 163, 74, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--text-accent); margin-bottom: 16px;">
                <i class="ph ph-ticket"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Promotion & Coupon Engine</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Atur diskon promosi otomatis, batas belanja minimum, dan kupon khusus.</p>
        </div>
    </a>
</div>

<!-- RECENT CAMPAIGNS & UPCOMING BIRTHDAYS -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Campaign Terbaru</h3>
        @forelse($recentCampaigns as $c)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div>
                    <div style="font-weight: 600; color: #1e293b;">{{ $c->title }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Channel: {{ strtoupper($c->channel) }} • Target: {{ ucfirst($c->target_type) }}</div>
                </div>
                <span class="badge badge-success">{{ ucfirst($c->status) }}</span>
            </div>
        @empty
            <p style="color: #64748b; font-size: 13px;">Belum ada campaign.</p>
        @endforelse
    </div>

    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Ulang Tahun Bulan Ini</h3>
        @forelse($upcomingBirthdays as $b)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div>
                    <div style="font-weight: 600; color: #1e293b;">{{ $b->name }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ $b->birth_date ? $b->birth_date->format('d F') : '-' }} • {{ $b->phone }}</div>
                </div>
                <form action="{{ route('crm.marketing.birthdays.reward', $b->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Kirim Reward</button>
                </form>
            </div>
        @empty
            <p style="color: #64748b; font-size: 13px;">Tidak ada customer ulang tahun bulan ini.</p>
        @endforelse
    </div>
</div>
@endsection
