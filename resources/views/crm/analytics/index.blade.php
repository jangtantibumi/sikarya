@extends('crm.layouts.app')
@section('title', 'Advanced CRM Analytics - CLV, RFM & Churn')

@section('styles')
<style>
    .analytics-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--crm-border); margin-bottom: 24px; }
    .analytics-tab-btn { padding: 12px 20px; font-size: 14px; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s ease; }
    .analytics-tab-btn:hover { color: var(--crm-primary); }
    .analytics-tab-btn.active { color: var(--crm-primary); border-bottom-color: var(--crm-primary); }
    .analytics-tab-content { display: none; }
    .analytics-tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .stat-card-enhanced { background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 20px; box-shadow: var(--crm-shadow); }
    .stat-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
    .stat-value { font-size: 26px; font-weight: 800; color: var(--crm-primary); }

    .rfm-matrix-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 16px; }
    .rfm-cell { background: #fff; border: 1px solid var(--crm-border); border-radius: 12px; padding: 16px; text-align: center; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Advanced CRM Analytics</h1>
        <p>Analisis statistik Customer Lifetime Value (CLV), segmentasi RFM, tingkat retensi, dan deteksi risiko churn.</p>
    </div>
</div>

<!-- STATS SUMMARY -->
<div class="grid-4">
    <div class="stat-card-enhanced">
        <div class="stat-title">Total Customer</div>
        <div class="stat-value">{{ number_format($total_customers) }}</div>
    </div>
    <div class="stat-card-enhanced">
        <div class="stat-title">Rata-rata CLV</div>
        <div class="stat-value" style="color: var(--text-accent);">Rp {{ number_format($clv['avg_clv'], 0, ',', '.') }}</div>
    </div>
    <div class="stat-card-enhanced">
        <div class="stat-title">Repeat Rate</div>
        <div class="stat-value" style="color: var(--text-accent);">{{ $repeat['repeat_rate'] }}%</div>
    </div>
    <div class="stat-card-enhanced">
        <div class="stat-title">Churn Rate</div>
        <div class="stat-value" style="color: #dc2626;">{{ $churn['churn_rate'] }}%</div>
    </div>
</div>

<!-- TABS NAVIGATION -->
<div class="analytics-tabs">
    <div class="analytics-tab-btn active" onclick="switchAnalyticsTab('clv', this)">Customer Lifetime Value (CLV)</div>
    <div class="analytics-tab-btn" onclick="switchAnalyticsTab('rfm', this)">RFM Analysis Matrix</div>
    <div class="analytics-tab-btn" onclick="switchAnalyticsTab('repeat', this)">Repeat Customer Rate</div>
    <div class="analytics-tab-btn" onclick="switchAnalyticsTab('churn', this)">Churn Analysis & Risks</div>
    <div class="analytics-tab-btn" onclick="switchAnalyticsTab('trends', this)">Spending Trend</div>
</div>

<!-- 1. CLV TAB -->
<div id="analytics-clv" class="analytics-tab-content active">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Komponen Kalkulasi CLV</h3>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="padding: 12px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">Average Order Value (AOV)</div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--crm-primary);">Rp {{ number_format($clv['avg_order_value'], 0, ',', '.') }}</div>
                </div>
                <div style="padding: 12px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">Purchase Frequency</div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--crm-primary);">{{ $clv['avg_frequency'] }} transaksi / customer</div>
                </div>
                <div style="padding: 12px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">Estimasi Customer Lifespan</div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--crm-primary);">{{ $clv['avg_lifespan_years'] }} Tahun</div>
                </div>
            </div>
        </div>

        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Top 10 Customer Teratas Berdasarkan CLV</h3>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Level</th>
                        <th>Total Spending</th>
                        <th>Estimasi CLV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clv['top_clv_customers'] as $c)
                        <tr>
                            <td><strong>{{ $c->name }}</strong> ({{ $c->customer_code }})</td>
                            <td><span class="badge badge-success">{{ $c->membership_level }}</span></td>
                            <td style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($c->total_spending, 0, ',', '.') }}</td>
                            <td style="color: var(--crm-primary); font-weight: 800;">Rp {{ number_format($c->estimated_clv, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada data customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. RFM TAB -->
<div id="analytics-rfm" class="analytics-tab-content">
    <div class="crm-card">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 12px;">Segmentasi Matriks RFM (Recency, Frequency, Monetary)</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Customer dikelompokkan secara otomatis berdasarkan keaktifan kunjungan terakhir (Recency), frekuensi order (Frequency), dan total pengeluaran (Monetary).</p>

        <div class="rfm-matrix-grid">
            @foreach($rfm['segments'] as $segName => $count)
                <div class="rfm-cell">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">{{ $segName }}</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--crm-primary); margin: 8px 0;">{{ $count }}</div>
                    <div style="font-size: 11px; color: #94a3b8;">Customer</div>
                </div>
            @endforeach
        </div>

        <h4 style="font-size: 14px; font-weight: 700; color: var(--crm-primary); margin: 24px 0 12px;">Daftar Customer Skor RFM</h4>
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Skor RFM</th>
                    <th>Segmen RFM</th>
                    <th>Spending</th>
                    <th>Last Visit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfm['classified_customers']->take(15) as $cust)
                    <tr>
                        <td><strong>{{ $cust->name }}</strong> ({{ $cust->customer_code }})</td>
                        <td><span style="font-family: monospace; font-weight: 700; background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 6px;">{{ $cust->rfm_score }}</span></td>
                        <td><span class="badge badge-success">{{ $cust->rfm_segment }}</span></td>
                        <td style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($cust->total_spending, 0, ',', '.') }}</td>
                        <td>{{ $cust->last_visit ? $cust->last_visit->format('d/m/Y') : 'Belum pernah' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 3. REPEAT CUSTOMER TAB -->
<div id="analytics-repeat" class="analytics-tab-content">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Rasio Repeat Customer</h3>
            <div style="text-align: center; padding: 32px 0;">
                <div style="font-size: 48px; font-weight: 800; color: var(--text-accent);">{{ $repeat['repeat_rate'] }}%</div>
                <div style="font-size: 14px; font-weight: 600; color: #475569; margin-top: 8px;">Tingkat Retensi Customer</div>
            </div>
            <div style="border-top: 1px solid var(--crm-border); padding-top: 16px; display: flex; justify-content: space-around;">
                <div>
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Repeat Customer</div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--text-accent);">{{ $repeat['repeat_customers'] }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Single Customer</div>
                    <div style="font-size: 18px; font-weight: 700; color: #d97706;">{{ $repeat['single_customers'] }}</div>
                </div>
            </div>
        </div>

        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Visualisasi Komposisi Customer</h3>
            <div style="display: flex; flex-direction: column; gap: 16px; justify-content: center; height: 200px;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        <span>Repeat Customers (>= 2 Visit/Order)</span>
                        <span>{{ $repeat['repeat_customers'] }} Customer ({{ $repeat['repeat_rate'] }}%)</span>
                    </div>
                    <div style="height: 12px; background: rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden;">
                        <div style="width: {{ $repeat['repeat_rate'] }}%; height: 100%; background: #0C3527;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        <span>First-Time Customers (1 Visit/Order)</span>
                        <span>{{ $repeat['single_customers'] }} Customer ({{ 100 - $repeat['repeat_rate'] }}%)</span>
                    </div>
                    <div style="height: 12px; background: rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden;">
                        <div style="width: {{ 100 - $repeat['repeat_rate'] }}%; height: 100%; background: #d97706;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. CHURN TAB -->
<div id="analytics-churn" class="analytics-tab-content">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Ringkasan Status Churn</h3>
            <div style="text-align: center; padding: 24px 0;">
                <div style="font-size: 44px; font-weight: 800; color: #dc2626;">{{ $churn['churn_rate'] }}%</div>
                <div style="font-size: 13px; font-weight: 600; color: #64748b; margin-top: 6px;">Persentase Customer Inaktif (>90 Hari)</div>
            </div>
            <div style="border-top: 1px solid var(--crm-border); padding-top: 16px; display: flex; justify-content: space-around;">
                <div>
                    <div style="font-size: 11px; color: #64748b;">Customer Aktif</div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--text-accent);">{{ $churn['active_count'] }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b;">Customer Churned</div>
                    <div style="font-size: 18px; font-weight: 700; color: #dc2626;">{{ $churn['churned_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="crm-card">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Daftar Customer Berisiko Churn (Inaktif 60-90 Hari)</h3>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Nomor HP</th>
                        <th>Total Spending</th>
                        <th>Kunjungan Terakhir</th>
                        <th style="text-align: right;">Aksi Re-engagement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($churn['risk_customers'] as $c)
                        <tr>
                            <td><strong>{{ $c->name }}</strong> ({{ $c->customer_code }})</td>
                            <td>{{ $c->phone }}</td>
                            <td style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($c->total_spending, 0, ',', '.') }}</td>
                            <td style="color: #dc2626; font-weight: 600;">{{ $c->last_visit ? $c->last_visit->format('d/m/Y') : 'Lama' }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('crm.marketing.campaigns') }}" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;"><i class="ph ph-paper-plane-right"></i> Kirim Win-back Offer</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 32px;">Tidak ada customer berisiko churn tinggi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 5. SPENDING TREND TAB -->
<div id="analytics-trends" class="analytics-tab-content">
    <div class="crm-card">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px;">Tren Pengeluaran Customer (12 Bulan Terakhir)</h3>
        
        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
            @foreach($trends as $tr)
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        <span>{{ $tr['label'] }}</span>
                        <span style="color: var(--text-accent); font-weight: 700;">Rp {{ number_format($tr['spending'], 0, ',', '.') }}</span>
                    </div>
                    <div style="height: 10px; background: rgba(0,0,0,0.05); border-radius: 5px; overflow: hidden;">
                        @php
                            $maxSpend = max(array_column($trends->toArray(), 'spending')) ?: 1;
                            $widthPct = min(100, max(5, ($tr['spending'] / $maxSpend) * 100));
                        @endphp
                        <div style="width: {{ $widthPct }}%; height: 100%; background: var(--crm-primary);"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchAnalyticsTab(tabId, el) {
    document.querySelectorAll('.analytics-tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.analytics-tab-btn').forEach(b => b.classList.remove('active'));

    document.getElementById('analytics-' + tabId).classList.add('active');
    el.classList.add('active');
}
</script>
@endsection
