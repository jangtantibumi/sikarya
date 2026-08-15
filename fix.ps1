$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

$search = @"
                    @endif
                        <form method="post" action="{{ route('master-demo.po.store') }}">
"@

$replace = @"
                    @endif
                </div>
                @endforeach
                
                <div style="margin-top: 16px;">
                    <button class="ios-btn ios-btn-secondary" onclick="document.getElementById('modal-add-role').style.display='flex'"><i class="fa-solid fa-plus"></i> Tambah Role Baru</button>
                </div>
            </div>
            
            <div class="card" style="min-height: 500px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div>
                        <h3 style="margin: 0;">System Audit Log</h3>
                        <p class="desc" style="margin: 4px 0 0 0;">Rekam jejak aktivitas pengguna di dalam ekosistem perusahaan.</p>
                    </div>
                    <!-- Filters -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="font-size: 11px; padding: 6px 12px;" onclick="setAuditFilter('today')">Hari Ini</button>
                        <button type="button" class="ios-btn ios-btn-secondary" style="font-size: 11px; padding: 6px 12px;" onclick="setAuditFilter('week')">7 Hari</button>
                        <button type="button" class="ios-btn ios-btn-secondary" style="font-size: 11px; padding: 6px 12px;" onclick="setAuditFilter('month')">Bulan Ini</button>
                        <input type="date" id="audit-date" class="form-control" style="width: auto; padding: 6px 10px; font-size: 11px;" onchange="setAuditFilter('custom')">
                        <input type="time" id="audit-time-start" class="form-control" style="width: auto; padding: 6px 10px; font-size: 11px;" onchange="setAuditFilter('custom')">
                        <span style="color: var(--text-muted); font-size: 11px;">-</span>
                        <input type="time" id="audit-time-end" class="form-control" style="width: auto; padding: 6px 10px; font-size: 11px;" onchange="setAuditFilter('custom')">
                    </div>
                </div>

                <div id="audit-log-container" style="position: relative; padding-left: 20px;">
                    <div style="text-align: center; padding: 32px 0;">
                        <div class="loader"></div> Mengambil data log...
                    </div>
                </div>
            </div>
        </section>
        
        <!-- PURCHASING VIEW -->
        <section id="view-purchasing" class="view-section">
            <div class="grid-2">
                <div>
                    <h4 style="margin: 0 0 12px 0;">Permintaan Pembelian (PR) & Approval</h4>
                    <div class="card" style="margin-bottom: 16px; background: rgba(0,0,0,0.2);">
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">Daftar PR yang diajukan oleh karyawan/manajer menunggu ACC Anda.</p>
                        @php
                            `$pendingPRs = \App\Models\PurchaseRequest::where('company_id', `$company->id)->where('status', 'pending_ceo')->get();
                        @endphp
                        @forelse(`$pendingPRs as `$pr)
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px; margin-bottom: 8px;">
                                <div>
                                    <div style="font-size: 13px; font-weight: bold;">{{ `$pr->number }} - {{ `$pr->title }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">Oleh: {{ `$pr->requester?->name }} | Alasan: {{ `$pr->reason }}</div>
                                </div>
                                <form method="post" action="{{ route('master-demo.pr.approve', `$pr->id) }}">
                                    @csrf
                                    <button type="submit" class="user-pill" style="background: var(--success); color: white; border: none; cursor: pointer; font-size: 11px;"><i class="fa-solid fa-check"></i> ACC</button>
                                </form>
                            </div>
                        @empty
                            <div style="font-size: 12px; color: var(--text-muted);">Tidak ada PR yang menunggu persetujuan.</div>
                        @endforelse
                    </div>

                    <h4 style="margin: 0 0 12px 0;">Buat Purchase Order (PO) Langsung</h4>
                    <div class="card" style="background: rgba(0,0,0,0.2);">
                        <form method="post" action="{{ route('master-demo.po.store') }}">
"@

$content = $content.Replace($search, $replace)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Fixed"
