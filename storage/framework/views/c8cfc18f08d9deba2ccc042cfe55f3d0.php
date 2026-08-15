<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <title>Sistem Manajemen Laporan - Marketing</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#111113">
    <meta name="application-name" content="SubaArch ERP">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SubaArch ERP">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <script>
        window.ERP_FEATURES = <?php echo json_encode($featureFlags ?? [], 15, 512) ?>;
    </script>
    <script>
        (() => {
            try {
                const raw = localStorage.getItem('currentUserSession');
                const session = raw ? JSON.parse(raw) : null;
                const startedAt = Number(session?.loginTime || 0);
                const expiresAt = Number(session?.expiresAt || (startedAt + 7 * 24 * 60 * 60 * 1000));
                if (session?.user?.username && startedAt > 0 && Date.now() < expiresAt) {
                    document.documentElement.classList.add('session-restoring');
                }
            } catch (error) {
                localStorage.removeItem('currentUserSession');
            }
        })();
    </script>
    <link rel="stylesheet" href="/css/styles.css?v=<?php echo e(filemtime(public_path('css/styles.css'))); ?>">
    <link rel="stylesheet" href="/css/strategic-erp.css?v=<?php echo e(filemtime(public_path('css/strategic-erp.css'))); ?>">
    <!-- Modern Premium Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons for elegant UI -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google API Client & Identity Services -->
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="https://accounts.google.com/gsi/client"></script>

    <!-- Core Layout Grid System - 100% Accurate & Compatible Across All Browsers -->
    <style id="core-layout-grid">
        *, *::before, *::after {
            box-sizing: border-box !important;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100vw !important;
            height: 100% !important;
            overflow-x: hidden !important;
        }

        .app-container {
            display: grid !important;
            grid-template-columns: 260px minmax(0, 1fr) !important;
            align-items: start !important;
            width: 100% !important;
            min-height: 100vh !important;
            overflow-x: clip !important;
        }

        .sidebar {
            grid-column: 1 !important;
            grid-row: 1 !important;
            position: sticky !important;
            top: 0 !important;
            width: 260px !important;
            height: 100vh !important;
            min-width: 0 !important;
            background-color: var(--bg-sidebar) !important;
            border-right: 1px solid var(--border) !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 100 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        .main-content {
            grid-column: 2 !important;
            grid-row: 1 !important;
            width: auto !important;
            min-width: 0 !important;
            margin: 0 !important;
            overflow-x: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            background: var(--bg-main) !important;
            min-height: 100vh !important;
        }

        .view-section {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 20px 24px !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
        }

        /* Responsive Drawer — Mobile and Tablet (max-width: 768px) */
        @media (max-width: 768px) {
            .app-container {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .sidebar {
                position: fixed !important;
                left: -260px !important;
                top: 0 !important;
                height: 100dvh !important;
                z-index: 1000 !important;
                box-shadow: 10px 0 30px rgba(0,0,0,0.6) !important;
            }

            .sidebar.active {
                left: 0 !important;
            }

            .main-content {
                grid-column: 1 !important;
                grid-row: 1 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- Phase 12: Password-Protected Login Overlay -->
    <!-- Password-Protected / OTP Login Overlay -->
    <div id="login-overlay">
        <div class="login-card">
            <h1 style="color: var(--primary); margin-bottom: 8px;">Suba-Arch</h1>
            <h2>ERP System</h2>
            <p>Secure Enterprise OTP Login</p>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                <input type="text" id="login-username" placeholder="Username atau Email terdaftar" style="padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; border-radius: var(--radius-sm); font-size: 14px; outline: none; text-align: center;">
                <input type="text" id="login-otp" placeholder="6-Digit OTP" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" style="padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; border-radius: var(--radius-sm); font-size: 16px; outline: none; display: none; text-align: center; letter-spacing: 6px; font-weight: bold;" maxlength="6">
            </div>
            <p id="login-error" style="color: var(--danger); font-size: 12px; display: none; margin-top: -16px; margin-bottom: 16px;">Akun atau kode OTP tidak valid.</p>
            <button class="primary-btn" id="login-btn" style="justify-content: center; width: 100%;"><i class="ph ph-envelope-simple"></i> Kirim OTP</button>
            <p style="font-size: 11px; margin-top: 16px; color: var(--text-muted);">OTP akan dikirim ke email terdaftar.</p>
        </div>
    </div>

    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">
                <i class="ph-fill ph-buildings" style="color: var(--primary);"></i>
                <span style="letter-spacing: 1px; font-weight: 600;">SUBA ARCH</span>
            </div>
            
            <!-- RBAC Role Display -->
            <div class="role-switcher" style="padding: 16px; border-bottom: 1px solid var(--border);">
                <label style="font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; display: block;">Current Role</label>
                <div id="sidebar-current-role" style="width: 100%; background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; color: var(--primary);">
                    <i class="ph ph-shield-check"></i> <span id="sidebar-role-text">Loading...</span>
                </div>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-section" id="nav-section-company">PERUSAHAAN</div>
                <a href="#" class="nav-item" data-target="hierarchy" id="nav-company-hierarchy">
                    <i class="ph ph-tree-structure"></i>
                    <span>Struktur Organisasi</span>
                </a>

                <div class="nav-section" data-role="admin">EXECUTIVE</div>
                <a href="#" class="nav-item" data-target="ceo" data-role="ceo">
                    <i class="ph ph-globe-hemisphere-west"></i>
                    <span>CEO Global View</span>
                </a>
                
                <div class="nav-section" data-role="marketing">MARKETING</div>
                <a href="#" class="nav-item active" data-target="dashboard" data-role="marketing">
                    <i class="ph ph-squares-four"></i>
                    <span>KPI Overview</span>
                </a>
                <a href="#" class="nav-item" data-target="kanban" data-role="marketing">
                    <i class="ph ph-kanban"></i>
                    <span>Leads Kanban</span>
                </a>
                
                <div class="nav-section" data-role="ops">OPERASIONAL</div>
                <a href="#" class="nav-item" data-target="ops-dashboard" data-role="ops">
                    <i class="ph ph-hard-hat"></i>
                    <span>Project Tracker</span>
                </a>
                <a href="#" class="nav-item" data-target="ops-staff" data-role="ops">
                    <i class="ph ph-check-square-offset"></i>
                    <span>Stage-Gate Workspace</span>
                </a>

                <div class="nav-section" data-role="finance">FINANCE</div>
                <a href="#" class="nav-item" data-target="finance-dashboard" data-role="finance">
                    <i class="ph ph-chart-line-up"></i>
                    <span>Cashflow & Payroll</span>
                </a>
                <a href="#" class="nav-item" data-target="finance-staff" data-role="finance">
                    <i class="ph ph-receipt"></i>
                    <span>Expense & Tagihan</span>
                </a>

                <div class="nav-section" id="team-member-nav-section" style="display: none;">ANGGOTA TIM</div>
                <div id="dynamic-staff-member-navs"></div>

                <div class="nav-section" id="nav-section-collab">KOLABORASI</div>
                <a href="#" class="nav-item" data-target="kpi-tasks" id="nav-kpi-tasks">
                    <i class="ph ph-list-checks"></i>
                    <span>My Tasks & KPIs</span>
                </a>
                <a href="#" class="nav-item" data-target="resignation" id="nav-resignation">
                    <i class="ph ph-door-open"></i>
                    <span>Pengajuan Resign</span>
                </a>

                <div class="nav-section" data-role="hrd">HUMAN RESOURCES</div>
                <a href="#" class="nav-item" data-target="hrd" data-role="hrd">
                    <i class="ph ph-users-four"></i>
                    <span>HRD Workspace</span>
                </a>

                <div class="nav-section" data-role="admin">ADMINISTRATION</div>
                <a href="#" class="nav-item" data-target="attendance" data-role="admin">
                    <i class="ph ph-map-pin"></i>
                    <span>Live Attendance</span>
                </a>
                <a href="#" class="nav-item" data-target="approval" id="nav-approval-status">
                    <i class="ph ph-check-circle"></i>
                    <span>Persetujuan & Status</span>
                </a>
                <a href="#" class="nav-item" data-target="setup" data-role="admin">
                    <i class="ph ph-gear"></i>
                    <span>Set Up Goal Divisi</span>
                </a>

                <div class="nav-section" id="nav-section-strategic" data-roles="ceo,mgr_marketing,staff_marketing,mgr_ops,staff_ops,mgr_finance,staff_finance,mgr_hrd,staff_hrd">ERP TERINTEGRASI</div>
                <a href="#" class="nav-item" data-target="talent" data-roles="ceo,mgr_marketing,staff_marketing,mgr_ops,staff_ops,mgr_finance,staff_finance,mgr_hrd,staff_hrd">
                    <i class="ph ph-user-focus"></i>
                    <span>Talent Management</span>
                </a>
                <a href="#" class="nav-item" data-target="alumni" data-public-nav="true">
                    <i class="ph ph-graduation-cap"></i>
                    <span>Alumni Network</span>
                </a>
                <a href="#" class="nav-item" data-target="analytics" data-roles="ceo,mgr_marketing,mgr_ops,mgr_finance,mgr_hrd">
                    <i class="ph ph-chart-polar"></i>
                    <span>Advanced Analytics</span>
                </a>
                <a href="#" class="nav-item" data-target="documents" data-roles="ceo,mgr_marketing,staff_marketing,mgr_ops,staff_ops,mgr_finance,staff_finance,mgr_hrd,staff_hrd">
                    <i class="ph ph-certificate"></i>
                    <span>Dokumen & E-Sign</span>
                </a>
                <a href="#" class="nav-item" data-target="accounting" data-roles="ceo,mgr_finance,staff_finance">
                    <i class="ph ph-book-open-text"></i>
                    <span>Akuntansi</span>
                </a>
                <a href="#" class="nav-item" data-target="project-costing" data-roles="ceo,mgr_ops,staff_ops,mgr_finance,staff_finance">
                    <i class="ph ph-calculator"></i>
                    <span>Project Costing</span>
                </a>
                
                <a href="#" class="nav-item" id="sidebar-logout-nav" style="color: var(--danger); margin-top: 24px; border-top: 1px solid rgba(255, 59, 48, 0.15); border-radius: 0; padding-top: 16px;">
                    <i class="ph ph-sign-out"></i>
                    <span>Logout Session</span>
                </a>
            </nav>
            
            <div class="user-profile" style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 8px;">
                <div id="sidebar-profile-trigger" style="display: flex; align-items: center; gap: 12px; overflow: hidden; flex: 1; cursor: pointer;" title="Edit Profil Saya">
                    <div class="avatar" id="sidebar-user-avatar">CEO</div>
                    <div class="user-info" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">
                        <div class="name" id="sidebar-user-name" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Executive View</div>
                        <div class="role" id="sidebar-user-role" style="overflow: hidden; text-muted; font-size: 12px; text-overflow: ellipsis; white-space: nowrap;">Marketing Division</div>
                    </div>
                </div>
                <button id="logout-btn" style="background: rgba(255, 59, 48, 0.15); color: var(--danger); border: 1px solid rgba(255, 59, 48, 0.3); min-width: 32px; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Logout">
                    <i class="ph ph-sign-out" style="font-size: 16px;"></i>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div style="display: flex; align-items: center;">
                    <button class="icon-btn" id="mobile-hamburger-btn" style="display: none; margin-right: 16px; min-width: 40px; min-height: 40px;"><i class="ph ph-list"></i></button>
                    <div class="page-title">
                        <h1 id="current-page-title">CEO Global View</h1>
                        <p id="header-welcome-subtitle" class="subtitle">Welcome back, Super Admin.</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="backup-data-btn install-app-btn" id="install-app-btn" type="button" style="display: none;" title="Instal SubaArch ERP di perangkat ini">
                        <i class="ph ph-device-mobile-arrow-down"></i>
                        <span>Instal Aplikasi</span>
                    </button>
                    <button class="backup-data-btn" id="backup-data-btn" title="Salin atau unduh seluruh data yang dapat Anda akses">
                        <i class="ph ph-database"></i>
                        <span>Backup Data</span>
                    </button>
                    <!-- Universal Clock-in (Phase 7) -->
                    <div class="header-clock-in">
                        <span id="server-live-clock"><i class="ph ph-clock"></i> --:--:-- WIB</span>
                        <button id="univ-clock-in-btn">Clock In</button>
                    </div>

                    <div class="search-bar">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" placeholder="Search leads, KPI, team...">
                    </div>
                    
                    <!-- Notification Bell (Phase 9) -->
                    <div class="notification-menu">
                        <button class="icon-btn" id="notif-bell-btn" type="button" aria-label="Buka notifikasi" aria-haspopup="true" aria-expanded="false"><i class="ph ph-bell"></i><span class="badge-count" style="position:absolute; top:0; right:0; background:var(--danger); width:10px; height:10px; border-radius:50%;"></span></button>
                        <div class="notif-dropdown" id="notif-dropdown" role="dialog" aria-label="Daftar notifikasi">
                            <div class="notif-header">
                                <span>Pengingat & Notifikasi</span>
                                <a href="#" id="mark-all-read-btn" style="font-size: 11px; font-weight: 400; color: var(--primary);">Tandai semua dibaca</a>
                            </div>
                            <div id="notifications-list-container">
                                <!-- Dynamically populated via JS -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat Toggle Button -->
                    <button class="icon-btn chat-trigger" id="chat-toggle-btn">
                        <i class="ph ph-chats-circle"></i>
                        <span class="badge-count">2</span>
                    </button>
                </div>
            </header>

            <!-- CEO Global View -->
            <section id="view-ceo" class="view-section active" style="display: block;">
                <!-- Company Health Banner -->
                <div class="ceo-health-banner">
                    <div class="health-text">
                        <h3>Suba-Arch Health Index</h3>
                        <p>Aggregate performance across Marketing, Operations, & Finance.</p>
                        <div style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px; background: rgba(52, 199, 89, 0.15); color: var(--success); padding: 8px 16px; border-radius: 20px; font-weight: 600;">
                            <i class="ph ph-trend-up"></i> +12% from last month
                        </div>
                    </div>
                    <div class="health-score">
                        <span class="health-number">92</span>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 24px; color: var(--text-secondary);">/ 100</span>
                            <span class="badge success" style="margin-top: 4px; text-align: center;">Grade A</span>
                        </div>
                    </div>
                </div>

                <!-- Division Summaries -->
                <h3 style="margin-bottom: 16px; font-weight: 500;">Division Performance</h3>
                <div class="division-grid">
                    <!-- Marketing -->
                    <div class="division-card" style="border-top: 3px solid var(--primary);">
                        <div class="div-header">
                            <h4>ðŸ“ˆ Marketing</h4>
                            <span class="div-status" style="color: var(--success);"><i class="ph-fill ph-circle"></i> On Track</span>
                        </div>
                        <div style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">Rp 500M</div>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;">Pipeline Omzet Target Achieved</p>
                        <div class="progress-container"><div class="progress-bar" style="width: 85%; background: var(--primary);"></div></div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 8px; color: var(--text-muted);">
                            <span>85% (Target 600M)</span>
                            <a href="#" style="color: var(--primary); text-decoration: none;" onclick="document.querySelector('[data-target=\'dashboard\']').click()">View Details</a>
                        </div>
                    </div>
                    <!-- Operasional -->
                    <div class="division-card" style="border-top: 3px solid var(--warning);">
                        <div class="div-header">
                            <h4>ðŸ—ï¸ Operasional</h4>
                            <span class="div-status" style="color: var(--warning);"><i class="ph-fill ph-circle"></i> Needs Attention</span>
                        </div>
                        <div style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">12 Active</div>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;">Construction & Design Projects</p>
                        <div class="progress-container"><div class="progress-bar" style="width: 60%; background: var(--warning);"></div></div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 8px; color: var(--text-muted);">
                            <span>2 Overdue</span>
                            <a href="#" style="color: var(--primary); text-decoration: none;">View Details</a>
                        </div>
                    </div>
                    <!-- Finance -->
                    <div class="division-card" style="border-top: 3px solid var(--success);">
                        <div class="div-header">
                            <h4>ðŸ’° Finance</h4>
                            <span class="div-status" style="color: var(--success);"><i class="ph-fill ph-circle"></i> Healthy</span>
                        </div>
                        <div style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">Rp 1.2B</div>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;">Net Cashflow (MTD)</p>
                        <div class="progress-container"><div class="progress-bar" style="width: 95%; background: var(--success);"></div></div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 8px; color: var(--text-muted);">
                            <span>Burn Rate: Normal</span>
                            <a href="#" style="color: var(--primary); text-decoration: none;">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- CEO Employee Performance Chart & Feedback Panel -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 32px; margin-bottom: 32px;">
                    <!-- Chart -->
                    <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                        <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 500; font-size: 16px; color: white;"><i class="ph ph-chart-bar"></i> Grafik Performa KPI Karyawan</h3>
                        <div style="height: 250px; position: relative;">
                            <canvas id="ceoEmployeePerformanceChart"></canvas>
                        </div>
                    </div>
                    <!-- CEO Feedback Panel -->
                    <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column;">
                        <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 500; font-size: 16px; color: white;"><i class="ph ph-chat-centered-text"></i> Kirim Umpan Balik / Komentar ke Manajer</h3>
                        <div style="display: flex; flex-direction: column; gap: 12px; flex: 1;">
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 12px; color: var(--text-secondary);">Pilih Manajer Divisi</label>
                                <select id="ceo-comment-target" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                                    <option value="mgr_marketing">Maulana Mkt (Manager Marketing)</option>
                                    <option value="mgr_ops">Reza Ops (Manager Operasional)</option>
                                    <option value="mgr_finance">Hendra Fin (Manager Finance)</option>
                                </select>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 6px; flex: 1;">
                                <label style="font-size: 12px; color: var(--text-secondary);">Komentar / Pesan Pengarahan</label>
                                <textarea id="ceo-comment-text" placeholder="Tulis instruksi atau catatan performa divisi di sini..." style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; resize: none; flex: 1; font-family: inherit; min-height: 80px;" required></textarea>
                            </div>
                            <button id="btn-submit-ceo-comment" class="primary-btn" style="width: 100%; justify-content: center; background: var(--primary); font-family: inherit;"><i class="ph ph-paper-plane-tilt"></i> Kirim Catatan CEO</button>
                        </div>
                    </div>
                </div>
                
                <!-- CEO Comments History Feed -->
                <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 32px;">
                    <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 500; font-size: 16px; color: white;"><i class="ph ph-chat-circle-dots"></i> Riwayat Pengarahan CEO</h3>
                    <div id="ceo-comments-feed" style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- CEO Team Addition/Deletion Requests -->
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 32px;" id="ceo-team-requests-card">
                    <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 500; font-size: 16px; color: var(--primary);"><i class="ph ph-users-three"></i> Persetujuan Pengajuan Tim (Manager)</h3>
                    <div id="ceo-team-requests-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto;">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- CEO Employee Leave Overview -->
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 32px;">
                    <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 500; font-size: 16px; color: white;"><i class="ph ph-calendar-blank"></i> Overview Cuti &amp; Absensi Karyawan</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 13px; color: var(--warning);"><i class="ph ph-clock"></i> Pengajuan Cuti Butuh Persetujuan (CEO)</h4>
                            <div id="ceo-pending-leaves-list" style="max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 10px 0; font-size: 13px; color: var(--success);"><i class="ph ph-user-check"></i> Karyawan Sedang Cuti</h4>
                            <div id="ceo-active-leaves-list" style="max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending KPI Proposals -->
                <h3 style="margin: 32px 0 16px; font-weight: 500;">Pending KPI Proposals (CEO Veto)</h3>
                <div class="proposal-list" style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="approval-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div class="approval-info" style="display: flex; gap: 16px; align-items: center;">
                            <div class="member-avatar" style="width: 48px; height: 48px; font-size: 16px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--warning), var(--primary));">Mkt</div>
                            <div>
                                <h4 style="font-size: 16px; margin-bottom: 4px;">Proposed KPI: Tiktok Live Conversion</h4>
                                <p style="color: var(--text-secondary); font-size: 13px;">Proposed by Manager Marketing &bull; Target: 20 Deals &bull; Bobot: 15%</p>
                                <div style="font-size: 12px; color: var(--warning); margin-top: 8px; background: rgba(255, 159, 10, 0.1); padding: 8px; border-radius: 4px;">
                                    <strong>Justification:</strong> "Tiktok Live sedang tren, saya ingin memindahkan bobot 15% dari Leads IG ke sini karena konversinya lebih tinggi."
                                </div>
                            </div>
                        </div>
                        <div class="approval-actions" style="display: flex; gap: 12px; align-items: center;">
                            <button class="icon-btn" style="color: var(--danger); border-color: rgba(255, 59, 48, 0.3);"><i class="ph ph-x"></i></button>
                            <button class="primary-btn" style="background: var(--success); box-shadow: 0 4px 15px rgba(52, 199, 89, 0.3);"><i class="ph ph-check"></i> Approve</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Org Hierarchy View -->
            <section id="view-hierarchy" class="view-section" style="display: none;">
                <div class="organization-heading">
                    <div>
                        <span class="organization-eyebrow">Direktori Perusahaan</span>
                        <h2>Struktur Organisasi Suba Arch</h2>
                        <p>Kenali jalur koordinasi, divisi, dan atasan langsung tanpa membuka data pribadi atau kinerja yang bukan kewenangan Anda.</p>
                    </div>
                    <div id="ceo-hierarchy-actions" style="display: none; gap: 12px; align-items: center;">
                        <button id="btn-hierarchy-add-div" class="primary-btn organization-admin-btn"><i class="ph ph-shield-star"></i> Tambah Divisi</button>
                        <button id="btn-hierarchy-add-mgr" class="primary-btn organization-admin-btn"><i class="ph ph-crown"></i> Tetapkan Manager</button>
                        <button id="btn-hierarchy-add-staff" class="primary-btn"><i class="ph ph-user-plus"></i> Tambah Staff</button>
                    </div>
                </div>

                <div class="organization-toolbar">
                    <label class="organization-search" for="organization-search-input">
                        <i class="ph ph-magnifying-glass"></i>
                        <input id="organization-search-input" type="search" placeholder="Cari nama atau jabatan..." autocomplete="off">
                    </label>
                    <label class="organization-filter" for="organization-division-filter">
                        <span>Divisi</span>
                        <select id="organization-division-filter">
                            <option value="all">Semua divisi</option>
                            <option value="marketing">Marketing</option>
                            <option value="operasional">Operasional</option>
                            <option value="finance">Finance</option>
                            <option value="hrd">HRD</option>
                        </select>
                    </label>
                    <div id="organization-summary" class="organization-summary" aria-live="polite">Memuat struktur aktif...</div>
                </div>

                <div id="organization-privacy-notice" class="organization-privacy-notice">
                    <i class="ph ph-shield-check"></i>
                    <span>Informasi yang ditampilkan terbatas pada nama, jabatan, divisi, status kerja, dan jalur pelaporan.</span>
                </div>
                
                <div class="hierarchy-container">
                    <div class="org-tree-wrapper" style="position: relative; width: 100%; height: 100%;">
                        <svg id="org-chart-svg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;"></svg>
                        <div class="org-chart" id="org-chart-render" style="position: relative; z-index: 2;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI Overview Section (Marketing) -->
            <section id="view-dashboard" class="view-section" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2 style="font-size: 20px; font-weight: 500;">Marketing KPI Dashboard</h2>
                    <div class="export-btn-group">
                        <button class="export-btn sheets"><i class="ph ph-file-csv"></i> Download Spreadsheet</button>
                    </div>
                </div>
                <div class="kpi-grid">
                    <!-- KPI 1 -->
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="icon-box primary"><i class="ph ph-money"></i></div>
                            <span class="badge warning">Needs Attention</span>
                        </div>
                        <h3>Omzet from Consultant</h3>
                        <div class="kpi-value">Rp 10.8M <span class="target">/ Rp 50M</span></div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 21.6%; background: var(--warning)"></div>
                        </div>
                        <div class="kpi-footer">
                            <span>Achievement: 21.6%</span>
                            <span class="weight">Bobot: 30%</span>
                        </div>
                    </div>

                    <!-- KPI 2 -->
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="icon-box success"><i class="ph ph-users"></i></div>
                            <span class="badge warning">Needs Attention</span>
                        </div>
                        <h3>Conversion Leads to Sales</h3>
                        <div class="kpi-value">1 <span class="target">/ 5 Clients</span></div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 20%; background: var(--warning)"></div>
                        </div>
                        <div class="kpi-footer">
                            <span>Achievement: 20%</span>
                            <span class="weight">Bobot: 20%</span>
                        </div>
                    </div>

                    <!-- KPI 3 -->
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="icon-box danger"><i class="ph ph-chart-line-down"></i></div>
                            <span class="badge danger">Critical</span>
                        </div>
                        <h3>Fully Loaded CAC</h3>
                        <div class="kpi-value">48% <span class="target">/ < 30%</span></div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%; background: var(--danger)"></div>
                        </div>
                        <div class="kpi-footer">
                            <span>Achievement: 62.3% (Lower is better)</span>
                            <span class="weight">Bobot: 20%</span>
                        </div>
                    </div>

                    <!-- KPI 4 -->
                    <div class="kpi-card">
                        <div class="kpi-header">
                            <div class="icon-box info"><i class="ph ph-funnel"></i></div>
                            <span class="badge success">On Track</span>
                        </div>
                        <h3>Lead Gen Volume</h3>
                        <div class="kpi-value">115 <span class="target">/ 70 Leads</span></div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 100%; background: var(--success)"></div>
                        </div>
                        <div class="kpi-footer">
                            <span>Achievement: 164%</span>
                            <span class="weight">Bobot: 20%</span>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card wide">
                        <div class="card-header">
                            <h3>Pipeline Omzet Projection vs Target</h3>
                            <select class="glass-select"><option>This Month</option></select>
                        </div>
                        <div class="chart-container">
                            <canvas id="omzetChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="card-header">
                            <h3>Team Performance Index</h3>
                        </div>
                        <div class="score-display">
                            <div class="big-score">
                                96%
                                <span class="score-target">Target 85%</span>
                            </div>
                            <div class="predikat-badge grade-d">
                                Grade D (Overall)
                            </div>
                        </div>
                        <p class="score-desc">Overall Marketing KPIM Score is currently <b>52.94%</b> due to low Omzet and Conversion achievements.</p>
                    </div>
                </div>
            </section>

            <!-- Kanban View (Hidden by default) -->
            <section id="view-kanban" class="view-section" style="display: none;">
                <div class="crm-page-head">
                    <div>
                        <span class="crm-eyebrow">CRM & Revenue Intelligence</span>
                        <h2>Pipeline Leads</h2>
                        <p>Pantau prospek WhatsApp hingga menjadi pembayaran dan omzet aktual.</p>
                    </div>
                    <div class="crm-head-actions">
                        <span id="crm-whatsapp-status" class="crm-integration-badge pending" title="Status koneksi WhatsApp Cloud API">
                            <i class="ph ph-circle-notch"></i> Memeriksa WhatsApp API
                        </span>
                        <button id="kanban-new-lead-btn" class="primary-btn" style="display: none;"><i class="ph ph-plus"></i> Tambah Lead</button>
                    </div>
                </div>
                <div id="crm-summary" class="crm-summary-grid" aria-live="polite">
                    <div class="crm-summary-card"><span>Lead Aktif</span><strong>0</strong><small>Menunggu data CRM</small></div>
                    <div class="crm-summary-card"><span>Pipeline</span><strong>Rp 0</strong><small>Nilai potensi terbuka</small></div>
                    <div class="crm-summary-card"><span>Omzet Aktual</span><strong>Rp 0</strong><small>Pembayaran terhubung Finance</small></div>
                    <div class="crm-summary-card"><span>Konversi</span><strong>0%</strong><small>Deal dari lead yang ditutup</small></div>
                </div>
                <div class="crm-toolbar">
                    <div class="filters">
                        <button class="filter-btn active" data-filter="all">Semua</button>
                        <button class="filter-btn" data-filter="WhatsApp">WhatsApp</button>
                        <button class="filter-btn" data-filter="Desain">Desain</button>
                        <button class="filter-btn" data-filter="Pembangunan">Pembangunan</button>
                    </div>
                    <label class="crm-search">
                        <i class="ph ph-magnifying-glass"></i>
                        <input id="crm-lead-search" type="search" placeholder="Cari nama, nomor, kampanye..." autocomplete="off">
                    </label>
                </div>
                <div class="kanban-board">
                    <!-- Kolom 1: Leads Masuk -->
                    <div class="kanban-column" id="col-leads">
                        <div class="column-header">
                            <h3>Leads Masuk</h3>
                            <span class="count">3</span>
                        </div>
                        <div class="column-body">
                            <div class="kanban-card" draggable="true">
                                <div class="card-tags"><span class="tag source-ig">IG DM</span><span class="tag type-build">Pembangunan</span></div>
                                <h4>Bpk. Budi - Villa Puncak</h4>
                                <p class="budget">Est: Rp 1.5M</p>
                                <div class="card-footer">
                                    <span class="date">Hari ini</span>
                                </div>
                            </div>
                            <div class="kanban-card" draggable="true">
                                <div class="card-tags"><span class="tag source-wa">WhatsApp</span><span class="tag type-design">Desain</span></div>
                                <h4>Ibu Rina - Renovasi</h4>
                                <p class="budget">Est: Rp 200Jt</p>
                                <div class="card-footer">
                                    <span class="date">Kemarin</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Penawaran -->
                    <div class="kanban-column" id="col-penawaran">
                        <div class="column-header">
                            <h3>Penawaran</h3>
                            <span class="count">1</span>
                        </div>
                        <div class="column-body">
                            <div class="kanban-card" draggable="true">
                                <div class="card-tags"><span class="tag source-web">Website</span><span class="tag type-build">Pembangunan</span></div>
                                <h4>PT. Sejahtera - Ruko</h4>
                                <p class="budget">Est: Rp 3M</p>
                                <div class="card-footer">
                                    <span class="date">2 hari lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 3: Deal -->
                    <div class="kanban-column" id="col-deal">
                        <div class="column-header">
                            <h3>Deal (Survey/Desain)</h3>
                            <span class="count">1</span>
                        </div>
                        <div class="column-body">
                            <div class="kanban-card success" draggable="true">
                                <div class="card-tags"><span class="tag source-ref">Referensi</span><span class="tag type-design">Desain</span></div>
                                <h4>Keluarga Bapak Andi</h4>
                                <p class="budget deal-value">Deal: Rp 10.8M</p>
                                <div class="card-footer">
                                    <span class="date">Jan 15, 2026</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kolom 4: Lost -->
                    <div class="kanban-column" id="col-lost">
                        <div class="column-header">
                            <h3>Lost</h3>
                            <span class="count">0</span>
                        </div>
                        <div class="column-body">
                            <!-- Empty state -->
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Member / Personal Workspace View -->
            <section id="view-member-dev" class="view-section" style="display: none;">
                <!-- Attendance Clock-in Widget -->
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(52, 199, 89, 0.1); border: 2px solid var(--success); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--success);">
                            <i class="ph-fill ph-check-circle"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; margin-bottom: 4px;">Status: Clocked In</h3>
                            <p style="color: var(--text-secondary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
                                <i class="ph ph-clock"></i> 08:15 AM (Server Time) &bull; <i class="ph ph-map-pin"></i> 102.13, -6.12 (Suba-Arch HQ)
                            </p>
                        </div>
                    </div>
                    <div>
                        <button id="workspace-clock-btn" class="primary-btn" style="background: rgba(239, 68, 68, 0.15); color: var(--danger); box-shadow: none; border: 1px solid rgba(239, 68, 68, 0.3);"><i class="ph ph-sign-out"></i> Clock Out</button>
                    </div>
                </div>

                <div class="member-profile-header">
                    <div class="member-avatar">MZ</div>
                    <div class="member-info">
                        <h2>M. Maulana Zakaria</h2>
                        <p>Web Developer | KPIM Score: 100% (Grade A)</p>
                    </div>
                </div>
                
                <div class="workspace-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <!-- Daily Checklist & Reporting Form -->
                    <div class="setup-card kpi-sheet-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 32px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h2>Daily Workspace & Reports</h2>
                            <span class="badge warning" style="display: flex; align-items: center; gap: 4px;"><i class="ph ph-key"></i> Manager Bypass Active</span>
                        </div>
                        
                        <!-- Report Item 1 -->
                        <div class="report-item" style="border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px; background: rgba(0,0,0,0.2);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 style="font-size: 15px; display: flex; align-items: center; gap: 12px;">
                                    <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--primary); cursor: pointer;">
                                    High-Ticket Leads
                                </h3>
                                <!-- Manager Status Override -->
                                <select class="glass-select" style="background: rgba(52, 199, 89, 0.15); color: var(--success); border-color: rgba(52, 199, 89, 0.3); font-weight: 600;">
                                    <option>Status: Pending</option>
                                    <option selected>Status: Done</option>
                                    <option>Status: Revisi</option>
                                </select>
                            </div>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 12px; color: var(--text-secondary);">Evidence Link / Reporting Notes</label>
                                <div style="display: flex; gap: 12px;">
                                    <input type="text" value="https://prnt.sc/T64Kq_Ex-yO_" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; flex: 1; outline: none;">
                                    <button class="primary-btn" style="padding: 10px 20px;"><i class="ph ph-floppy-disk"></i> Save</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Report Item 2 -->
                        <div class="report-item" style="border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px; background: rgba(0,0,0,0.2);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h3 style="font-size: 15px; display: flex; align-items: center; gap: 12px;">
                                    <input type="checkbox" style="width: 20px; height: 20px; accent-color: var(--primary); cursor: pointer;">
                                    Publish & Optimasi Artikel
                                </h3>
                                <select class="glass-select" style="background: rgba(255, 159, 10, 0.15); color: var(--warning); border-color: rgba(255, 159, 10, 0.3); font-weight: 600;">
                                    <option>Status: Pending</option>
                                    <option>Status: Done</option>
                                    <option selected>Status: Revisi</option>
                                </select>
                            </div>
                            <!-- Manager Feedback -->
                            <div style="font-size: 13px; color: var(--warning); margin-bottom: 16px; background: rgba(255, 159, 10, 0.1); padding: 12px; border-radius: var(--radius-sm); border-left: 3px solid var(--warning);">
                                <strong>Manager Note:</strong> Konten artikel kurang relevan dengan niche arsitektur, tolong perbaiki bagian pendahuluannya.
                            </div>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 12px; color: var(--text-secondary);">Evidence Link / Reporting Notes</label>
                                <div style="display: flex; gap: 12px;">
                                    <input type="text" placeholder="Masukkan URL artikel / Google Docs..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; flex: 1; outline: none;">
                                    <button class="primary-btn" style="padding: 10px 20px;"><i class="ph ph-paper-plane-right"></i> Resubmit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- KPI Summary Pane -->
                    <div class="member-kpi-list" style="height: fit-content;">
                        <div class="list-header" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                            <div class="col-indicator">Indicator Summary</div>
                            <div class="col-achieve" style="text-align: right;">Progress</div>
                        </div>
                        <div class="list-row" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                            <div class="col-indicator">High-Ticket Leads</div>
                            <div class="col-achieve" style="text-align: right;"><span class="badge success">110%</span></div>
                        </div>
                        <div class="list-row" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                            <div class="col-indicator">Optimasi Artikel</div>
                            <div class="col-achieve" style="text-align: right;"><span class="badge warning">Revisi</span></div>
                        </div>
                        <div class="list-row" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                            <div class="col-indicator">Migrasi Post/URL</div>
                            <div class="col-achieve" style="text-align: right;"><span class="badge success">100%</span></div>
                        </div>
                    </div>
                </div>
            </section>
            
             <!-- Member View Content Creator -->
            <section id="view-member-creator" class="view-section" style="display: none;">
                <div class="member-profile-header">
                    <div class="member-avatar">DB</div>
                    <div class="member-info">
                        <h2>D BEST AR</h2>
                        <p>Content Creator | KPIM Score: 87% (Grade B)</p>
                    </div>
                </div>
                
                <div class="member-kpi-list">
                    <div class="list-header">
                        <div class="col-indicator">Indicator</div>
                        <div class="col-target">Target</div>
                        <div class="col-actual">Actual</div>
                        <div class="col-achieve">Achievement</div>
                    </div>
                    <div class="list-row">
                        <div class="col-indicator">Video Output (60-90s)</div>
                        <div class="col-target">45</div>
                        <div class="col-actual">65</div>
                        <div class="col-achieve"><span class="badge success">144%</span></div>
                    </div>
                    <div class="list-row">
                        <div class="col-indicator">Audience Retention</div>
                        <div class="col-target">25%</div>
                        <div class="col-actual">10%</div>
                        <div class="col-achieve"><span class="badge danger">40%</span></div>
                    </div>
                    <div class="list-row">
                        <div class="col-indicator">Save Ratio</div>
                        <div class="col-target">135</div>
                        <div class="col-actual">151</div>
                        <div class="col-achieve"><span class="badge success">112%</span></div>
                    </div>
                    <div class="list-row">
                        <div class="col-indicator">Organic Leads (DM/WA)</div>
                    <div class="col-indicator">Organic Leads (DM/WA)</div>
                        <div class="col-target">5</div>
                        <div class="col-actual">5</div>
                        <div class="col-achieve"><span class="badge success">100%</span></div>
                    </div>
                </div>
            </section>
            
            <!-- Approval Evidence View / Inbox -->
            <section id="view-resignation" class="view-section" style="display: none;">
                <div class="section-header resignation-heading">
                    <div>
                        <h2><i class="ph ph-door-open"></i> Pengajuan Resign</h2>
                        <p>Pengajuan diproses berjenjang oleh atasan langsung dan CEO, dengan tembusan otomatis kepada HRD.</p>
                    </div>
                </div>
                <div class="resignation-layout">
                    <form id="resignation-form" class="setup-card resignation-form-card">
                        <div class="resignation-card-icon"><i class="ph ph-file-text"></i></div>
                        <h3>Form Pengajuan</h3>
                        <p>Isi data secara lengkap agar proses serah terima pekerjaan dapat dipersiapkan dengan baik.</p>
                        <label for="resignation-last-date">Hari kerja terakhir</label>
                        <input id="resignation-last-date" type="date" required>
                        <label for="resignation-reason">Alasan pengajuan</label>
                        <textarea id="resignation-reason" rows="4" maxlength="3000" placeholder="Sampaikan alasan secara profesional..." required></textarea>
                        <label for="resignation-handover">Catatan serah terima pekerjaan</label>
                        <textarea id="resignation-handover" rows="4" maxlength="3000" placeholder="Daftar pekerjaan, dokumen, atau tanggung jawab yang perlu dialihkan..."></textarea>
                        <div id="resignation-form-error" class="premium-dialog-error"></div>
                        <button type="submit" class="primary-btn resignation-submit-btn"><i class="ph ph-paper-plane-tilt"></i> Kirim Pengajuan</button>
                    </form>
                    <div class="setup-card resignation-history-card">
                        <div class="resignation-history-header">
                            <div>
                                <h3>Riwayat Pengajuan Saya</h3>
                                <p>Status yang ditampilkan berasal dari keputusan terbaru.</p>
                            </div>
                            <i class="ph ph-clock-counter-clockwise"></i>
                        </div>
                        <div id="resignation-history-list" class="resignation-history-list">
                            <div class="approval-loading"><i class="ph ph-spinner ph-spin"></i> Menyinkronkan riwayat...</div>
                        </div>
                    </div>
                </div>

            </section>

            <section id="view-approval" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="margin-bottom: 4px;">Pusat Persetujuan</h2>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 13px;">Pengajuan ditampilkan sesuai tahap persetujuan dan kewenangan akun Anda.</p>
                    </div>
                    <div class="filters" id="approval-inbox-tabs" style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button class="filter-btn active" data-div="all">Semua <span class="badge warning" id="badge-approval-all">0</span></button>
                        <button class="filter-btn" data-div="marketing">Marketing <span class="badge warning" id="badge-approval-marketing">0</span></button>
                        <button class="filter-btn" data-div="operasional">Operasional <span class="badge warning" id="badge-approval-operasional">0</span></button>
                        <button class="filter-btn" data-div="finance">Finance <span class="badge warning" id="badge-approval-finance">0</span></button>
                        <button class="filter-btn" data-div="hrd">HRD <span class="badge warning" id="badge-approval-hrd">0</span></button>
                    </div>
                </div>

                <div id="approval-mode-tabs" class="approval-mode-tabs">
                    <button class="approval-mode-tab active" data-mode="pending"><i class="ph ph-hourglass"></i> Menunggu Keputusan</button>
                    <button class="approval-mode-tab" data-mode="history"><i class="ph ph-clock-counter-clockwise"></i> Riwayat Keputusan</button>
                </div>

                <div class="erp-deletion-policy-note">
                    <i class="ph ph-shield-check"></i>
                    <div>
                        <strong>Kebijakan penghapusan data ERP</strong>
                        <span>Data bersama wajib melalui persetujuan atasan. Transaksi keuangan dibalik, dokumen bertanda tangan dicabut, dan riwayat keputusan serta audit tidak dapat dihapus.</span>
                    </div>
                </div>
                 
                <div id="approval-list-container" class="approval-list" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Rendered by JS -->
                </div>
            </section>
            
            <!-- KPI Setup View -->
            <section id="view-setup" class="view-section" style="display: none;">
                <div class="setup-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <!-- Form Setup -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 32px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h2 id="goal-kpi-builder-title">Set Up Goal Divisi</h2>
                            <select id="kpi-setup-role-select" class="glass-select" style="min-width: 150px;">
                                <option>Role: Web Developer</option>
                                <option>Role: Content Creator</option>
                                <option>Role: Sales</option>
                            </select>
                        </div>
                        
                        <p class="kpi-sheet-intro">Workspace KPI berbasis formula: target, bobot, realisasi, pencapaian, dan kontribusi saling menghitung secara realtime.</p>
                        <form id="goal-kpi-builder-form" class="kpi-sheet-form" style="display: flex; flex-direction: column; gap: 20px;">
                            <div id="kpi-goal-select-group" class="form-group" style="display: none; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Goal CEO yang Dituju <span style="color: var(--text-muted);">(opsional)</span></label>
                                <select id="kpi-goal-select" class="glass-select" style="padding: 12px;">
                                    <option value="">Tanpa goal CEO — ajukan KPI mandiri</option>
                                </select>
                                <span style="font-size: 11px; color: var(--text-muted);">Manager tetap dapat mengajukan KPI berdasarkan kebutuhan divisi meskipun CEO belum menetapkan goal.</span>
                            </div>
                            <div id="kpi-plan-title-group" class="form-group" style="display: none; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Judul / Fokus Rencana KPI</label>
                                <input id="kpi-plan-title" type="text" maxlength="255" placeholder="Contoh: Peningkatan Akuisisi Klien Kuartal III" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                                <span style="font-size: 11px; color: var(--text-muted);">Rencana ini tetap harus disahkan CEO sebelum menjadi KPI aktif.</span>
                            </div>
                            <div id="kpi-supporting-file-group" class="form-group" style="display: none; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Dokumen Pendukung <span style="color: var(--text-muted);">(opsional)</span></label>
                                <input id="kpi-supporting-file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,.zip">
                                <span style="font-size: 11px; color: var(--text-muted);">Proposal, spreadsheet, atau bukti pendukung maksimal 10 MB ikut dalam approval CEO.</span>
                            </div>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Indicator Name</label>
                                <input id="kpi-indicator-name" type="text" placeholder="e.g., High-Ticket Leads" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Target Value</label>
                                    <input id="kpi-target-value" type="number" min="0.01" step="0.01" placeholder="e.g., 10" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                                </div>
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Measurement Type</label>
                                    <select id="kpi-measurement-type" class="glass-select" style="padding: 12px;">
                                        <option value="count">Number (Leads, Videos)</option>
                                        <option value="currency">Currency (Rp)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Bobot (%)</label>
                                    <input id="kpi-weight" type="number" min="1" max="100" step="0.01" placeholder="e.g., 30" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                                </div>
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Requires Evidence?</label>
                                    <div style="display: flex; align-items: center; height: 100%;">
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                                            <span>Yes, require link submission</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="kpi-technical-options" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Arah Target</label>
                                    <select id="kpi-direction" class="glass-select" style="padding: 12px;">
                                        <option value="higher_is_better">Semakin tinggi semakin baik</option>
                                        <option value="lower_is_better">Semakin rendah semakin baik</option>
                                    </select>
                                </div>
                                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-size: 13px; color: var(--text-secondary);">Sumber Data</label>
                                    <select id="kpi-data-source" class="glass-select" style="padding: 12px;">
                                        <option value="manual">Input manual pada sheet KPI</option>
                                        <option value="tasks">Task yang diverifikasi</option>
                                        <option value="leads">Leads</option>
                                        <option value="client_inflows">Pemasukan klien</option>
                                        <option value="attendance">Kehadiran</option>
                                    </select>
                                </div>
                            </div>
                            <div id="kpi-live-formula-preview" class="kpi-live-formula-preview">
                                <span><small>Realisasi</small><input id="kpi-current-value-preview" type="number" min="0" step="0.01" value="0"></span>
                                <span><small>Pencapaian</small><strong id="kpi-achievement-preview">0%</strong></span>
                                <span><small>Kontribusi Bobot</small><strong id="kpi-contribution-preview">0.00</strong></span>
                                <code id="kpi-formula-preview">0 ÷ target × 100%</code>
                            </div>
                            
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Justification / Proposal Notes</label>
                                <textarea id="kpi-proposal-notes" rows="3" placeholder="Jelaskan alasan mengapa KPI ini ditambahkan..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; width: 100%; outline: none; resize: none;"></textarea>
                            </div>

                            <div id="kpi-plan-draft" style="display: none; border: 1px dashed var(--glass-border); border-radius: var(--radius-sm); padding: 12px;">
                                <div id="kpi-plan-draft-list" style="display: flex; flex-direction: column; gap: 8px;"></div>
                                <div id="kpi-plan-weight-total" style="margin-top: 10px; font-size: 12px; color: var(--warning);">Total bobot: 0%</div>
                            </div>

                            <button id="btn-create-division-goal" type="button" class="primary-btn" style="margin-top: 12px; justify-content: center; width: 100%; background: var(--primary); color: black;">
                                <i class="ph ph-target"></i> Tetapkan Goal Divisi
                            </button>
                            <button id="btn-add-kpi-draft" type="button" class="primary-btn" style="display: none; margin-top: 12px; justify-content: center; width: 100%; background: rgba(10, 132, 255, 0.18); color: var(--info); border: 1px solid rgba(10, 132, 255, 0.35);">
                                <i class="ph ph-plus"></i> Tambahkan KPI ke Rencana
                            </button>
                            <button id="btn-submit-kpi-plan" type="button" class="primary-btn" style="display: none; justify-content: center; width: 100%; background: var(--warning); color: black;">
                                <i class="ph ph-paper-plane-tilt"></i> Ajukan Rencana KPI ke CEO
                            </button>
                        </form>
                    </div>
                    
                    <!-- Reward & Punishment / Budget Rule -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-scales" style="color: var(--primary); font-size: 20px;"></i> Rules Engine</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;" id="rules-engine-container">
                                <!-- Populated dynamically via JS -->
                            </div>
                            <button id="btn-add-new-rule" style="background: transparent; border: 1px dashed var(--glass-border); color: var(--text-secondary); padding: 10px; border-radius: var(--radius-sm); margin-top: 12px; cursor: pointer; width: 100%; font-family: inherit;">
                                + Add New Rule
                            </button>
                        </div>
                        
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-wallet" style="color: var(--primary); font-size: 20px;"></i> Div. Budget Control</h3>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">Max Monthly Marketing Budget</label>
                                <div style="display: flex; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); overflow: hidden;">
                                    <span style="padding: 12px; background: rgba(0,0,0,0.3); color: var(--text-muted); border-right: 1px solid var(--glass-border);">Rp</span>
                                    <input type="text" value="27,000,000" style="background: transparent; border: none; padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                                </div>
                                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Used for CAC calculations</p>
                            </div>
                        </div>

                        <!-- Phase 10: D-Point Setup -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;"><i class="ph ph-coins" style="color: var(--primary); font-size: 20px;"></i> D-Point / Meal Allowance</h3>
                            <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <label style="font-size: 13px; color: var(--text-secondary);">D-Point Daily Rate (Nominal)</label>
                                <div style="display: flex; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); overflow: hidden;">
                                    <span style="padding: 12px; background: rgba(0,0,0,0.3); color: var(--text-muted); border-right: 1px solid var(--glass-border);">Rp</span>
                                    <input type="text" value="50,000" style="background: transparent; border: none; padding: 12px; color: white; font-family: inherit; width: 100%; outline: none;">
                                </div>
                                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">If clocked in: +1 D-Point. If absent: Forfeited.</p>
                            </div>
                        </div>

                        <!-- Phase 12: User Management -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;" id="user-management-card">
                            <h3 style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="display: flex; align-items: center; gap: 8px;"><i class="ph ph-users-three" style="color: var(--primary); font-size: 20px;"></i> User & Org Management</span>
                                <span id="ceo-bypass-badge" style="display: none; background: rgba(255, 59, 48, 0.2); color: var(--danger); font-size: 10px; padding: 2px 6px; border-radius: 4px;">SUPER ADMIN BYPASS</span>
                            </h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <button id="btn-add-staff" class="primary-btn" style="width: 100%; justify-content: center; background: rgba(255,255,255,0.1); color: white;"><i class="ph ph-user-plus"></i> Add New Staff (Email OTP)</button>
                                <div id="ceo-management-tools" style="display: none; flex-direction: column; gap: 12px; margin-top: 12px; border-top: 1px solid var(--glass-border); padding-top: 12px;">
                                    <button id="btn-create-division" class="primary-btn" style="width: 100%; justify-content: center; background: rgba(242, 201, 76, 0.2); color: var(--primary);"><i class="ph ph-shield-star"></i> Create New Division</button>
                                    <button id="btn-appoint-manager" class="primary-btn" style="width: 100%; justify-content: center; background: rgba(242, 201, 76, 0.2); color: var(--primary);"><i class="ph ph-crown"></i> Appoint New Manager</button>
                                </div>
                            </div>
                        </div>

                        <div class="setup-card erp-control-center-card" id="erp-feature-control-card" style="display: none;">
                            <div class="control-card-heading">
                                <div>
                                    <h3><i class="ph ph-toggle-right"></i> Modul ERP</h3>
                                    <p>Aktifkan hanya modul yang dibutuhkan perusahaan. Modul roadmap tidak dapat diaktifkan sebelum implementasinya selesai.</p>
                                </div>
                                <span class="control-admin-badge">CEO ONLY</span>
                            </div>
                            <div id="erp-feature-list" class="erp-feature-list">
                                <div class="control-loading"><i class="ph ph-spinner ph-spin"></i> Memuat konfigurasi modul...</div>
                            </div>
                        </div>

                        <div class="setup-card erp-control-center-card" id="erp-security-control-card" style="display: none;">
                            <div class="control-card-heading">
                                <div>
                                    <h3><i class="ph ph-shield-check"></i> Keamanan Login</h3>
                                    <p>OTP selalu enam digit, sekali pakai, dan tidak pernah ditampilkan di browser.</p>
                                </div>
                                <span class="control-admin-badge secure">SECURE</span>
                            </div>

                            <div id="erp-mail-readiness" class="erp-security-readiness"></div>

                            <form id="erp-mail-settings-form" class="control-form control-password-form">
                                <h4>SMTP Pengiriman OTP</h4>
                                <p>Kredensial disimpan terenkripsi dan tidak pernah ditampilkan kembali. Gunakan App Password, bukan password utama Gmail.</p>
                                <div class="control-form-grid">
                                    <label>SMTP Host
                                        <input id="security-mail-host" type="text" maxlength="255" placeholder="smtp.gmail.com" required>
                                    </label>
                                    <label>Port
                                        <input id="security-mail-port" type="number" min="1" max="65535" value="587" required>
                                    </label>
                                    <label>Keamanan koneksi
                                        <select id="security-mail-scheme" required>
                                            <option value="smtp">STARTTLS / SMTP (587)</option>
                                            <option value="smtps">SSL / SMTPS (465)</option>
                                        </select>
                                    </label>
                                    <label>Email SMTP
                                        <input id="security-mail-username" type="email" maxlength="255" autocomplete="username" required>
                                    </label>
                                    <label>Email pengirim
                                        <input id="security-mail-from-address" type="email" maxlength="255" required>
                                    </label>
                                    <label>Nama pengirim
                                        <input id="security-mail-from-name" type="text" maxlength="120" value="Suba Arch ERP" required>
                                    </label>
                                </div>
                                <label class="control-field">App Password Gmail
                                    <input id="security-mail-password" type="password" minlength="16" maxlength="255" autocomplete="new-password" placeholder="Isi hanya untuk memasang atau mengganti">
                                </label>
                                <button type="submit" class="primary-btn control-save-btn"><i class="ph ph-envelope-simple"></i> Simpan SMTP Terenkripsi</button>
                            </form>

                            <form id="erp-security-policy-form" class="control-form">
                                <div class="control-form-grid">
                                    <label>Masa berlaku OTP (menit)
                                        <input id="security-otp-expiry" type="number" min="2" max="10" required>
                                    </label>
                                    <label>Jeda kirim ulang (detik)
                                        <input id="security-otp-resend" type="number" min="30" max="300" required>
                                    </label>
                                    <label>Maksimal percobaan
                                        <input id="security-otp-attempts" type="number" min="3" max="10" required>
                                    </label>
                                    <label>Durasi penguncian (menit)
                                        <input id="security-otp-lock" type="number" min="5" max="60" required>
                                    </label>
                                </div>
                                <div class="control-inline-setting">
                                    <div>
                                        <b>Gerbang password perusahaan</b>
                                        <small>Lapisan tambahan sebelum halaman login OTP.</small>
                                    </div>
                                    <label class="feature-switch">
                                        <input id="security-gate-enabled" type="checkbox">
                                        <span></span>
                                    </label>
                                </div>
                                <label class="control-field">Masa akses gerbang (jam)
                                    <input id="security-gate-hours" type="number" min="1" max="168" required>
                                </label>
                                <button type="submit" class="primary-btn control-save-btn"><i class="ph ph-floppy-disk"></i> Simpan Kebijakan</button>
                            </form>

                            <form id="erp-gate-password-form" class="control-form control-password-form">
                                <h4>Atur / Rotasi Password Perusahaan</h4>
                                <p>Password minimal 12 karakter dan wajib memiliki huruf besar, huruf kecil, angka, serta simbol. Password tidak akan ditampilkan kembali.</p>
                                <label class="control-field">Password baru
                                    <input id="security-gate-password" type="password" minlength="12" maxlength="255" autocomplete="new-password" required>
                                </label>
                                <label class="control-field">Ulangi password baru
                                    <input id="security-gate-password-confirmation" type="password" minlength="12" maxlength="255" autocomplete="new-password" required>
                                </label>
                                <label class="control-check">
                                    <input id="security-gate-enable-now" type="checkbox" checked>
                                    Langsung aktifkan setelah password disimpan
                                </label>
                                <button type="submit" class="primary-btn control-save-btn"><i class="ph ph-key"></i> Simpan & Amankan Portal</button>
                            </form>
                        </div>

                        <div class="setup-card erp-control-center-card" id="erp-retention-control-card" style="display: none;">
                            <div class="control-card-heading">
                                <div>
                                    <h3><i class="ph ph-archive"></i> Pusat Retensi Data</h3>
                                    <p>Kelola arsip pegawai nonaktif dan kapasitas penyimpanan tanpa menghilangkan jejak audit perusahaan.</p>
                                </div>
                                <span class="control-admin-badge">CEO ONLY</span>
                            </div>

                            <div id="retention-summary" class="retention-summary-grid">
                                <div class="control-loading"><i class="ph ph-spinner ph-spin"></i> Memuat ringkasan retensi...</div>
                            </div>

                            <div id="retention-storage-status" class="erp-security-readiness ready"></div>

                            <form id="erp-retention-policy-form" class="control-form">
                                <div class="control-form-grid">
                                    <label>Arsipkan akun nonaktif setelah (hari)
                                        <input id="retention-archive-days" type="number" min="1" max="3650" required>
                                    </label>
                                    <label>Anonimkan data pribadi setelah (hari)
                                        <input id="retention-anonymize-days" type="number" min="365" max="7300" required>
                                    </label>
                                    <label>Hapus permanen data operasional terhapus setelah (hari)
                                        <input id="retention-purge-days" type="number" min="90" max="3650" required>
                                    </label>
                                    <label>Peringatan penyimpanan (MB)
                                        <input id="retention-storage-warning" type="number" min="100" max="102400" required>
                                    </label>
                                </div>

                                <div class="control-inline-setting">
                                    <div>
                                        <b>Anonimisasi otomatis</b>
                                        <small>Menghapus nama, email, jabatan, dan tanda tangan pegawai lama; kode pegawai serta relasi audit tetap dipertahankan.</small>
                                    </div>
                                    <label class="feature-switch">
                                        <input id="retention-auto-anonymize" type="checkbox">
                                        <span></span>
                                    </label>
                                </div>

                                <div class="control-inline-setting">
                                    <div>
                                        <b>Pembersihan permanen otomatis</b>
                                        <small>Hanya untuk data operasional yang sudah dihapus lunak dan melewati masa retensi. Default dinonaktifkan.</small>
                                    </div>
                                    <label class="feature-switch">
                                        <input id="retention-auto-purge" type="checkbox">
                                        <span></span>
                                    </label>
                                </div>

                                <div class="retention-protection-note">
                                    <i class="ph ph-lock-key"></i>
                                    <span>Audit keamanan, dokumen atau sertifikat bertanda tangan, serta data keuangan tidak ikut dihapus. Akun dengan <b>legal hold</b> selalu dilindungi.</span>
                                </div>

                                <button type="submit" class="primary-btn control-save-btn"><i class="ph ph-floppy-disk"></i> Simpan Kebijakan Retensi</button>
                                <button type="button" id="btn-run-retention" class="primary-btn retention-run-btn"><i class="ph ph-play-circle"></i> Jalankan Retensi Sekarang</button>
                            </form>

                            <div id="retention-last-run" class="retention-last-run">Belum ada proses retensi yang tercatat.</div>
                        </div>
                    </div>
                </div>

                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; margin-top: 24px;">
                    <h3 style="margin: 0 0 8px; display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-shield-check" style="color: var(--primary);"></i> Data Goal & KPI Terkelola
                    </h3>
                    <p style="margin: 0 0 16px; color: var(--text-secondary); font-size: 12px;">
                        Penghapusan goal, rencana KPI, dan indikator mengikuti relasi serta alur persetujuan. Riwayat keputusan tetap tersimpan.
                    </p>
                    <div id="governed-performance-data-list" style="display: flex; flex-direction: column; gap: 10px;"></div>
                </div>
            </section>

            <!-- My Tasks & KPIs View (Hidden by default) -->
            <section id="view-kpi-tasks" class="view-section" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div>
                        <h2 style="font-size: 20px; font-weight: 500;">My Personal Dashboard</h2>
                        <p style="color: var(--text-secondary); font-size: 13px;">Manage tasks, trace division & personal KPIs, and analyze your attendance hours deficit.</p>
                    </div>
                </div>

                <!-- Daily Task Reminder Banner -->
                <div id="daily-task-reminder-banner" style="display: none;"></div>

                <!-- Top Row: Attendance Summary & KPIs Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
                    <!-- Attendance deficit tracker -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                        <h3 style="margin-top: 0; margin-bottom: 12px; color: var(--primary); font-size: 14px;"><i class="ph ph-calendar-check"></i> HRIS Attendance Summary</h3>
                        <div id="user-attendance-summary-widget" style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: white;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <!-- Division KPIs -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="margin-top: 0; margin-bottom: 12px; color: var(--warning); font-size: 14px;"><i class="ph ph-shield-star"></i> KPI Divisi (Division Goal)</h3>
                            <div id="division-kpi-list-container" style="display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                                <!-- Populated dynamically via JS -->
                            </div>
                        </div>
                        <!-- Division Goal Comments & Feedback -->
                        <div style="margin-top: 16px; border-top: 1px solid var(--glass-border); padding-top: 12px;">
                            <h4 style="margin: 0 0 8px 0; color: var(--warning); font-size: 11px; text-transform: uppercase;"><i class="ph ph-chats"></i> Feedback Goal Divisi</h4>
                            <div id="division-goal-comments-list" style="max-height: 100px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; margin-bottom: 8px;">
                                <!-- Populated dynamically -->
                            </div>
                            <div id="division-goal-input-area" style="display: none; gap: 8px; align-items: center;">
                                <input type="text" id="division-goal-comment-input" placeholder="Komentari Goal Divisi..." style="flex: 1; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 4px; padding: 6px 10px; color: white; font-size: 11px; outline: none; font-family: inherit;">
                                <button id="btn-submit-divgoal-comment" class="primary-btn" style="padding: 6px 12px; font-size: 11px; font-family: inherit; justify-content: center;"><i class="ph ph-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Personal KPIs -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                        <h3 style="margin-top: 0; margin-bottom: 12px; color: var(--success); font-size: 14px;"><i class="ph ph-target"></i> KPI Pribadi (My Specific KPI)</h3>
                        <div id="user-kpi-list-container" style="display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <!-- Left: Create Task Form -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <!-- Create Task Form -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-top: 0; margin-bottom: 16px; color: var(--primary); font-size: 16px;"><i class="ph ph-plus-circle"></i> Buat Task Baru (My KPI)</h3>
                            <form id="create-task-form" style="display: flex; flex-direction: column; gap: 12px;">
                                <div id="task-assignee-container" style="display: none; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Ditujukan untuk Karyawan</label>
                                    <select id="task-assignee-select" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                                        <!-- Populated dynamically -->
                                    </select>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Nama / Judul Task</label>
                                    <input type="text" id="task-title-input" placeholder="Masukkan pekerjaan..." style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit;" required>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">KPI Target Pribadi <span style="color: var(--text-muted);">(opsional)</span></label>
                                    <select id="task-kpi-select" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                                        <!-- Populated dynamically -->
                                    </select>
                                    <span style="font-size: 10px; color: var(--text-muted);">Task mandiri tanpa KPI tetap dapat diajukan dan akan menunggu persetujuan manager.</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Lampiran Task / Laporan <span style="color: var(--text-muted);">(opsional)</span></label>
                                    <input type="file" id="task-attachment-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,.zip">
                                    <span style="font-size: 10px; color: var(--text-muted);">Maksimal 10 MB; file tersimpan privat untuk pihak yang berwenang.</span>
                                </div>
                                <button type="submit" class="primary-btn" style="width: 100%; justify-content: center; font-family: inherit;"><i class="ph ph-plus"></i> Tambah Task</button>
                            </form>
                        </div>
                        
                        <!-- Persetujuan Cuti Tim (Manager / CEO) -->
                        <div id="manager-leave-approval-card" class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; display: none; margin-top: 24px;">
                            <h3 style="margin-top: 0; margin-bottom: 16px; color: var(--warning); font-size: 16px;"><i class="ph ph-calendar-blank"></i> Persetujuan Cuti Staf</h3>
                            <div id="manager-leave-requests-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 200px; overflow-y: auto;">
                                <!-- Populated dynamically -->
                            </div>
                        </div>

                        <!-- Persetujuan Task Staf (Manager Level) -->
                        <div id="manager-task-approval-card" class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; display: none; margin-top: 16px;">
                            <h3 style="margin-top: 0; margin-bottom: 16px; color: var(--warning); font-size: 16px;"><i class="ph ph-check-square-offset"></i> Persetujuan Tugas Staf (Pending Approval)</h3>
                            <div id="manager-task-requests-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto;">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- Right: Task Checklist and Comments/Messaging -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <!-- Team Monitor for Manager/CEO -->
                        <div id="manager-team-monitor" class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; display: none;">
                            <h3 style="margin-top: 0; margin-bottom: 16px; color: var(--warning); font-size: 16px;"><i class="ph ph-users-three"></i> Monitoring Tugas Tim</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Pilih Anggota Tim</label>
                                    <select id="team-member-select" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                                        <!-- Populated dynamically -->
                                    </select>
                                    <button id="btn-manager-assign-task-to-team" type="button" class="primary-btn" style="width: 100%; justify-content: center; font-family: inherit; background: rgba(10, 132, 255, 0.15); color: var(--info); border: 1px solid rgba(10, 132, 255, 0.3); font-weight: 600; margin-top: 8px;"><i class="ph ph-plus-circle"></i> + Buat Task Baru untuk Anggota Tim</button>
                                </div>
                                <!-- Dynamic KPI Weight Editor -->
                                <div id="manager-kpi-weight-editor" style="display: none; margin-top: 16px; border-top: 1px solid var(--glass-border); padding-top: 16px;">
                                    <h4 style="margin: 0 0 12px 0; color: var(--success); font-size: 13px;"><i class="ph ph-seal-check"></i> Bobot KPI Divisi (Disahkan CEO)</h4>
                                    <div id="kpi-weight-sliders-list" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 12px;">
                                        <!-- Sliders populated dynamically -->
                                    </div>
                                    <button id="btn-save-kpi-weights" class="primary-btn" style="width: 100%; justify-content: center; font-family: inherit; background: var(--success); color: #020617; font-weight: 700;"><i class="ph ph-pencil-simple"></i> Ajukan Revisi Rencana KPI</button>
                                    <button id="btn-request-delete-staff" class="primary-btn" style="width: 100%; justify-content: center; font-family: inherit; background: var(--danger); color: white; font-weight: 700; margin-top: 10px; display: none;"><i class="ph ph-trash"></i> Minta Hapus Staf dari Tim</button>
                                </div>
                            </div>
                        </div>

                        <!-- Task List Container -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; flex: 1; display: flex; flex-direction: column;">
                            <h3 id="task-list-title" style="margin-top: 0; margin-bottom: 16px; color: white; font-size: 16px;">Daftar Tugas Saya</h3>
                            <div id="kpi-task-list" style="display: flex; flex-direction: column; gap: 16px; max-height: 500px; overflow-y: auto; flex: 1;">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Live Attendance Tracker View (CEO/Manager) -->
            <section id="view-attendance" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>Live Attendance & HRIS</h2>
                        <p style="color: var(--text-secondary); font-size: 13px;">Geolocation tracker, leave management, and D-Point accumulation.</p>
                    </div>
                    <div class="filters" id="attendance-filter-container">
                        <button class="filter-btn active" id="attendance-btn-today">Today</button>
                        <button class="filter-btn" id="attendance-btn-marketing">Marketing</button>
                        <button class="filter-btn" id="attendance-btn-ops">Operasional</button>
                        <button class="filter-btn" id="attendance-btn-finance">Finance</button>
                        <button class="filter-btn" id="attendance-btn-hrd">HRD</button>
                        <!-- Export Button -->
                        <button class="export-btn sheets" style="margin-left: 12px;"><i class="ph ph-file-csv"></i> Download Spreadsheet</button>
                    </div>
                </div>

                <!-- Phase 7: Office Hours Target & Phase 9: Leave Request -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 24px;">
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                            <h3 style="font-size: 16px;">Rekap Jam Kerja Saya Bulan Ini</h3>
                            <span style="font-size: 12px; color: var(--primary);">Waktu server</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                                    <span>Target jam kerja</span>
                                    <span id="attendance-personal-target" style="color: var(--primary);">Memuat...</span>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                                    <span>Jam tercatat</span>
                                    <span id="attendance-personal-worked" style="color: var(--success);">Memuat...</span>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                                    <span>Kekurangan jam</span>
                                    <span id="attendance-personal-remaining" style="color: var(--warning);">Memuat...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="setup-card" id="my-leave-request-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                        <h3 style="font-size: 16px; margin-bottom: 16px;">Leave & Permission (Cuti/Izin)</h3>
                        <button id="btn-request-leave" class="primary-btn" style="width: 100%; justify-content: center; margin-bottom: 16px;"><i class="ph ph-calendar-plus"></i> Request Leave</button>
                        <div style="font-size: 12px; color: var(--text-secondary); background: rgba(255,255,255,0.05); padding: 12px; border-radius: 6px;">
                            <strong>Your Leave Balance:</strong> 10 Days
                        </div>
                        <div id="my-leave-history-list" style="display: flex; flex-direction: column; gap: 8px; margin-top: 14px;"></div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <!-- Map Mockup Container -->
                    <div id="live-attendance-map-card" class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 0; overflow: hidden; position: relative; height: 500px; display: flex; align-items: center; justify-content: center; width: 100%;">
                        <!-- Rendered Dynamically via JS -->
                    </div>
                    
                    <!-- Staff List -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px; overflow-y: auto; max-height: 500px;">
                        <h3 style="margin-bottom: 16px;">Clock-in Log</h3>
                        <div id="attendance-log-list" style="display: flex; flex-direction: column; gap: 12px;">
                            <!-- Log Item -->
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 12px; display: flex; gap: 12px; align-items: center;">
                                <div class="member-avatar" style="width: 40px; height: 40px; font-size: 14px; border-radius: var(--radius-sm);">MZ</div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 600;">M. Maulana Zakaria</div>
                                    <div style="font-size: 11px; color: var(--text-secondary); display: flex; gap: 8px; margin-top: 4px;">
                                        <span><i class="ph ph-clock"></i> 08:15 AM</span>
                                        <span style="color: var(--success);"><i class="ph ph-check-circle"></i> On Time</span>
                                    </div>
                                    <div style="font-size: 11px; color: var(--primary); margin-top: 4px;"><i class="ph ph-coins"></i> D-Point: Rp 50.000</div>
                                </div>
                            </div>
                            <!-- Log Item -->
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 12px; display: flex; gap: 12px; align-items: center;">
                                <div class="member-avatar" style="width: 40px; height: 40px; font-size: 14px; border-radius: var(--radius-sm);">DB</div>
                                <div style="flex: 1;">
                                    <div style="font-size: 14px; font-weight: 600;">D BEST AR</div>
                                    <div style="font-size: 11px; color: var(--text-secondary); display: flex; gap: 8px; margin-top: 4px;">
                                        <span><i class="ph ph-clock"></i> 09:42 AM</span>
                                        <span style="color: var(--warning);"><i class="ph ph-warning-circle"></i> Late 42m</span>
                                    </div>
                                    <div style="font-size: 11px; color: var(--danger); margin-top: 4px;"><i class="ph ph-coins"></i> D-Point: Forfeited (Late > 30m)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- HRD Workspace View (Hidden by default) -->
            <section id="view-hrd" class="view-section" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 20px; font-weight: 500;">HRD Division Portal</h2>
                        <p style="color: var(--text-secondary); font-size: 13px;">Manage employees, track attendance deficit hours, and review leave requests.</p>
                    </div>
                </div>

                <!-- HR Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Total Employees</div>
                        <div id="hr-stat-employees" style="font-size: 28px; font-weight: 700; color: white; margin-top: 8px;">0</div>
                    </div>
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Today WFO/WFH</div>
                        <div id="hr-stat-present" style="font-size: 28px; font-weight: 700; color: var(--success); margin-top: 8px;">0</div>
                    </div>
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Leave Requests</div>
                        <div id="hr-stat-leave" style="font-size: 28px; font-weight: 700; color: var(--warning); margin-top: 8px;">0</div>
                    </div>
                </div>

                <!-- HRD Main Panels -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- 1. Employee Directory and Attendance Deficit -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                            <h3 style="margin: 0; color: white; font-size: 16px;"><i class="ph ph-users-three"></i> Employee Database & Working Hours Deficit</h3>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button id="hr-btn-export-attendance-csv" class="primary-btn" style="background: rgba(52, 199, 89, 0.15); color: var(--success); border: 1px solid rgba(52, 199, 89, 0.3); font-size: 12px; padding: 8px 14px;"><i class="ph ph-file-csv"></i> Download All-Staff Attendance (Spreadsheet)</button>
                                <button id="hr-btn-add-staff" class="primary-btn" style="padding: 8px 16px; font-size: 12px;"><i class="ph ph-user-plus"></i> Add New Employee</button>
                            </div>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                                <thead>
                                    <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-secondary);">
                                        <th style="padding: 12px 8px;">Name</th>
                                        <th style="padding: 12px 8px;">Title & Role</th>
                                        <th style="padding: 12px 8px;">Target Working Days</th>
                                        <th style="padding: 12px 8px;">Hours Worked</th>
                                        <th style="padding: 12px 8px;">Deficit Hours</th>
                                        <th style="padding: 12px 8px;">Level</th>
                                    </tr>
                                </thead>
                                <tbody id="hr-employee-table-body" style="color: white;">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Global Attendance Logs -->
                    <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                        <h3 style="margin-top: 0; margin-bottom: 16px; color: white; font-size: 16px;"><i class="ph ph-clock"></i> Global Live Attendance Log (HR)</h3>
                        <div id="hr-attendance-logs" style="display: flex; flex-direction: column; gap: 8px; max-height: 300px; overflow-y: auto;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                    <!-- HRD Configurations (Calendar Override & D-Points) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 24px;">
                        <!-- Calendar overrides panel -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-top: 0; margin-bottom: 12px; color: var(--primary); font-size: 16px;"><i class="ph ph-calendar"></i> Working Calendar Override</h3>
                            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 16px;">Ubah tanggal merah/hari libur nasional menjadi hari kerja wajib bagi seluruh staf.</p>
                            <div style="display: flex; gap: 8px; margin-bottom: 14px;">
                                <input type="date" id="hr-calendar-date-input" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 12px; border-radius: 6px; flex: 1; outline: none; font-family: inherit; font-size: 13px;">
                                <button id="btn-add-calendar-override" class="primary-btn" style="font-size: 12px; padding: 8px 14px;"><i class="ph ph-plus"></i> Set Wajib Masuk</button>
                            </div>
                            <div id="hr-calendar-override-list" style="display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto;">
                                <!-- Populated dynamically with checkbox switches -->
                            </div>
                        </div>

                        <!-- D-Point config panel -->
                        <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                            <h3 style="margin-top: 0; margin-bottom: 12px; color: var(--success); font-size: 16px;"><i class="ph ph-coins"></i> D-Point Rate Configuration</h3>
                            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 16px;">Tentukan besaran nilai nominal D-Point harian per karyawan.</p>
                            <form id="hr-dpoint-form" style="display: flex; flex-direction: column; gap: 12px;">
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Pilih Karyawan</label>
                                    <select id="hr-dpoint-user-select" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                                        <!-- Populated dynamically -->
                                    </select>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; color: var(--text-secondary);">Nominal D-Point (Rp)</label>
                                    <input type="number" id="hr-dpoint-value-input" placeholder="e.g. 50000" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;" required>
                                </div>
                                <button type="submit" class="primary-btn" style="width: 100%; justify-content: center; font-family: inherit;"><i class="ph ph-floppy-disk"></i> Simpan Tarif D-Point</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Phase 11: Operations Dashboard (Manager) -->
            <section id="view-ops-dashboard" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 24px;">
                    <h2 style="font-size: 20px;">Operations & Project Tracker</h2>
                    <p style="color: var(--text-secondary); font-size: 13px;">Gantt Chart & Project Delivery Status</p>
                </div>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <p class="kpi-title"><i class="ph ph-hard-hat"></i> Active Projects</p>
                        <p class="kpi-value">12</p>
                        <p class="kpi-trend positive"><i class="ph ph-trend-up"></i> +2 this month</p>
                    </div>
                    <div class="kpi-card">
                        <p class="kpi-title"><i class="ph ph-check-circle"></i> Zero Defect Rate</p>
                        <p class="kpi-value">95%</p>
                        <p class="kpi-trend positive"><i class="ph ph-trend-up"></i> +5% from avg</p>
                    </div>
                </div>
                
                <h3 style="margin: 24px 0 16px;">Active Project Timelines</h3>
                <div class="gantt-container">
                    <div class="gantt-row">
                        <div class="gantt-label">Villa SCBD (Bpk. Andi)</div>
                        <div class="gantt-track">
                            <div class="gantt-bar survey">Survey (10%)</div>
                        </div>
                    </div>
                    <div class="gantt-row">
                        <div class="gantt-label">Cafe Senopati</div>
                        <div class="gantt-track">
                            <div class="gantt-bar design">Design (45%)</div>
                        </div>
                    </div>
                    <div class="gantt-row">
                        <div class="gantt-label">Rumah Bintaro</div>
                        <div class="gantt-track">
                            <div class="gantt-bar build">Construction (80%)</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Phase 11: Operations Staff Workspace -->
            <section id="view-ops-staff" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 24px;">
                    <h2 style="font-size: 20px;">Stage-Gate Workspace</h2>
                    <p style="color: var(--text-secondary); font-size: 13px;">My Active Tasks</p>
                </div>
                <div class="stage-gate-card">
                    <h3 style="font-size: 16px;">Project: Villa SCBD (Current: Survey)</h3>
                    <div class="stage active">
                        <div class="stage-icon"><i class="ph ph-map-pin"></i></div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 14px; margin-bottom: 4px;">Fase 1: Survey Lokasi & Pengukuran</h4>
                            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">Upload dokumen denah dan foto lokasi untuk di-approve Manager.</p>
                            <input type="file" style="margin-bottom: 8px; font-size: 12px;">
                            <button class="primary-btn" style="font-size: 11px; padding: 4px 12px;">Submit Evidence</button>
                        </div>
                    </div>
                    <div class="stage">
                        <div class="stage-icon"><i class="ph ph-pen-nib"></i></div>
                        <div style="flex: 1;">
                            <h4 style="font-size: 14px; margin-bottom: 4px;">Fase 2: Desain Arsitektur</h4>
                            <p style="font-size: 12px; color: var(--text-secondary);">Terkunci. Menunggu approval Fase 1.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Phase 11: Finance Dashboard (Manager Data Transfer Klien) -->
            <section id="view-finance-dashboard" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 20px; font-weight: 600; color: white;"><i class="ph ph-bank" style="color: var(--primary);"></i> Finance & Cashflow - Data Transfer Klien 2026</h2>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Pencatatan pemasukan klien, perhitungan sisa pembayaran otomatis, dan status lunas per termin.</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <input type="month" id="inflow-month-filter" style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 8px 12px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                        <button id="btn-open-inflow-modal" class="primary-btn" style="background: var(--primary); color: #020617; font-weight: 700; font-family: inherit; font-size: 12px;"><i class="ph ph-plus-circle"></i> + Pop-up Modal Input</button>
                        <button id="btn-open-import-inflow-modal" class="primary-btn" style="background: rgba(10, 132, 255, 0.2); color: var(--info); border: 1px solid rgba(10, 132, 255, 0.4); font-family: inherit; font-size: 12px;"><i class="ph ph-upload-simple"></i> Upload File Spreadsheet</button>
                        <button id="btn-export-inflow-csv" class="primary-btn" style="background: rgba(52, 199, 89, 0.15); color: var(--success); border: 1px solid rgba(52, 199, 89, 0.3); font-family: inherit; font-size: 12px;"><i class="ph ph-file-csv"></i> Unduh Spreadsheet (CSV)</button>
                    </div>
                </div>

                <!-- Form Input Transfer Pemasukan Klien Baru (Step-by-step Wizard Input) -->
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; margin-bottom: 16px; color: white; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-plus-circle" style="color: var(--primary);"></i> Form Input Transfer Pemasukan Klien Baru
                    </h3>

                    <!-- Visual Step Progress Bar -->
                    <div class="wizard-progress-bar" style="display: flex; align-items: center; justify-content: space-between; background: rgba(0, 0, 0, 0.35); border: 1px solid var(--glass-border); border-radius: 10px; padding: 10px 16px; margin-bottom: 20px; gap: 8px; flex-wrap: wrap;">
                        <div class="wizard-progress-step active" id="wiz-progress-1" style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: var(--primary); background: rgba(242, 201, 76, 0.12); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(242, 201, 76, 0.3);">
                            <span style="background: var(--primary); color: #000; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">1</span>
                            <span>1. Data Klien</span>
                        </div>
                        <i class="ph ph-caret-right" style="color: var(--text-muted); font-size: 14px;"></i>
                        <div class="wizard-progress-step" id="wiz-progress-2" style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 500; color: var(--text-secondary); padding: 6px 14px; border-radius: 20px;">
                            <span style="background: rgba(255,255,255,0.1); color: var(--text-secondary); width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">2</span>
                            <span>2. Detail Proyek</span>
                        </div>
                        <i class="ph ph-caret-right" style="color: var(--text-muted); font-size: 14px;"></i>
                        <div class="wizard-progress-step" id="wiz-progress-3" style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 500; color: var(--text-secondary); padding: 6px 14px; border-radius: 20px;">
                            <span style="background: rgba(255,255,255,0.1); color: var(--text-secondary); width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">3</span>
                            <span>3. Pembayaran & Termin</span>
                        </div>
                    </div>

                    <form id="inline-inflow-form" style="display: flex; flex-direction: column; gap: 20px;">
                        
                        <!-- Step 1: Data Klien -->
                        <div class="wizard-step active" data-step="1">
                            <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                                <span><i class="ph ph-user"></i> Langkah 1: Data Identitas Klien</span>
                                <span style="font-size: 11px; color: var(--text-muted);">Isian 1 dari 3</span>
                            </div>
                            <div class="form-grid-3">
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Nama Klien *</label>
                                    <input type="text" id="inline-inflow-client-name" required placeholder="Nama Klien (e.g. Pak Sygma)..." class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">No. Klien</label>
                                    <input type="text" id="inline-inflow-client-no" placeholder="e.g. 600" class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Domisili / Kota</label>
                                    <input type="text" id="inline-inflow-domicile" placeholder="Kota / Domisili (e.g. Sukabumi)..." class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                                <button type="button" class="primary-btn btn-wizard-next" style="background: var(--info); color: white; padding: 8px 24px; border-radius: 6px; font-weight: 600;">Lanjut ke Detail Proyek <i class="ph ph-arrow-right"></i></button>
                            </div>
                        </div>

                        <!-- Step 2: Detail Proyek -->
                        <div class="wizard-step" data-step="2" style="display: none;">
                            <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                                <span><i class="ph ph-briefcase"></i> Langkah 2: Detail Paket & Proyek</span>
                                <span style="font-size: 11px; color: var(--text-muted);">Isian 2 dari 3</span>
                            </div>
                            <div class="form-grid-3">
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Start Project</label>
                                    <select id="inline-inflow-start-project" class="glass-select" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                        <option value="Jan">Januari</option>
                                        <option value="Feb">Februari</option>
                                        <option value="Mar">Maret</option>
                                        <option value="Apr">April</option>
                                        <option value="Mei">Mei</option>
                                        <option value="Jun">Juni</option>
                                        <option value="Jul">Juli</option>
                                        <option value="Ags">Agustus</option>
                                        <option value="Sep">September</option>
                                        <option value="Okt">Oktober</option>
                                        <option value="Nov">November</option>
                                        <option value="Des">Desember</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Paket *</label>
                                    <select id="inline-inflow-package" class="glass-select" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                        <option value="Survey">Survei</option>
                                        <option value="Bronze">Bronze</option>
                                        <option value="Silver">Silver</option>
                                        <option value="Gold">Gold</option>
                                        <option value="Diamond">Diamond</option>
                                        <option value="Custom">Custom / Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">PJ Survey</label>
                                    <input type="text" id="inline-inflow-pj-survey" placeholder="e.g. LANI" class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 14px;">
                                <button type="button" class="btn-wizard-prev" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border); padding: 8px 16px; border-radius: 6px; cursor: pointer;"><i class="ph ph-arrow-left"></i> Kembali</button>
                                <button type="button" class="primary-btn btn-wizard-next" style="background: var(--info); color: white; padding: 8px 24px; border-radius: 6px; font-weight: 600;">Lanjut ke Pembayaran <i class="ph ph-arrow-right"></i></button>
                            </div>
                        </div>

                        <!-- Step 3: Keuangan & Termin -->
                        <div class="wizard-step" data-step="3" style="display: none;">
                            <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                                <span><i class="ph ph-credit-card"></i> Langkah 3: Rincian Pembayaran & Termin</span>
                                <span style="font-size: 11px; color: var(--text-muted);">Isian 3 dari 3</span>
                            </div>
                            <div class="form-grid-3">
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Tanggal Transfer *</label>
                                    <input type="date" id="inline-inflow-date" required class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Nilai Total Project (Rp) *</label>
                                    <input type="number" id="inline-inflow-project-value" required min="0" placeholder="e.g. 11000000" class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Besar Pembayaran Ini (Rp) *</label>
                                    <input type="number" id="inline-inflow-payment-amount" required min="0" placeholder="e.g. 3300000" class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                            </div>
                            <div class="form-grid-3" style="margin-bottom: 0;">
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Total Termin Project *</label>
                                    <select id="inline-inflow-total-termin" class="glass-select" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                        <option value="Survei">Survei</option>
                                        <option value="3">3 Termin</option>
                                        <option value="4">4 Termin</option>
                                        <option value="1">1 Termin (Pelunasan Direct)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Termin ke *</label>
                                    <select id="inline-inflow-termin-no" class="glass-select" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                        <option value="Survei">Survei</option>
                                        <option value="1">Termin 1 (DP)</option>
                                        <option value="2">Termin 2</option>
                                        <option value="3">Termin 3</option>
                                        <option value="4">Termin 4</option>
                                        <option value="Revisi">Revisi</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Catatan Tambahan</label>
                                    <input type="text" id="inline-inflow-notes" placeholder="Opsional (misal: Tambah RAP)..." class="glass-input" style="width: 100%; background: var(--bg-sidebar); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 12px; transition: all 0.2s;">
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 14px;">
                                <button type="button" class="btn-wizard-prev" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border); padding: 8px 16px; border-radius: 6px; cursor: pointer;"><i class="ph ph-arrow-left"></i> Kembali</button>
                                <button type="submit" class="primary-btn" style="background: var(--primary); color: #020617; font-weight: 700; font-family: inherit; font-size: 13px; padding: 10px 28px; border-radius: 6px; box-shadow: 0 4px 15px rgba(242, 201, 76, 0.3); transition: all 0.2s;"><i class="ph ph-check-circle"></i> Simpan Data Transfer</button>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Auto-Calculated Summary Cards -->
                <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 20px;">
                    <div class="kpi-card" style="background: rgba(52, 199, 89, 0.1); border: 1px solid rgba(52, 199, 89, 0.3); padding: 16px; border-radius: var(--radius-md);">
                        <p class="kpi-title" style="font-size: 12px; color: var(--text-secondary); margin: 0;"><i class="ph ph-arrow-down-left" style="color: var(--success);"></i> Total Inflow (Bulan Ini)</p>
                        <p class="kpi-value" id="summary-total-inflow" style="font-size: 20px; font-weight: 700; color: var(--success); margin: 6px 0 0 0;">Rp 0</p>
                    </div>
                    <div class="kpi-card" style="background: rgba(255, 159, 10, 0.1); border: 1px solid rgba(255, 159, 10, 0.3); padding: 16px; border-radius: var(--radius-md);">
                        <p class="kpi-title" style="font-size: 12px; color: var(--text-secondary); margin: 0;"><i class="ph ph-clock-afternoon" style="color: var(--warning);"></i> Total Sisa Tagihan (Piutang)</p>
                        <p class="kpi-value" id="summary-total-outstanding" style="font-size: 20px; font-weight: 700; color: var(--warning); margin: 6px 0 0 0;">Rp 0</p>
                    </div>
                    <div class="kpi-card" style="background: rgba(10, 132, 255, 0.1); border: 1px solid rgba(10, 132, 255, 0.3); padding: 16px; border-radius: var(--radius-md);">
                        <p class="kpi-title" style="font-size: 12px; color: var(--text-secondary); margin: 0;"><i class="ph ph-scroll" style="color: var(--info);"></i> Total Nilai Kontrak Project</p>
                        <p class="kpi-value" id="summary-total-project-value" style="font-size: 20px; font-weight: 700; color: var(--info); margin: 6px 0 0 0;">Rp 0</p>
                    </div>
                    <div class="kpi-card" style="background: rgba(175, 82, 222, 0.1); border: 1px solid rgba(175, 82, 222, 0.3); padding: 16px; border-radius: var(--radius-md);">
                        <p class="kpi-title" style="font-size: 12px; color: var(--text-secondary); margin: 0;"><i class="ph ph-user-plus" style="color: #af52de;"></i> Klien Baru (Bulan Ini)</p>
                        <p class="kpi-value" id="summary-new-clients-count" style="font-size: 20px; font-weight: 700; color: #af52de; margin: 6px 0 0 0;">0 Klien</p>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 20px;">
                    <h3 style="margin-top: 0; margin-bottom: 16px; color: white; font-size: 15px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="ph ph-table"></i> Rekapan Transfer (Tampilan Ringkas)</span>
                        <span style="font-size: 11px; font-weight: 400; color: var(--text-muted);"><i class="ph ph-info"></i> Klik Detail untuk info utuh</span>
                    </h3>
                    <div style="overflow-x: auto;" class="payroll-table-wrapper">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: white;">
                            <thead>
                                <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--glass-border); text-align: left;">
                                    <th style="padding: 10px 8px;">No</th>
                                    <th style="padding: 10px 8px;">Tanggal</th>
                                    <th style="padding: 10px 8px;">Klien</th>
                                    <th style="padding: 10px 8px;">Paket</th>
                                    <th style="padding: 10px 8px; text-align: right;">Pembayaran</th>
                                    <th style="padding: 10px 8px; text-align: right;">Sisa Tagihan</th>
                                    <th style="padding: 10px 8px; text-align: center;">Status</th>
                                    <th style="padding: 10px 8px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="finance-inflow-table-body">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Phase 11: Finance Staff (Payroll) -->
            <section id="view-finance-staff" class="view-section" style="display: none;">
                <div class="section-header" style="margin-bottom: 24px;">
                    <h2 style="font-size: 20px;">Payroll & Expenses</h2>
                    <p style="color: var(--text-secondary); font-size: 13px;">Auto-calculated based on D-Point & KPIs</p>
                </div>
                <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 24px;">
                    <h3 style="margin-bottom: 16px;">Draft Payroll (Bulan Ini)</h3>
                    <div class="payroll-table-wrapper">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Base Salary</th>
                                    <th>KPI Bonus</th>
                                    <th>D-Point (Meal)</th>
                                    <th>Total Payout</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="payroll-table-body">
                                <tr>
                                    <td>M. Maulana (Web Dev)</td>
                                    <td>Rp 5.000.000</td>
                                    <td>Rp 1.000.000 (100%)</td>
                                    <td>Rp 1.000.000 (20 Days)</td>
                                    <td style="font-weight: 700; color: var(--success);">Rp 7.000.000</td>
                                    <td><button class="primary-btn" style="padding: 4px 12px; font-size: 11px;">Generate Slip</button></td>
                                </tr>
                                <tr>
                                    <td>D BEST AR (Content)</td>
                                    <td>Rp 4.500.000</td>
                                    <td>Rp 400.000 (80%)</td>
                                    <td>Rp 950.000 (19 Days)</td>
                                    <td style="font-weight: 700; color: var(--success);">Rp 5.850.000</td>
                                    <td><button class="primary-btn" style="padding: 4px 12px; font-size: 11px;">Generate Slip</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Finance Detail Modal -->
            <div id="inflow-detail-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <div style="padding: 16px 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-sidebar); z-index: 2;">
                        <h3 style="margin: 0; font-size: 16px; color: white; display: flex; align-items: center; gap: 8px;"><i class="ph ph-receipt" style="color: var(--primary);"></i> Detail Transaksi</h3>
                        <button id="btn-close-inflow-detail" style="background: transparent; border: none; color: var(--text-secondary); cursor: pointer; font-size: 20px;"><i class="ph ph-x"></i></button>
                    </div>
                    <div style="padding: 20px; color: white; font-size: 13px;" id="inflow-detail-content">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <?php echo $__env->make('partials.strategic-erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('partials.alumni-network', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </main>

        <!-- Slide-out Internal Chat (Slack-like) -->
        <div class="chat-overlay" id="chat-overlay"></div>
        <div class="chat-panel" id="chat-panel">
            <div class="chat-header" style="align-items: flex-start; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between; width: 100%;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 6px;"><i class="ph ph-hash"></i> Internal Chat <span class="online-dot"></span></h3>
                    <div class="chat-header-actions">
                        <button type="button" id="chat-holiday-announcement-btn" class="chat-announcement-btn" title="Buat pengumuman hari libur"><i class="ph ph-megaphone"></i></button>
                        <button class="close-chat-btn" id="close-chat-btn"><i class="ph ph-x"></i></button>
                    </div>
                </div>
                <select id="chat-channel-selector" style="width: 100%; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--glass-border); padding: 6px 10px; border-radius: 6px; font-size: 13px;">
                    <!-- Options populated by JS based on RBAC -->
                </select>
            </div>
            
            <div class="chat-body" id="chat-body">
                <!-- System Message Integration -->
                <div class="system-msg">
                    <i class="ph ph-bell-ringing"></i> M. Maulana Zakaria has completed KPI: High-Ticket Leads
                </div>
                
                <div class="chat-bubble other">
                    <span class="chat-sender">Maulana Z.</span>
                    <div class="chat-text">Halo pak, leads dari IG ads hari ini sudah saya masukkan ke Kanban ya. Total ada 11 lead potensial.</div>
                </div>
                
                <div class="chat-bubble other">
                    <span class="chat-sender">D BEST AR</span>
                    <div class="chat-text">Siap! Video portofolio desain rumah SCBD juga sudah saya up di TikTok, link evidence sudah saya submit di daily workspace. Mohon di-approve pak.</div>
                </div>
                
                <div class="chat-bubble me">
                    <span class="chat-sender">You (CEO)</span>
                    <div class="chat-text">Great job tim! @Maulana tolong pastikan dari 11 lead itu minimal 2 closing minggu ini ya biar target Omzet 50Jt kita tembus.</div>
                </div>
            </div>
            
            <div id="chat-attachment-preview" class="chat-attachment-preview" style="display: none;"></div>
            <div class="chat-input-area chat-composer">
                <input id="chat-attachment-input" type="file" hidden accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,.zip">
                <button type="button" id="chat-attachment-btn" style="background: rgba(255,255,255,0.1); color: white;" title="Lampirkan file" aria-label="Lampirkan file"><i class="ph ph-paperclip"></i></button>
                <input type="text" placeholder="Tulis pesan... (@AI untuk Gemini)" id="chat-input-field" autocomplete="off" enterkeyhint="send">
                <button id="send-chat-btn" type="button" aria-label="Kirim pesan"><i class="ph ph-paper-plane-right"></i></button>
            </div>
        </div>

        <div id="holiday-announcement-modal" class="modal-overlay premium-dialog-overlay">
            <form id="holiday-announcement-form" class="premium-dialog-card premium-dialog-form">
                <div class="premium-dialog-glow"></div>
                <div class="premium-dialog-icon primary"><i class="ph-fill ph-megaphone"></i></div>
                <h3>Pengumuman Hari Libur</h3>
                <p>Pengumuman akan diterbitkan di kanal umum dan dinotifikasikan kepada seluruh karyawan aktif.</p>
                <label for="holiday-announcement-title">Judul pengumuman</label>
                <input id="holiday-announcement-title" type="text" maxlength="180" placeholder="Contoh: Libur Nasional Hari Kemerdekaan" required>
                <div class="premium-dialog-grid">
                    <div>
                        <label for="holiday-announcement-start">Tanggal mulai</label>
                        <input id="holiday-announcement-start" type="date" required>
                    </div>
                    <div>
                        <label for="holiday-announcement-end">Tanggal selesai</label>
                        <input id="holiday-announcement-end" type="date" required>
                    </div>
                </div>
                <label for="holiday-announcement-message">Keterangan</label>
                <textarea id="holiday-announcement-message" rows="4" maxlength="5000" placeholder="Sampaikan ketentuan hari libur dan jadwal masuk kembali..." required></textarea>
                <div id="holiday-announcement-error" class="premium-dialog-error"></div>
                <div class="premium-dialog-actions">
                    <button type="button" id="holiday-announcement-cancel" class="premium-dialog-secondary">Batal</button>
                    <button type="submit" class="primary-btn premium-dialog-primary"><i class="ph ph-megaphone"></i> Terbitkan</button>
                </div>
            </form>
        </div>

        <div id="backup-data-modal" class="modal-overlay premium-dialog-overlay">
            <div class="premium-dialog-card backup-dialog-card">
                <div class="premium-dialog-glow"></div>
                <div class="premium-dialog-icon success"><i class="ph-fill ph-database"></i></div>
                <h3>Backup Data Dashboard</h3>
                <p>Backup hanya mencakup data yang memang dapat diakses oleh akun Anda sesuai jabatan dan divisi.</p>
                <div id="backup-data-summary" class="backup-data-summary">
                    <i class="ph ph-shield-check"></i>
                    <span>Data siap disalin atau diunduh dalam format JSON.</span>
                </div>
                <div id="backup-data-error" class="premium-dialog-error"></div>
                <div class="premium-dialog-actions backup-dialog-actions">
                    <button type="button" id="backup-data-close" class="premium-dialog-secondary">Tutup</button>
                    <button type="button" id="backup-data-copy" class="premium-dialog-secondary"><i class="ph ph-copy"></i> Salin Data</button>
                    <button type="button" id="backup-data-download" class="primary-btn premium-dialog-primary"><i class="ph ph-download-simple"></i> Unduh JSON</button>
                </div>
            </div>
        </div>

        <!-- Floating AI Copilot (Phase 8) -->
        <button type="button" class="ai-floating-btn" id="ai-floating-btn" aria-label="Buka Gemini Copilot" title="Gemini Copilot">
            <i class="ph-fill ph-sparkle"></i>
        </button>
        
        <div class="ai-panel" id="ai-panel">
            <div class="ai-panel-header">
                <div class="ai-avatar"><i class="ph-fill ph-sparkle"></i></div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 600;">Suba-Arch Copilot</h4>
                    <p id="ai-copilot-status" class="ai-copilot-status checking"><span></span> Memeriksa koneksi Gemini...</p>
                </div>
                <button class="icon-btn" id="ai-copilot-settings-btn" title="Atur API Gemini pribadi" aria-label="Atur API Gemini pribadi" style="margin-left: auto; font-size: 18px;"><i class="ph ph-key"></i></button>
                <button class="icon-btn" id="close-ai-btn" style="font-size: 18px;"><i class="ph ph-x"></i></button>
            </div>
            <div class="chat-body" id="ai-copilot-body" style="flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                <div class="chat-bubble other">
                    <div class="chat-text" style="background: rgba(255,255,255,0.05); font-size: 13px;">
                        Halo, saya Suba-Arch Copilot. Saya dapat membantu menganalisis KPI, task, kehadiran, goal, dan pengajuan berdasarkan data yang memang dapat Anda akses.
                    </div>
                </div>
            </div>
            <div class="chat-input-area" style="padding: 12px; border-top: 1px solid var(--glass-border);">
                <input id="ai-copilot-input" type="text" maxlength="3000" placeholder="Tanya Gemini tentang dashboard..." style="padding: 8px 12px; font-size: 13px;">
                <button id="ai-copilot-send" type="button" aria-label="Kirim pertanyaan ke Gemini" style="width: 36px; height: 36px; font-size: 16px;"><i class="ph ph-paper-plane-tilt"></i></button>
            </div>
        </div>
    </div>
    <!-- Phase 17: User Registration Modal -->
    <div id="user-registration-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 400px; max-width: 90%; max-height: calc(100vh - 32px); overflow-y: auto; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
            <h2 id="modal-title" style="margin-bottom: 24px;">Add New User</h2>
            <form id="user-registration-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" id="reg-name" class="form-control" required placeholder="Contoh: Budi Santoso" maxlength="255" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%;">
                </div>
                <div class="form-group">
                    <label>Username Login Otomatis</label>
                    <input type="text" id="reg-username" class="form-control" readonly placeholder="Akan dibuat otomatis" style="background: rgba(255,255,255,0.025); border: 1px solid var(--glass-border); color: var(--text-secondary); padding: 10px; border-radius: 6px; width: 100%;">
                    <small style="color: var(--text-muted);">Format: perusahaan.divisi.level.nama.nomor. Username merekam posisi awal dan tetap agar jejak audit tidak rusak.</small>
                </div>
                <div class="form-group">
                    <label>Kode Pegawai Otomatis</label>
                    <input type="text" id="reg-employee-code" class="form-control" readonly placeholder="Akan dibuat otomatis" style="background: rgba(255,255,255,0.025); border: 1px solid var(--glass-border); color: var(--primary); padding: 10px; border-radius: 6px; width: 100%; font-weight: 700;">
                    <small style="color: var(--text-muted);">Kode menunjukkan perusahaan, divisi, level, dan nomor urut akun.</small>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="reg-email" class="form-control" required placeholder="e.g. budi@suba-arch.co.id" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%;">
                </div>
                <div class="form-group">
                    <label>Nama Jabatan Kustom</label>
                    <input type="text" id="reg-job-title" class="form-control" required placeholder="Contoh: Content Creator" maxlength="120" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%;">
                    <small style="color: var(--text-muted);">Level dan divisi tetap ditentukan sistem; nama jabatan dapat disesuaikan.</small>
                </div>
                <div class="gemini-security-note" style="margin: 0;">
                    <i class="ph ph-envelope-simple"></i>
                    Akun akan masuk menggunakan OTP yang dikirim ke email di atas. Sistem tidak membuat password default.
                </div>
                <div class="form-group" id="reg-employment-type-container">
                    <label>Tipe Pekerjaan / Kontrak</label>
                    <select id="reg-employment-type" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%;">
                        <option value="Full-Time">Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Paid Internship">Paid Internship</option>
                        <option value="Unpaid Internship">Unpaid Internship</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="reg-role" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%;">
                        <!-- Options injected by JS based on permissions -->
                    </select>
                </div>
                <div id="reg-error" style="color: var(--danger); font-size: 13px; display: none;">Error creating user.</div>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="button" id="btn-cancel-reg" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer;">Cancel</button>
                    <button type="submit" id="btn-submit-reg" class="primary-btn" style="flex: 1; justify-content: center;">Simpan / Ajukan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile Toggle -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Org Node Detail Modal -->
    <div id="node-detail-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 500px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 id="modal-employee-name" style="margin: 0; font-size: 18px;">Daftar Tugas Karyawan</h3>
                <button class="icon-btn" id="close-node-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <div id="modal-employee-role" style="color: var(--text-secondary); font-size: 14px; margin-top: -16px; margin-bottom: 20px;">Role</div>
            <div id="modal-tasks-list" style="display: flex; flex-direction: column; gap: 12px; max-height: 300px; overflow-y: auto; padding-right: 4px;">
                <!-- Dynamic task list item -->
            </div>
        </div>
    </div>

    <!-- Salary Slip Modal -->
    <div id="salary-slip-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 450px; max-width: 90%; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <div>
                    <h3 style="margin: 0; font-size: 20px; color: var(--primary);">SUBA ARCH</h3>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">Slip Gaji Karyawan</p>
                </div>
                <button class="icon-btn" id="close-slip-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <div id="slip-content" style="display: flex; flex-direction: column; gap: 16px; font-size: 14px;">
                <!-- Filled dynamically by JS -->
            </div>
            <div style="margin-top: 24px; border-top: 1px dashed var(--border); padding-top: 16px; display: flex; justify-content: flex-end;">
                <button class="primary-btn" id="print-slip-btn" style="background: var(--success);"><i class="ph ph-printer"></i> Print Slip</button>
            </div>
        </div>
    </div>

    <!-- Paklaring Generator Modal (HRD & CEO) -->
    <div id="paklaring-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: #ffffff; color: #020617; border-radius: var(--radius-lg); width: 650px; max-width: 95%; padding: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.7); font-family: 'Times New Roman', Times, serif; line-height: 1.6; border: 4px double #020617;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #020617; padding-bottom: 12px; margin-bottom: 20px;">
                <div style="text-align: left;">
                    <h2 style="margin: 0; font-size: 24px; font-weight: 800; font-family: sans-serif; letter-spacing: 2px; color: #020617;">PT SUBA ARCHITECTURE</h2>
                    <p style="margin: 2px 0 0 0; font-size: 11px; font-family: sans-serif; color: #475569;">Architecture, Interior Design & Master Planning Enterprise</p>
                </div>
                <button class="icon-btn" id="close-paklaring-modal-btn" style="color: #020617; font-size: 20px; background: transparent; border: none; cursor: pointer;"><i class="ph ph-x"></i></button>
            </div>
            
            <div style="text-align: center; margin-bottom: 24px;">
                <h3 style="margin: 0; font-size: 18px; text-decoration: underline; letter-spacing: 1px; font-weight: bold; text-transform: uppercase;">SURAT KETERANGAN KERJA</h3>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #334155;" id="paklaring-ref-no">No: SUBA-ARCH/HRD/PKL/2026/001</p>
            </div>

            <div id="paklaring-content" style="font-size: 14px; text-align: justify; display: flex; flex-direction: column; gap: 14px;">
                <!-- Filled dynamically by JS -->
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; font-family: sans-serif; font-size: 13px;">
                <div style="text-align: center; width: 200px;">
                    <p style="margin-bottom: 60px;">Mengetahui,<br><b>HRD Manager</b></p>
                    <p style="font-weight: 700; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px;">Sonia, S.Psikologi</p>
                </div>
                <div style="text-align: center; width: 200px;">
                    <p style="margin-bottom: 60px;">Jakarta, <span id="paklaring-date-str">21 Juli 2026</span><br><b>Chief Executive Officer</b></p>
                    <p style="font-weight: 700; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px;">CEO Suba-Arch</p>
                </div>
            </div>

            <div style="margin-top: 24px; border-top: 1px dashed #cbd5e1; padding-top: 16px; display: flex; justify-content: flex-end; font-family: sans-serif;">
                <button class="primary-btn" id="print-paklaring-btn" style="background: #020617; color: #ffffff;"><i class="ph ph-printer"></i> Print / Download Paklaring</button>
            </div>
        </div>
    </div>

    <!-- Edit Staff Profile Modal (Manager & CEO) -->
    <div id="staff-edit-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 450px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; color: white;" id="staff-edit-modal-title">Edit Profil Staf Tim</h3>
                <button class="icon-btn" id="close-staff-edit-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <form id="staff-edit-form" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" id="staff-edit-username-hidden">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Username / NIK</label>
                    <input type="text" id="staff-edit-username-display" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--text-muted); padding: 10px; border-radius: 6px; width: 100%; outline: none;" readonly>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Nama Lengkap</label>
                    <input type="text" id="staff-edit-name-input" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;" required>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Email Staf (Untuk Penerimaan OTP)</label>
                    <input type="email" id="staff-edit-email-input" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;" required>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Nama Jabatan Kustom</label>
                    <input type="text" id="staff-edit-job-title" placeholder="Contoh: Content Creator" maxlength="120" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;">
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Password Baru (Kosongkan jika tidak diubah)</label>
                    <div style="padding: 10px 12px; border: 1px solid rgba(242,201,76,0.2); border-radius: 8px; color: var(--text-secondary); font-size: 11px; line-height: 1.5;">
                        Login akun menggunakan OTP email. Perubahan email akan menentukan alamat tujuan OTP berikutnya.
                    </div>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Tipe Pekerjaan / Kontrak</label>
                    <select id="staff-edit-employment-type" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;">
                        <option value="Full-Time">Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Paid Internship">Paid Internship</option>
                        <option value="Unpaid Internship">Unpaid Internship</option>
                    </select>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="button" id="btn-cancel-staff-edit" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer; font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1; justify-content: center; font-family: inherit;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="profile-edit-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 450px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; color: white;">Edit Profil Saya</h3>
                <button class="icon-btn" id="close-profile-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <form id="profile-edit-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div id="profile-modal-preview" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--info)); display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; color: white; overflow: hidden; border: 2px solid var(--glass-border);">
                        CEO
                    </div>
                    <label class="primary-btn" style="padding: 6px 16px; font-size: 13px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--glass-border); box-shadow: none; cursor: pointer; justify-content: center;">
                        <i class="ph ph-upload-simple"></i> Unggah Foto
                        <input type="file" id="profile-photo-input" accept="image/*" style="display: none;">
                    </label>
                    <span id="photo-size-info" style="font-size: 11px; color: var(--text-muted); text-align: center;">Maks. 300KB (akan dikompres otomatis jika lebih)</span>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; color: var(--text-secondary);">Nama Lengkap</label>
                    <input type="text" id="profile-name-input" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;" required>
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; color: var(--text-secondary);">Bio Singkat</label>
                    <textarea id="profile-bio-input" rows="3" placeholder="Tulis bio singkat mengenai diri Anda..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none; resize: none;"></textarea>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 12px;">
                    <button type="button" id="btn-cancel-profile" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1; justify-content: center;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Reusable Confirm Modal -->
    <div id="confirm-modal" class="modal-overlay premium-dialog-overlay">
        <div class="premium-dialog-card">
            <div class="premium-dialog-glow"></div>
            <div id="confirm-modal-icon" class="premium-dialog-icon"><i class="ph-fill ph-warning-circle"></i></div>
            <h3 id="confirm-modal-title" style="margin-bottom: 12px; font-size: 20px; color: white; font-weight: 600;">Konfirmasi</h3>
            <p id="confirm-modal-message" style="color: var(--text-secondary); font-size: 14px; margin-bottom: 24px; line-height: 1.6;">Apakah Anda yakin ingin keluar dari sesi ini?</p>
            <div class="premium-dialog-actions">
                <button id="btn-confirm-cancel" class="premium-dialog-secondary">Batal</button>
                <button id="btn-confirm-ok" class="primary-btn premium-dialog-primary">Ya</button>
            </div>
        </div>
    </div>

        <div id="gemini-settings-modal" class="modal-overlay premium-dialog-overlay">
            <form id="gemini-settings-form" class="premium-dialog-card premium-dialog-form gemini-settings-card">
                <div class="premium-dialog-glow"></div>
                <div class="premium-dialog-icon primary"><i class="ph-fill ph-key"></i></div>
                <h3>Hubungkan Gemini Pribadi</h3>
                <p>Setiap akun menggunakan API key miliknya sendiri. Pemakaian dan kuota Gemini tidak dibagikan dengan akun lain.</p>

                <div class="gemini-setup-guide">
                    <div class="gemini-guide-step"><span>1</span><div><b>Buka Google AI Studio</b><p>Masuk menggunakan akun Google Anda.</p></div></div>
                    <div class="gemini-guide-step"><span>2</span><div><b>Buat Auth API Key</b><p>Pilih <i>Create API key</i>, lalu batasi khusus untuk Gemini API.</p></div></div>
                    <div class="gemini-guide-step"><span>3</span><div><b>Salin dan tempel di bawah</b><p>Sistem akan menguji key sebelum menyimpannya secara terenkripsi.</p></div></div>
                    <a class="gemini-studio-link" href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">
                        <i class="ph ph-arrow-square-out"></i> Buka Halaman API Key Google AI Studio
                    </a>
                </div>

                <label for="gemini-settings-api-key">API key pribadi</label>
                <div class="gemini-key-input-wrap">
                    <input id="gemini-settings-api-key" type="password" autocomplete="new-password" maxlength="500" placeholder="Tempel API key Gemini Anda..." required>
                    <button id="gemini-key-visibility" type="button" title="Tampilkan atau sembunyikan API key"><i class="ph ph-eye"></i></button>
                </div>
                <small class="gemini-security-note"><i class="ph ph-shield-check"></i> Key dienkripsi di server, tidak ditampilkan kembali, dan tidak disertakan dalam backup.</small>

                <label for="gemini-settings-model">Model Gemini</label>
                <select id="gemini-settings-model" required>
                    <option value="gemini-2.5-flash">Gemini 2.5 Flash — kualitas dan kecepatan seimbang</option>
                    <option value="gemini-2.5-flash-lite">Gemini 2.5 Flash-Lite — lebih hemat untuk pemakaian rutin</option>
                </select>

                <div id="gemini-settings-error" class="premium-dialog-error"></div>
                <div class="gemini-settings-current" id="gemini-settings-current" style="display: none;"></div>
                <div class="premium-dialog-actions gemini-settings-actions">
                    <button type="button" id="gemini-settings-remove" class="premium-dialog-secondary gemini-remove-btn" style="display: none;"><i class="ph ph-trash"></i> Hapus Koneksi</button>
                    <button type="button" id="gemini-settings-cancel" class="premium-dialog-secondary">Batal</button>
                    <button type="submit" id="gemini-settings-save" class="primary-btn premium-dialog-primary"><i class="ph ph-plugs-connected"></i> Uji & Simpan</button>
                </div>
            </form>
        </div>

    <!-- Reusable styled text input dialog -->
    <div id="input-dialog-modal" class="modal-overlay premium-dialog-overlay">
        <form id="input-dialog-form" class="premium-dialog-card premium-dialog-form">
            <div class="premium-dialog-glow"></div>
            <div class="premium-dialog-icon primary"><i class="ph-fill ph-note-pencil"></i></div>
            <h3 id="input-dialog-title">Tambahkan Catatan</h3>
            <p id="input-dialog-description">Tuliskan catatan yang akan disimpan bersama keputusan ini.</p>
            <label id="input-dialog-label" for="input-dialog-value">Catatan</label>
            <textarea id="input-dialog-value" rows="4" maxlength="1000" required></textarea>
            <div class="premium-dialog-actions">
                <button type="button" id="btn-input-dialog-cancel" class="premium-dialog-secondary">Batal</button>
                <button type="submit" id="btn-input-dialog-submit" class="primary-btn premium-dialog-primary">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Structured employee separation dialog -->
    <div id="staff-separation-modal" class="modal-overlay premium-dialog-overlay">
        <form id="staff-separation-form" class="premium-dialog-card premium-dialog-form staff-separation-card">
            <div class="premium-dialog-glow"></div>
            <div class="premium-dialog-icon danger"><i class="ph-fill ph-user-minus"></i></div>
            <h3 id="staff-separation-title">Catat Status Keluar Anggota</h3>
            <p id="staff-separation-description">Lengkapi status serah terima dan alasan keluar. Catatan ini disimpan untuk audit HRD.</p>
            <div class="premium-dialog-grid">
                <div>
                    <label for="staff-separation-completion">Status pekerjaan</label>
                    <select id="staff-separation-completion" required>
                        <option value="completed">Selesai dan serah terima tuntas</option>
                        <option value="incomplete">Belum selesai / perlu tindak lanjut</option>
                    </select>
                </div>
                <div>
                    <label for="staff-separation-reason">Alasan keluar</label>
                    <select id="staff-separation-reason" required>
                        <option value="completed">Masa kerja / kontrak selesai</option>
                        <option value="resigned">Mengundurkan diri</option>
                        <option value="terminated">Diberhentikan perusahaan</option>
                        <option value="other">Alasan lain</option>
                    </select>
                </div>
            </div>
            <label for="staff-separation-effective-date">Tanggal efektif</label>
            <input id="staff-separation-effective-date" type="date" required>
            <label for="staff-separation-notes">Catatan serah terima / tindak lanjut</label>
            <textarea id="staff-separation-notes" rows="4" maxlength="2000" placeholder="Contoh: Seluruh file proyek telah dipindahkan ke folder tim dan diterima oleh atasan."></textarea>
            <label class="staff-alumni-option" for="staff-separation-alumni">
                <input id="staff-separation-alumni" type="checkbox">
                <span>
                    <strong>Alihkan menjadi akun alumni</strong>
                    <small>Hanya tersedia jika status “Selesai”. Akun keluar dari hirarki dan attendance, tetapi tetap login menggunakan OTP email yang sama.</small>
                </span>
            </label>
            <div class="gemini-security-note" style="margin: 0;">
                <i class="ph ph-shield-check"></i>
                Data akun tidak dihapus permanen. Akun dinonaktifkan, dikeluarkan dari hirarki serta attendance aktif, dan riwayatnya tetap tersimpan sesuai kebijakan retensi.
            </div>
            <div id="staff-separation-error" class="premium-dialog-error"></div>
            <div class="premium-dialog-actions">
                <button type="button" id="staff-separation-cancel" class="premium-dialog-secondary">Batal</button>
                <button type="submit" id="staff-separation-submit" class="primary-btn premium-dialog-primary"><i class="ph ph-paper-plane-tilt"></i> Lanjutkan</button>
            </div>
        </form>
    </div>

    <!-- KPI rule dialog -->
    <div id="rule-dialog-modal" class="modal-overlay premium-dialog-overlay">
        <form id="rule-dialog-form" class="premium-dialog-card premium-dialog-form">
            <div class="premium-dialog-glow"></div>
            <div class="premium-dialog-icon primary"><i class="ph-fill ph-scales"></i></div>
            <h3>Tambah Aturan KPI</h3>
            <p>Aturan akan berlaku sesuai divisi yang dipilih dan langsung tersinkron ke akun berwenang.</p>
            <label for="rule-dialog-condition">Kondisi</label>
            <input id="rule-dialog-condition" type="text" maxlength="255" placeholder="Contoh: Score ≥ 80%" required>
            <label for="rule-dialog-reward">Reward / Konsekuensi</label>
            <input id="rule-dialog-reward" type="text" maxlength="255" placeholder="Contoh: Bonus 1.0%" required>
            <div class="premium-dialog-grid">
                <div>
                    <label for="rule-dialog-type">Tipe</label>
                    <select id="rule-dialog-type" required>
                        <option value="success">Pencapaian</option>
                        <option value="warning">Peringatan</option>
                        <option value="danger">Konsekuensi</option>
                    </select>
                </div>
                <div id="rule-dialog-division-group">
                    <label for="rule-dialog-division">Cakupan Divisi</label>
                    <select id="rule-dialog-division">
                        <option value="">Seluruh perusahaan</option>
                        <option value="marketing">Marketing</option>
                        <option value="operasional">Operasional</option>
                        <option value="finance">Finance</option>
                        <option value="hrd">HRD</option>
                    </select>
                </div>
            </div>
            <div id="rule-dialog-error" class="premium-dialog-error"></div>
            <div class="premium-dialog-actions">
                <button type="button" id="btn-rule-dialog-cancel" class="premium-dialog-secondary">Batal</button>
                <button type="submit" class="primary-btn premium-dialog-primary"><i class="ph ph-plus"></i> Tambahkan</button>
            </div>
        </form>
    </div>

    <!-- Kanban Card Detail Modal -->
    <div id="lead-detail-modal" class="modal-overlay crm-detail-overlay" style="display: none;">
        <div class="crm-detail-dialog">
            <div class="crm-detail-head">
                <div>
                    <span class="crm-eyebrow">Customer timeline</span>
                    <h3>Detail Lead</h3>
                </div>
                <button class="icon-btn" id="close-lead-detail-btn"><i class="ph ph-x"></i></button>
            </div>

            <div class="crm-detail-layout">
                <div class="crm-detail-main">
                    <div class="crm-client-title">
                        <div>
                            <span>Klien / proyek</span>
                            <strong id="detail-lead-name">-</strong>
                        </div>
                        <a id="detail-lead-whatsapp" class="crm-whatsapp-btn" href="#" target="_blank" rel="noopener" style="display:none;">
                            <i class="ph-fill ph-whatsapp-logo"></i> Buka WhatsApp
                        </a>
                    </div>

                    <div class="crm-detail-grid">
                        <div><span>Estimasi deal</span><strong id="detail-lead-budget">Rp 0</strong></div>
                        <div><span>Omzet aktual</span><strong id="detail-lead-revenue">Rp 0</strong></div>
                        <div><span>Nomor WhatsApp</span><strong id="detail-lead-phone">-</strong></div>
                        <div><span>Tanggal masuk</span><strong id="detail-lead-date">-</strong></div>
                        <div><span>Sumber</span><strong id="detail-lead-source">-</strong></div>
                        <div><span>Kampanye</span><strong id="detail-lead-campaign">Organik</strong></div>
                        <div><span>Tipe proyek</span><strong id="detail-lead-type">-</strong></div>
                        <div><span>Sales PIC</span><strong id="detail-lead-assignee">-</strong></div>
                        <div><span>Status</span><strong id="detail-lead-column">-</strong></div>
                        <div><span>Follow-up berikutnya</span><strong id="detail-lead-follow-up">Belum dijadwalkan</strong></div>
                    </div>

                    <div class="crm-notes">
                        <span>Catatan kebutuhan</span>
                        <p id="detail-lead-notes">Belum ada catatan.</p>
                    </div>

                    <form id="lead-activity-form" class="crm-activity-form">
                        <div class="crm-form-row">
                            <label>Kanal
                                <select name="channel">
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="phone">Telepon</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="email">Email</option>
                                    <option value="internal">Catatan internal</option>
                                </select>
                            </label>
                            <label>Arah
                                <select name="direction">
                                    <option value="outbound">Keluar / balasan sales</option>
                                    <option value="inbound">Masuk dari calon klien</option>
                                    <option value="internal">Internal</option>
                                </select>
                            </label>
                            <label>Follow-up berikutnya
                                <input name="next_follow_up_at" type="datetime-local">
                            </label>
                        </div>
                        <label>Catat aktivitas
                            <textarea name="body" rows="3" maxlength="5000" placeholder="Ringkasan percakapan, kebutuhan klien, atau hasil follow-up..." required></textarea>
                        </label>
                        <div class="crm-form-actions">
                            <small id="crm-whatsapp-send-hint">Riwayat tersimpan pada lead dan dapat dilihat tim terkait.</small>
                            <div class="crm-form-buttons">
                                <button type="submit" class="crm-secondary-btn"><i class="ph ph-note-pencil"></i> Simpan Aktivitas</button>
                                <button type="submit" id="crm-send-whatsapp-api" data-whatsapp-send="true" class="primary-btn" hidden>
                                    <i class="ph-fill ph-whatsapp-logo"></i> Kirim via API
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="crm-timeline-panel">
                    <div class="crm-timeline-head">
                        <div>
                            <span class="crm-eyebrow">Activity log</span>
                            <h4>Riwayat Interaksi</h4>
                        </div>
                        <span id="detail-lead-activity-count" class="count">0</span>
                    </div>
                    <div id="lead-activity-list" class="crm-timeline">
                        <div class="crm-timeline-empty">Memuat riwayat...</div>
                    </div>
                </aside>
            </div>

            <div class="crm-detail-footer">
                <span id="detail-lead-sync-state"><i class="ph ph-check-circle"></i> Tersinkron dengan CRM</span>
                <button class="primary-btn" id="btn-close-lead-detail">Tutup</button>
            </div>
        </div>
    </div>

    <!-- CEO Division Detail Modal -->
    <div id="ceo-division-detail-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 600px; max-width: 95%; padding: 28px; box-shadow: 0 20px 45px rgba(0,0,0,0.6); display: flex; flex-direction: column; gap: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 14px;">
                <h3 id="ceo-div-modal-title" style="margin: 0; font-size: 20px; color: var(--primary); display: flex; align-items: center; gap: 8px;"><i class="ph ph-shield-star"></i> Detail Divisi</h3>
                <button class="icon-btn" id="close-ceo-div-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            
            <!-- Target KPIs List -->
            <div>
                <h4 style="margin: 0 0 10px 0; font-size: 14px; color: var(--warning);"><i class="ph ph-target"></i> Target KPI Divisi</h4>
                <div id="ceo-div-kpis-list" style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto;">
                    <!-- Dynamically populated -->
                </div>
            </div>
            
            <!-- Performance Graph of team members in that division -->
            <div>
                <h4 style="margin: 0 0 10px 0; font-size: 14px; color: var(--success);"><i class="ph ph-chart-bar-horizontal"></i> Grafik Performa Tim Divisi</h4>
                <div style="height: 160px; position: relative;">
                    <canvas id="ceoDivisionTeamPerformanceChart"></canvas>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                <button class="primary-btn" id="btn-close-ceo-div-modal" style="padding: 10px 24px; font-family: inherit;">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Create Division Modal -->
    <div id="division-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 400px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--primary);"><i class="ph ph-shield-star"></i> Create New Division</h3>
                <button class="icon-btn" id="close-div-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <form id="division-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; color: var(--text-secondary);">Nama Divisi Baru</label>
                    <input type="text" id="div-name-input" placeholder="Contoh: Customer Service, Legal" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; width: 100%; outline: none;" required>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="button" id="btn-cancel-div" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer; font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1; justify-content: center;">Buat Divisi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leave Request Modal -->
    <div id="leave-request-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 450px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--primary);"><i class="ph ph-calendar-plus"></i> Request Leave</h3>
                <button class="icon-btn" id="close-leave-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <form id="leave-request-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Jenis Cuti / Izin</label>
                    <select id="leave-type-select" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                        <option value="Sakit (Sick Leave)">Sakit (Sick Leave)</option>
                        <option value="Cuti Tahunan (Annual Leave)">Cuti Tahunan (Annual Leave)</option>
                        <option value="Izin Khusus (Special Leave)">Izin Khusus (Special Permission)</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 12px; color: var(--text-secondary);">Tanggal Mulai</label>
                        <input type="date" id="leave-start-date" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit;" required>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-size: 12px; color: var(--text-secondary);">Tanggal Selesai</label>
                        <input type="date" id="leave-end-date" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit;" required>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary);">Alasan Pengajuan</label>
                    <textarea id="leave-reason" rows="3" placeholder="Tulis alasan detail pengajuan cuti..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; resize: none; font-family: inherit;" required></textarea>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="button" id="btn-cancel-leave" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer; font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1; justify-content: center; font-family: inherit; background: var(--primary); color: black;">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Appoint Manager Modal -->
    <div id="appoint-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 400px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--primary);"><i class="ph ph-crown"></i> Appoint New Manager</h3>
                <button class="icon-btn" id="close-appoint-modal-btn"><i class="ph ph-x"></i></button>
            </div>
            <form id="appoint-form" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 13px; color: var(--text-secondary);">Pilih Anggota Staf</label>
                    <select id="appoint-staff-select" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit;">
                        <!-- Populate dynamically in app.js -->
                    </select>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <button type="button" id="btn-cancel-appoint" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer; font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1; justify-content: center;">Tunjuk Manager</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Phase 13: Firebase SDK Configuration -->
    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.0/firebase-app.js";
        import { getFirestore, collection, getDocs, getDoc, doc, setDoc, updateDoc, onSnapshot } from "https://www.gstatic.com/firebasejs/11.0.0/firebase-firestore.js";

        // TODO: Replace the following with your app's Firebase project configuration
        // See: https://firebase.google.com/docs/web/learn-more#config-object
        const firebaseConfig = {
            apiKey: "AIzaSyAvQk8Bp4NgspeC3JN4_S66qOp5nf8hozk",
            authDomain: "suba-arch-erp.firebaseapp.com",
            projectId: "suba-arch-erp",
            storageBucket: "suba-arch-erp.firebasestorage.app",
            messagingSenderId: "620533193308",
            appId: "1:620533193308:web:07e22fb9268f21f03b2499",
            measurementId: "G-R221GJHLNC"
        };

        // Initialize Firebase ONLY if config is provided
        window.isFirebaseConfigured = firebaseConfig.apiKey !== "PASTE_API_KEY_HERE";
        
        if (window.isFirebaseConfigured) {
            console.log("ðŸ”¥ Firebase is Initializing...");
            const app = initializeApp(firebaseConfig);
            window.db = getFirestore(app);
            // Expose Firestore methods for app.js
            window.fs = { collection, getDocs, getDoc, doc, setDoc, updateDoc, onSnapshot };
            console.log("ðŸ”¥ Firebase Firestore Connected!");
        } else {
            console.warn("âš ï¸ Firebase API Keys are missing. Using local Mock Database.");
        }
    </script>

    <!-- Attendance Confirmation Modal -->
    <div id="attendance-confirm-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 450px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); text-align: center;">
            <div style="background: rgba(52, 199, 89, 0.1); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 1px solid rgba(52, 199, 89, 0.2);" id="att-confirm-icon-container">
                <i class="ph-fill ph-map-pin" style="font-size: 32px; color: var(--primary);" id="att-confirm-icon"></i>
            </div>
            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 8px; color: white;" id="att-confirm-title">Konfirmasi Kehadiran</h2>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 24px;" id="att-confirm-subtitle">Deteksi lokasi GPS untuk absensi masuk.</p>
            
            <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); border-radius: 8px; padding: 16px; margin-bottom: 24px; text-align: left; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; gap: 8px; align-items: flex-start; font-size: 13px;">
                    <i class="ph ph-navigation-arrow" style="color: var(--primary); font-size: 16px; margin-top: 2px;"></i>
                    <div>
                        <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: bold;">Koordinat GPS</div>
                        <div style="font-weight: 500; font-family: monospace; color: white;" id="att-confirm-coords"><i class="ph ph-spinner ph-spin"></i> Mendeteksi GPS...</div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: flex-start; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                    <i class="ph ph-map-trifold" style="color: var(--warning); font-size: 16px; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: bold;">Alamat Terdeteksi</div>
                        <div style="font-weight: 500; color: white; line-height: 1.4;" id="att-confirm-address"><i class="ph ph-spinner ph-spin"></i> Mencari alamat...</div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button id="btn-att-confirm-cancel" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: white; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 500;">Batal</button>
                <button id="btn-att-confirm-ok" class="primary-btn" style="flex: 1; justify-content: center; font-size: 13px; font-weight: 600; padding: 12px; border-radius: 8px;">Ya, Absen Sekarang</button>
            </div>
        </div>
    </div>

    <!-- Quick Create Task Modal (All Roles & Manager Team Assign) -->
    <div id="quick-create-task-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2500; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 480px; max-width: 90%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); font-family: inherit; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">
                <h3 style="margin: 0; color: white; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-plus-circle" style="color: var(--primary); font-size: 20px;"></i>
                    <span>Buat Task Baru</span>
                </h3>
                <button type="button" class="icon-btn" id="btn-close-quick-task-modal"><i class="ph ph-x"></i></button>
            </div>
            <form id="quick-create-task-form" style="display: flex; flex-direction: column; gap: 14px;">
                <div id="quick-task-assignee-container" style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Ditujukan Kepada (Staf Tim / Diri Sendiri)</label>
                    <select id="quick-task-assignee-select" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit; font-size: 13px;">
                        <!-- Populated dynamically -->
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Nama / Judul Pekerjaan (Task)</label>
                    <input type="text" id="quick-task-title-input" placeholder="Contoh: Optimasi SEO Landing Page / Survey Site..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">KPI Target <span style="color: var(--text-muted);">(opsional)</span></label>
                    <select id="quick-task-kpi-select" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px; color: white; outline: none; font-family: inherit; font-size: 13px;">
                        <!-- Populated dynamically -->
                    </select>
                    <span style="font-size: 10px; color: var(--text-muted);">Pilih “Task mandiri” jika pekerjaan belum terkait KPI yang disahkan.</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Batas Waktu (Deadline)</label>
                    <input type="date" id="quick-task-deadline-input" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">Lampiran Task / Laporan <span style="color: var(--text-muted);">(opsional)</span></label>
                    <input type="file" id="quick-task-attachment-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,.zip">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" class="primary-btn" id="btn-cancel-quick-task" style="flex: 1; justify-content: center; background: rgba(255,255,255,0.05); color: white; border: 1px solid var(--glass-border); font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1.5; justify-content: center; background: var(--primary); color: #020617; font-weight: 700; font-family: inherit;"><i class="ph ph-check"></i> Simpan Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Income Entry Modal (Data Transfer Klien - Finance Manager) -->
    <div id="inflow-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2600; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 620px; max-width: 95%; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); font-family: inherit; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">
                <h3 id="inflow-modal-title" style="margin: 0; color: white; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-bank" style="color: var(--primary);"></i> Input Pemasukan Klien Baru
                </h3>
                <button type="button" id="btn-close-inflow-modal" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 18px;"><i class="ph ph-x"></i></button>
            </div>
            <form id="inflow-form" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" id="inflow-id" value="">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Tanggal Transfer *</label>
                        <input type="date" id="inflow-date" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">No. Klien</label>
                        <input type="text" id="inflow-client-no" placeholder="Contoh: 600" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Nama Klien *</label>
                        <input type="text" id="inflow-client-name" required placeholder="Contoh: Pak Abdul" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Domisili / Kota</label>
                        <input type="text" id="inflow-domicile" placeholder="Contoh: Sukabumi / Bogor" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Start Project</label>
                        <select id="inflow-start-project" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                            <option value="Jan">Januari</option>
                            <option value="Feb">Februari</option>
                            <option value="Mar">Maret</option>
                            <option value="Apr">April</option>
                            <option value="Mei">Mei</option>
                            <option value="Jun">Juni</option>
                            <option value="Jul">Juli</option>
                            <option value="Ags">Agustus</option>
                            <option value="Sep">September</option>
                            <option value="Okt">Oktober</option>
                            <option value="Nov">November</option>
                            <option value="Des">Desember</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Paket *</label>
                        <select id="inflow-package" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                            <option value="Survey">Survei</option>
                            <option value="Bronze">Bronze</option>
                            <option value="Silver">Silver</option>
                            <option value="Gold">Gold</option>
                            <option value="Diamond">Diamond</option>
                            <option value="Custom">Custom / Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">PJ Survey</label>
                        <input type="text" id="inflow-pj-survey" placeholder="PJ Survey (e.g. LANI)" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Nilai Total Project (Rp) *</label>
                        <input type="number" id="inflow-project-value" required min="0" placeholder="11000000" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Besar Pembayaran Ini (Rp) *</label>
                        <input type="number" id="inflow-payment-amount" required min="0" placeholder="3300000" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Termin ke *</label>
                        <select id="inflow-termin-no" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                            <option value="Survei">Survei</option>
                            <option value="1">Termin 1 (DP)</option>
                            <option value="2">Termin 2</option>
                            <option value="3">Termin 3</option>
                            <option value="4">Termin 4</option>
                            <option value="Revisi">Revisi</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Total Termin Project *</label>
                        <select id="inflow-total-termin" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                            <option value="Survei">Survei</option>
                            <option value="3">3 Termin</option>
                            <option value="4">4 Termin</option>
                            <option value="1">1 Termin (Pelunasan Direct)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">Catatan Tambahan</label>
                    <input type="text" id="inflow-notes" placeholder="Catatan (e.g. Tambah RAP, Diskon khusus, dll)" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 8px 10px; border-radius: 6px; outline: none; font-family: inherit; font-size: 13px;">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="button" class="primary-btn" id="btn-cancel-inflow-modal" style="flex: 1; justify-content: center; background: rgba(255,255,255,0.05); color: white; border: 1px solid var(--glass-border); font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1.5; justify-content: center; background: var(--primary); color: #020617; font-weight: 700; font-family: inherit;"><i class="ph ph-check"></i> Simpan Data Pemasukan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Spreadsheet File Modal (Data Transfer Klien - Finance Manager) -->
    <div id="import-inflow-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2700; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-sidebar); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); width: 520px; max-width: 95%; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); font-family: inherit; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">
                <h3 style="margin: 0; color: white; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-upload-simple" style="color: var(--info);"></i> Upload Spreadsheet & Auto-Detect Metriks
                </h3>
                <button type="button" id="btn-close-import-inflow-modal" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 18px;"><i class="ph ph-x"></i></button>
            </div>
            <form id="import-inflow-form" style="display: flex; flex-direction: column; gap: 16px;">
                <p style="font-size: 12px; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                    Unggah file spreadsheet (.csv) Data Transfer Klien Anda. Sistem akan membaca baris transaksi secara otomatis, menghitung Sisa Pembayaran, Status Lunas per Termin, dan mendeteksi seluruh metriks keuangan secara otomatis.
                </p>
                <div style="border: 2px dashed var(--glass-border); border-radius: 8px; padding: 24px; text-align: center; background: rgba(255,255,255,0.02); cursor: pointer;" onclick="document.getElementById('import-file-input').click()">
                    <i class="ph ph-file-csv" style="font-size: 40px; color: var(--info); margin-bottom: 8px;"></i>
                    <p style="margin: 0; font-size: 13px; font-weight: 600; color: white;">Pilih atau Tarik File Spreadsheet (.csv)</p>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-muted);" id="import-filename-display">Format yang didukung: CSV (Sesuai Struktur Google Sheets Data Transfer Klien)</p>
                    <input type="file" id="import-file-input" accept=".csv, .txt" style="display: none;">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="primary-btn" id="btn-cancel-import-inflow" style="flex: 1; justify-content: center; background: rgba(255,255,255,0.05); color: white; border: 1px solid var(--glass-border); font-family: inherit;">Batal</button>
                    <button type="submit" class="primary-btn" style="flex: 1.5; justify-content: center; background: var(--info); color: white; font-weight: 700; font-family: inherit;"><i class="ph ph-lightning"></i> Import & Deteksi Metriks</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Logic -->
    <script src="/js/app.js?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>"></script>
    <script src="/js/strategic-erp.js?v=<?php echo e(filemtime(public_path('js/strategic-erp.js'))); ?>"></script>
    <script src="/js/pwa.js?v=<?php echo e(filemtime(public_path('js/pwa.js'))); ?>"></script>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\dashboard.blade.php ENDPATH**/ ?>