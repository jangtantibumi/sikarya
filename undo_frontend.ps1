$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

# Remove my new UI and JS block, and put back the old UI block exactly as it was.
$pattern = '(?s)<div class="card" style="min-height: 500px; padding: 24px;">.*?</script>\s*</section>'

$replacement = @"
            <div class="card" style="min-height: 500px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div>
                        <h3 style="margin: 0;">System Audit Log</h3>
                        <p class="desc" style="margin: 4px 0 0 0;">Rekam jejak aktivitas pengguna di dalam ekosistem perusahaan.</p>
                    </div>
                    <!-- Filters -->
                    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <div style="background: var(--panel-border); padding: 4px; border-radius: 9px; display: inline-flex; font-weight: 600;">
                            <button type="button" id="btn-filter-today" class="audit-filter-btn" style="border: none; background: var(--panel-bg); box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 7px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: var(--text-main); cursor: pointer; transition: all 0.2s;" onclick="setAuditFilter('today', this)">Hari Ini</button>
                            <button type="button" id="btn-filter-week" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('week', this)">7 Hari</button>
                            <button type="button" id="btn-filter-month" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('month', this)">Bulan Ini</button>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; background: var(--panel-bg); border: 1px solid var(--panel-border); padding: 4px; border-radius: 9px;">
                            <input type="date" id="audit-date" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                            <div style="width: 1px; height: 16px; background: var(--panel-border);"></div>
                            <input type="time" id="audit-time-start" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                            <span style="color: var(--text-muted); font-size: 11px;">-</span>
                            <input type="time" id="audit-time-end" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                        </div>
                    </div>
                </div>

                <div id="audit-log-container" style="position: relative; padding-left: 20px;">
                    <div style="text-align: center; padding: 32px 0;">
                        <div class="loader"></div> Mengambil data log...
                    </div>
                </div>
            </div>
        </section>
"@

$content = [regex]::Replace($content, $pattern, $replacement)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Reverted Frontend Logic"
