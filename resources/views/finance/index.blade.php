@php
    $summary = $summary ?? [
        'balance_sheet' => ['value' => 0],
        'profit_loss'   => ['value' => 0],
        'cash_flow'     => ['value' => 0],
        'equity'        => ['value' => 0],
    ];
    $journals = $journals ?? collect();
@endphp
<div class="finance-dashboard">
    <h2 class="section-title">Dashboard Keuangan</h2>
    <div class="grid-4 finance-summary" style="margin-bottom: 24px;">
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(170, 60%, 45%), hsl(200, 55%, 45%)); color: white;">
            <h3>Balance Sheet</h3>
            <p class="summary-value">{{ number_format($summary['balance_sheet']['value'] ?? 0, 2) }}</p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(45, 70%, 45%), hsl(30, 65%, 45%)); color: white;">
            <h3>Profit &amp; Loss</h3>
            <p class="summary-value">{{ number_format($summary['profit_loss']['value'] ?? 0, 2) }}</p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(340, 60%, 45%), hsl(310, 55%, 45%)); color: white;">
            <h3>Cash Flow</h3>
            <p class="summary-value">{{ number_format($summary['cash_flow']['value'] ?? 0, 2) }}</p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(120, 60%, 45%), hsl(100, 55%, 45%)); color: white;">
            <h3>Equity</h3>
            <p class="summary-value">{{ number_format($summary['equity']['value'] ?? 0, 2) }}</p>
        </div>
    </div>
    <h3 class="section-subtitle">Jurnal Terbaru</h3>
    <div class="card" style="overflow-x:auto;">
        <table class="finance-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background: var(--panel-bg);">
                    <th>Tanggal</th>
                    <th>Akun</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journals as $j)
                <tr>
                    <td>{{ $j->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $j->account->name ?? $j->system_key }}</td>
                    <td style="color: var(--success);">{{ number_format($j->debit, 2) }}</td>
                    <td style="color: var(--danger);">{{ number_format($j->credit, 2) }}</td>
                    <td>{{ $j->memo }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Tidak ada jurnal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
