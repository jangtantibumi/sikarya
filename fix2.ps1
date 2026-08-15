$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

$search = @"
    }
</script>
"@

$replace = @"
    }

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
                if (data.length === 0) {
                    container.innerHTML = '<div style="text-align: center; padding: 32px 0; color: var(--text-muted);">Tidak ada riwayat aktivitas pada rentang waktu ini.</div>';
                    return;
                }
                
                let html = '<div style="border-left: 2px solid var(--panel-border); margin-left: 12px; padding-bottom: 20px;">';
                data.forEach(log => {
                    const avatar = log.actor_avatar ? `<img src="` + log.actor_avatar + `" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">` : `<i class="fa-solid fa-user-circle" style="font-size:24px; color:var(--text-muted);"></i>`;
                    
                    html += `
                        <div style="position: relative; margin-bottom: 20px; padding-left: 24px;">
                            <div style="position: absolute; left: -14px; top: 0; background: var(--panel-bg); padding: 4px; border-radius: 50%;">
                                <div style="width: 16px; height: 16px; border-radius: 50%; background: ${log.type === 'rbac' ? 'var(--danger)' : 'var(--primary)'}; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                            </div>
                            <div style="display: flex; gap: 12px; align-items: flex-start; background: rgba(255,255,255,0.4); padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border);">
                                ${avatar}
                                <div style="flex: 1;">
                                    <div style="font-size: 13px;">
                                        <strong>${log.actor}</strong> 
                                        <span style="color: var(--text-muted);">melakukan</span> 
                                        <span style="font-weight: 600; color: ${log.type === 'rbac' ? 'var(--danger)' : 'var(--primary)'};">${log.action_label}</span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        Target: <strong>${log.target}</strong> | IP: ${log.ip_address}
                                    </div>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">
                                    ${log.created_at}
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div style="text-align: center; padding: 32px 0; color: var(--danger);">Gagal memuat log.</div>';
            });
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        if(document.getElementById('audit-log-container')) {
            setTimeout(() => setAuditFilter('today'), 500);
        }
    });
</script>
"@

$content = $content.Replace($search, $replace)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Fixed JS"
