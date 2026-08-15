$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

# 1. Clean up duplicated HTML
$searchDupe = @"
                    <!-- Filters -->
                    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <div style="background: rgba(118, 118, 128, 0.12); padding: 2px; border-radius: 9px; display: inline-flex; font-weight: 600;">
                            <button type="button" id="btn-filter-today" class="audit-filter-btn" style="border: none; background: #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 7px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: #000; cursor: pointer; transition: all 0.2s;" onclick="setAuditFilter('today', this)">Hari Ini</button>
                            <button type="button" id="btn-filter-week" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: #000; cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('week', this)">7 Hari</button>
                        <div style="background: var(--panel-border); padding: 4px; border-radius: 9px; display: inline-flex; font-weight: 600;">
                            <button type="button" id="btn-filter-today" class="audit-filter-btn" style="border: none; background: var(--panel-bg); box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 7px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: var(--text-main); cursor: pointer; transition: all 0.2s;" onclick="setAuditFilter('today', this)">Hari Ini</button>
                            <button type="button" id="btn-filter-week" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('week', this)">7 Hari</button>
                            <button type="button" id="btn-filter-month" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('month', this)">Bulan Ini</button>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; background: var(--panel-bg); border: 1px solid var(--panel-border); padding: 4px; border-radius: 9px;">
"@

$replaceDupe = @"
                    <!-- Filters -->
                    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <div style="background: var(--panel-border); padding: 4px; border-radius: 9px; display: inline-flex; font-weight: 600;">
                            <button type="button" id="btn-filter-today" class="audit-filter-btn" style="border: none; background: var(--panel-bg); box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 7px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: var(--text-main); cursor: pointer; transition: all 0.2s;" onclick="setAuditFilter('today', this)">Hari Ini</button>
                            <button type="button" id="btn-filter-week" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('week', this)">7 Hari</button>
                            <button type="button" id="btn-filter-month" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('month', this)">Bulan Ini</button>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; background: var(--panel-bg); border: 1px solid var(--panel-border); padding: 4px; border-radius: 9px;">
"@

$content = $content.Replace($searchDupe, $replaceDupe)

# 2. Fix JS function body to properly apply text color
$searchJS = @"
    function setAuditFilter(rangeType, btnElement = null) {
        if (btnElement) {
            document.querySelectorAll('.audit-filter-btn').forEach(btn => {
                btn.style.background = 'transparent';
                btn.style.boxShadow = 'none';
                btn.style.fontWeight = 'normal';
            });
            btnElement.style.background = '#fff';
            btnElement.style.boxShadow = '0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04)';
            btnElement.style.fontWeight = '600';
            
            // Reset custom inputs
            document.getElementById('audit-date').value = '';
            document.getElementById('audit-time-start').value = '';
            document.getElementById('audit-time-end').value = '';
        } else if (rangeType === 'custom') {
            document.querySelectorAll('.audit-filter-btn').forEach(btn => {
                btn.style.background = 'transparent';
                btn.style.boxShadow = 'none';
                btn.style.fontWeight = 'normal';
            });
        }
"@

$replaceJS = @"
    function setAuditFilter(rangeType, btnElement = null) {
        if (btnElement) {
            document.querySelectorAll('.audit-filter-btn').forEach(btn => {
                btn.style.background = 'transparent';
                btn.style.boxShadow = 'none';
                btn.style.fontWeight = 'normal';
                btn.style.color = 'var(--text-muted)';
            });
            btnElement.style.background = 'var(--panel-bg)';
            btnElement.style.boxShadow = '0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04)';
            btnElement.style.fontWeight = '600';
            btnElement.style.color = 'var(--text-main)';
            
            // Reset custom inputs
            document.getElementById('audit-date').value = '';
            document.getElementById('audit-time-start').value = '';
            document.getElementById('audit-time-end').value = '';
        } else if (rangeType === 'custom') {
            document.querySelectorAll('.audit-filter-btn').forEach(btn => {
                btn.style.background = 'transparent';
                btn.style.boxShadow = 'none';
                btn.style.fontWeight = 'normal';
                btn.style.color = 'var(--text-muted)';
            });
        }
"@

$content = $content.Replace($searchJS, $replaceJS)

# 3. Add explicit DOMContentLoaded trigger that passes the button element
$searchLoad = @"
    document.addEventListener('DOMContentLoaded', () => {
        if(document.getElementById('audit-log-container')) {
            setTimeout(() => setAuditFilter('today'), 500);
        }
    });
"@
$replaceLoad = @"
    document.addEventListener('DOMContentLoaded', () => {
        if(document.getElementById('audit-log-container')) {
            setTimeout(() => setAuditFilter('today', document.getElementById('btn-filter-today')), 500);
        }
    });
"@
$content = $content.Replace($searchLoad, $replaceLoad)

Set-Content -Path $file -Value $content -NoNewline
Write-Host "Fixed UI completely"
