$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

$searchHTML = @"
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
"@

$replaceHTML = @"
                    <!-- Filters -->
                    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <div style="background: rgba(118, 118, 128, 0.12); padding: 2px; border-radius: 9px; display: inline-flex; font-weight: 600;">
                            <button type="button" id="btn-filter-today" class="audit-filter-btn" style="border: none; background: #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 7px; padding: 6px 14px; font-size: 12px; font-weight: 600; color: #000; cursor: pointer; transition: all 0.2s;" onclick="setAuditFilter('today', this)">Hari Ini</button>
                            <button type="button" id="btn-filter-week" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: #000; cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('week', this)">7 Hari</button>
                            <button type="button" id="btn-filter-month" class="audit-filter-btn" style="border: none; background: transparent; padding: 6px 14px; font-size: 12px; color: #000; cursor: pointer; transition: all 0.2s; border-radius: 7px;" onclick="setAuditFilter('month', this)">Bulan Ini</button>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; background: #fff; border: 1px solid var(--panel-border); padding: 4px; border-radius: 9px;">
                            <input type="date" id="audit-date" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                            <div style="width: 1px; height: 16px; background: var(--panel-border);"></div>
                            <input type="time" id="audit-time-start" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                            <span style="color: var(--text-muted); font-size: 11px;">-</span>
                            <input type="time" id="audit-time-end" style="border: none; outline: none; background: transparent; font-size: 11px; padding: 4px; color: var(--text-main);" onchange="setAuditFilter('custom', null)">
                        </div>
                    </div>
"@

$searchJS = @"
    function setAuditFilter(rangeType) {
        document.querySelectorAll('#view-security .ios-btn-secondary').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
        });
        if (event && event.target && event.target.tagName === 'BUTTON') {
            event.target.style.background = 'var(--primary)';
            event.target.style.color = 'white';
        }
        
        let url = '/master-demo/security/audit-logs?range=' + rangeType;
        if (rangeType === 'custom') {
            const date = document.getElementById('audit-date').value;
            const tStart = document.getElementById('audit-time-start').value;
            const tEnd = document.getElementById('audit-time-end').value;
            if (date) url += '&date=' + date;
            if (tStart) url += '&time_start=' + tStart;
            if (tEnd) url += '&time_end=' + tEnd;
        }
        
        loadAuditLogs(url);
    }
    
    function loadAuditLogs(url = '/master-demo/security/audit-logs?range=today') {
        const container = document.getElementById('audit-log-container');
        if (!container) return;
        
        container.innerHTML = '<div style="text-align: center; padding: 32px 0;"><div class="loader"></div> Mengambil data log...</div>';
        
        // bypass global loader
        const fetchMethod = window.originalFetch || fetch;
        
        fetchMethod(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
"@

$replaceJS = @"
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
        
        let url = '/master-demo/security/audit-logs?range=' + rangeType;
        if (rangeType === 'custom') {
            const date = document.getElementById('audit-date').value;
            const tStart = document.getElementById('audit-time-start').value;
            const tEnd = document.getElementById('audit-time-end').value;
            if (date) url += '&date=' + date;
            if (tStart) url += '&time_start=' + tStart;
            if (tEnd) url += '&time_end=' + tEnd;
        }
        
        loadAuditLogs(url);
    }
    
    function loadAuditLogs(url = '/master-demo/security/audit-logs?range=today') {
        const container = document.getElementById('audit-log-container');
        if (!container) return;
        
        container.innerHTML = '<div style="text-align: center; padding: 32px 0;"><div class="loader"></div> Mengambil data log...</div>';
        
        // bypass global loader
        const fetchMethod = window.originalFetch || fetch;
        
        fetchMethod(url, { 
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(res => res.json())
            .then(data => {
"@

$content = $content.Replace($searchHTML, $replaceHTML)
$content = $content.Replace($searchJS, $replaceJS)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Fixed UI and JS"
