$file = 'D:\suba-erp-master-local-latest\resources\views\master-portal.blade.php'
$content = Get-Content $file -Raw

# We will use Regex to replace the Audit Log block because exact match is hard with previous messed up edits
$pattern = '(?s)<div class="card" style="min-height: 500px;">\s*<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">\s*<div>\s*<h3 style="margin: 0;">System Audit Log</h3>.*?</div>\s*</div>\s*</section>'

$replacement = @"
            <div class="card" style="min-height: 500px; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700;">System Audit Log</h3>
                        <p class="desc" style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 13px;">Rekam jejak seluruh aktivitas sistem secara real-time.</p>
                    </div>
                    
                    <!-- Advanced Filters & Search (iOS Style) -->
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        
                        <!-- Search Box -->
                        <div style="position: relative; width: 240px;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                            <input type="text" id="audit-search" placeholder="Cari user, modul, action..." 
                                style="width: 100%; padding: 10px 14px 10px 40px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); font-size: 13px; outline: none; transition: all 0.2s;"
                                onkeyup="if(event.key === 'Enter') loadAuditLogs()">
                        </div>

                        <!-- Segmented Control -->
                        <div style="background: rgba(120,120,128,0.12); padding: 3px; border-radius: 10px; display: inline-flex; font-weight: 500; font-size: 13px;">
                            <button type="button" class="audit-filter-btn active" data-range="today" onclick="setAuditFilter('today', this)" style="border: none; background: var(--panel-bg); box-shadow: 0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04); border-radius: 8px; padding: 6px 16px; color: var(--text-main); cursor: pointer; transition: all 0.2s;">Hari Ini</button>
                            <button type="button" class="audit-filter-btn" data-range="week" onclick="setAuditFilter('week', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 8px;">7 Hari</button>
                            <button type="button" class="audit-filter-btn" data-range="30_days" onclick="setAuditFilter('30_days', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 8px;">30 Hari</button>
                            <button type="button" class="audit-filter-btn" data-range="month" onclick="setAuditFilter('month', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; border-radius: 8px;">Bulan Ini</button>
                        </div>

                        <!-- Sort Dropdown -->
                        <select id="audit-sort" onchange="loadAuditLogs()" style="padding: 9px 14px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); font-size: 13px; cursor: pointer; outline: none;">
                            <option value="desc">Newest First</option>
                            <option value="asc">Oldest First</option>
                        </select>
                        
                    </div>
                </div>

                <!-- Custom Date Range -->
                <div id="audit-custom-range" style="display: none; justify-content: flex-end; gap: 8px; align-items: center; margin-bottom: 20px;">
                    <input type="date" id="audit-date" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                    <span style="color: var(--text-muted); font-size: 12px;">Pukul</span>
                    <input type="time" id="audit-time-start" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                    <span style="color: var(--text-muted); font-size: 12px;">-</span>
                    <input type="time" id="audit-time-end" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                </div>

                <!-- List Header -->
                <div style="display: grid; grid-template-columns: 200px 150px 150px 200px minmax(200px, 1fr) 120px; gap: 16px; padding: 12px 16px; border-bottom: 1px solid var(--panel-border); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    <div>Waktu</div>
                    <div>Aktor (User)</div>
                    <div>Modul/Target</div>
                    <div>Aktivitas</div>
                    <div>Detail</div>
                    <div>IP Address</div>
                </div>

                <!-- Timeline / Data Container -->
                <div id="audit-log-container" style="position: relative; min-height: 200px; padding-top: 8px;">
                    <div style="text-align: center; padding: 40px 0;">
                        <div class="loader"></div> <span style="margin-top: 12px; display: block; color: var(--text-muted); font-size: 14px;">Mengambil data log...</span>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="audit-pagination" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--panel-border);">
                    <div style="font-size: 13px; color: var(--text-muted);" id="audit-page-info">Menampilkan 0 data</div>
                    <div style="display: flex; gap: 8px;" id="audit-page-controls">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
            
            <script>
                let currentAuditRange = 'today';
                let currentAuditPage = 1;

                function setAuditFilter(range, btn) {
                    currentAuditRange = range;
                    document.querySelectorAll('.audit-filter-btn').forEach(el => {
                        el.style.background = 'transparent';
                        el.style.boxShadow = 'none';
                        el.style.color = 'var(--text-muted)';
                        el.classList.remove('active');
                    });
                    
                    if (btn) {
                        btn.style.background = 'var(--panel-bg)';
                        btn.style.boxShadow = '0 3px 8px rgba(0,0,0,0.12), 0 3px 1px rgba(0,0,0,0.04)';
                        btn.style.color = 'var(--text-main)';
                        btn.classList.add('active');
                    }
                    
                    if(range === 'custom') {
                        document.getElementById('audit-custom-range').style.display = 'flex';
                    } else {
                        document.getElementById('audit-custom-range').style.display = 'none';
                    }
                    
                    currentAuditPage = 1;
                    loadAuditLogs();
                }

                async function loadAuditLogs() {
                    const container = document.getElementById('audit-log-container');
                    container.innerHTML = '<div style="text-align: center; padding: 40px 0;"><div class="loader" style="margin: 0 auto;"></div><div style="margin-top: 12px; color: var(--text-muted); font-size: 14px;">Memuat data...</div></div>';
                    
                    const date = document.getElementById('audit-date').value;
                    const timeStart = document.getElementById('audit-time-start').value;
                    const timeEnd = document.getElementById('audit-time-end').value;
                    const keyword = document.getElementById('audit-search').value;
                    const sort = document.getElementById('audit-sort').value;
                    
                    let url = `/master-demo/security/audit-logs?range=` + currentAuditRange + `&page=` + currentAuditPage;
                    if(date) url += `&date=` + date;
                    if(timeStart) url += `&time_start=` + timeStart;
                    if(timeEnd) url += `&time_end=` + timeEnd;
                    if(keyword) url += `&keyword=` + encodeURIComponent(keyword);
                    if(sort) url += `&sort=` + sort;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        // Avoid global loader interception by using XMLHttpRequest header explicitly
                        const response = await fetch(url, {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        
                        renderAuditLogs(data.data);
                        renderPagination(data);
                    } catch(e) {
                        console.error('Audit Log Error:', e);
                        container.innerHTML = '<div style="text-align:center; padding:32px; color:var(--danger);"><i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:12px;"></i><br>Gagal memuat log audit.</div>';
                    }
                }

                function renderAuditLogs(logs) {
                    const container = document.getElementById('audit-log-container');
                    if(!logs || logs.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:16px; opacity:0.5;"></i><br>Tidak ada riwayat aktivitas pada rentang waktu ini.</div>';
                        return;
                    }

                    let html = '<div style="display: flex; flex-direction: column;">';
                    
                    logs.forEach(log => {
                        let icon = log.type === 'rbac' ? '<i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i>' : '<i class="fa-solid fa-server" style="color:var(--success);"></i>';
                        
                        let detailsStr = '';
                        if (log.details && typeof log.details === 'object') {
                            detailsStr = Object.entries(log.details).map(([k, v]) => `<span style="display:inline-block; margin-right:8px; margin-bottom:4px; padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;"><b>\${k}:</b> \${v}</span>`).join('');
                        } else if (log.details) {
                            detailsStr = `<span style="padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;">\${log.details}</span>`;
                        }

                        let avatar = log.actor_avatar ? `<img src="\${log.actor_avatar}" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">` : `<div style="width:24px; height:24px; border-radius:50%; background:var(--panel-border); display:flex; align-items:center; justify-content:center; font-size:10px;"><i class="fa-solid fa-user"></i></div>`;

                        html += `
                        <div style="display: grid; grid-template-columns: 200px 150px 150px 200px minmax(200px, 1fr) 120px; gap: 16px; padding: 16px; border-bottom: 1px solid var(--panel-border); font-size: 13px; align-items: start; transition: background 0.2s;" onmouseover="this.style.background='rgba(128,128,128,0.05)'" onmouseout="this.style.background='transparent'">
                            <div style="color: var(--text-muted);">\${log.created_at}</div>
                            <div style="display: flex; gap: 8px; align-items: center; font-weight: 500;">
                                \${avatar}
                                <span>\${log.actor}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                \${icon} \${log.target}
                            </div>
                            <div>
                                <span style="display: inline-block; padding: 4px 10px; background: var(--panel-border); border-radius: 20px; font-size: 11px; font-weight: 600;">\${log.action_label}</span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); line-height: 1.6;">\${detailsStr}</div>
                            <div style="font-family: monospace; font-size: 11px; color: var(--text-muted);">\${log.ip_address || '-'}</div>
                        </div>`;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                }

                function renderPagination(data) {
                    const info = document.getElementById('audit-page-info');
                    const controls = document.getElementById('audit-page-controls');
                    
                    if(data.total === 0) {
                        info.innerHTML = '';
                        controls.innerHTML = '';
                        return;
                    }
                    
                    info.innerHTML = `Menampilkan \${data.from || 0} - \${data.to || 0} dari total \${data.total} aktivitas`;
                    
                    let btns = '';
                    if (data.prev_page_url) {
                        btns += `<button onclick="changeAuditPage(\${data.current_page - 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
                    }
                    
                    btns += `<span style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: var(--text-main);">Halaman \${data.current_page} / \${data.last_page}</span>`;
                    
                    if (data.next_page_url) {
                        btns += `<button onclick="changeAuditPage(\${data.current_page + 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;">Next <i class="fa-solid fa-chevron-right"></i></button>`;
                    }
                    
                    controls.innerHTML = btns;
                }

                function changeAuditPage(page) {
                    currentAuditPage = page;
                    loadAuditLogs();
                }

                // Initial Load
                document.addEventListener('DOMContentLoaded', () => {
                    if(document.getElementById('audit-log-container')) {
                        loadAuditLogs();
                    }
                });
            </script>
        </section>
"@

$content = [regex]::Replace($content, $pattern, $replacement)
Set-Content -Path $file -Value $content -NoNewline
Write-Host "Frontend Fix Done"
