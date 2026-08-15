<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIKARYA - Employee Workspace</title>
    <style>
        /* Premium Modal CSS */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
            z-index: 1000; align-items: center; justify-content: center;
            opacity: 0; animation: fadeIn 0.3s forwards;
        }
        .modal-content {
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 24px;
            width: 90%; max-width: 500px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            transform: translateY(20px);
            animation: slideUp 0.3s forwards;
            color: var(--text-main);
        }
        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes slideUp { to { transform: translateY(0); } }
        
        :root {
            --bg-main: #F8FAFC;
            --panel: #FFFFFF;
            --panel-secondary: #F1F5F9;
            --panel-border: #E2E8F0;
            --text-heading: #111827;
            --text-main: #374151;
            --text-muted: #6b7280;
            --accent: #0C3527;
            --accent-hover: #124836;
            --accent-active: #08261C;
            --secondary-surface: #D9EFE9;
            --success: #0C3527;
            --warning: #F59E0B;
            --danger: #EF4444;
            --accent-rgb: 12, 53, 39;
        }

        [data-theme="dark"] {
            --bg-main: #111111;
            --panel: #1a1a1a;
            --panel-secondary: #262626;
            --panel-border: rgba(255, 255, 255, 0.1);
            --text-heading: #f9fafb;
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
        }

        [data-theme="dark"] .brand-logo { background-color: var(--text-accent)fff !important; }

        .ios-btn {
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: -0.2px;
            font-family: inherit;
        }
        .ios-btn:active { transform: scale(0.96); }
        .ios-btn-primary { background: var(--accent); color: white; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.3); }
        .ios-btn-primary:hover { background: var(--accent-hover); box-shadow: 0 6px 16px rgba(var(--accent-rgb), 0.4); }
        .ios-btn-secondary { background: var(--secondary-surface); color: var(--accent); }
        .ios-btn-secondary:hover { background: #c2ded7; }
        .ios-btn-danger { background: var(--danger); color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }
        .ios-btn-danger:hover { background: #dc2626; }
        .ios-input, .form-control {
            width: 100%; padding: 12px 16px;
            background: var(--panel-secondary);
            border: 1px solid var(--panel-border);
            border-radius: 12px; color: var(--text-heading);
            font-size: 14px; outline: none; transition: box-shadow 0.2s;
        }
        .ios-input:focus, .form-control:focus { box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.2); }
        select option { background: var(--panel-secondary); color: var(--text-heading); }
        
        .user-pill {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .user-pill:active { transform: scale(0.96); }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background-color: var(--bg-main);
            background-image: radial-gradient(circle at top right, rgba(var(--accent-rgb), 0.05), transparent 400px);
            color: var(--text-main);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
        }

        /* Sidebar */
        .sidebar {
            background: var(--panel-secondary);
            border-right: 1px solid var(--panel-border);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .brand {
            padding: 0 24px;
            margin-bottom: 32px;
        }
        .brand h1 {
            font-size: 20px;
            font-weight: 900;
            margin: 0;
            background: linear-gradient(to right, #D9EFE9, #0C3527);
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: 1px;
        }

        .user-profile {
            padding: 0 24px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar {
            width: 40px; height: 40px; border-radius: 12px; background: rgba(12, 53, 39, 0.1); border: 1px solid rgba(12, 53, 39, 0.2); color: var(--accent);
            display: flex; align-items: center; justify-content: center; font-weight: bold; object-fit: cover;
        }
        .user-info { flex: 1; }
        .user-name { font-weight: 600; font-size: 14px; margin-bottom: 2px; color: var(--text-heading); }
        .user-role { color: var(--text-muted); font-size: 12px; }

        .nav-section {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin: 20px 24px 8px;
            font-weight: 700;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .nav-item:hover {
            color: var(--text-main);
            background: rgba(0, 0, 0, 0.04);
        }
        .nav-item.active {
            color: var(--accent-active);
            background: rgba(12, 53, 39, 0.15);
            border-right: 3px solid var(--accent);
        }
        .nav-item i { margin-right: 12px; width: 20px; text-align: center; }

        /* Main Content */
        .main-view {
            padding: 32px 48px;
            overflow-y: auto;
            position: relative;
        }

        .top-bar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;
            background: var(--panel); padding: 16px 24px; border-radius: 12px; border: 1px solid var(--panel-border);
        }
        .user-info-top { display: flex; align-items: center; gap: 16px; }
        .avatar-top {
            width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--accent); background: var(--panel-secondary); display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--accent);
        }
        
        .btn {
            background: var(--accent); color: white; border: none; padding: 8px 16px;
            border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-danger { background: var(--danger); }
        .btn-outline { background: transparent; border: 1px solid var(--panel-border); color: var(--text-heading); }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        
        .card {
            background: var(--panel); border: 1px solid var(--panel-border);
            border-radius: 16px; padding: 24px;
        }
        .card h3 { margin: 0 0 16px 0; font-size: 16px; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px; color: var(--text-heading); font-weight: 700; }

        /* Form */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-heading); font-size: 13px; font-weight: 600; }
        .form-control {
            width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--panel-border);
            background: var(--panel-secondary); color: var(--text-heading); font-family: inherit; transition: all 0.2s;
        }
        .form-control:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 3px rgba(12, 53, 39, 0.1); }
        textarea.form-control { resize: vertical; min-height: 80px; }

        /* Status Pills */
        .pill { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .pill.success { background: rgba(12, 53, 39,0.15); color: var(--success); }
        .pill.warning { background: rgba(245,158,11,0.15); color: var(--warning); }
        
        /* Views */
        .view-section { display: none; }
        .view-section.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Chat UI */
        .chat-layout { display: grid; grid-template-columns: 250px 1fr; gap: 20px; height: calc(100vh - 180px); }
        .chat-channels { background: var(--panel-secondary); border: 1px solid var(--panel-border); border-radius: 12px; overflow-y: auto; }
        .channel-item { padding: 12px 16px; border-bottom: 1px solid var(--panel-border); cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .channel-item:hover, .channel-item.active { background: rgba(12, 53, 39,0.1); color: var(--accent-active); border-right: 3px solid var(--accent); }
        
        .chat-window { background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; display: flex; flex-direction: column; }
        .chat-header { padding: 16px; border-bottom: 1px solid var(--panel-border); font-weight: bold; display: flex; justify-content: space-between; color: var(--text-heading); }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; }
        .message { display: flex; gap: 12px; max-width: 80%; }
        .message.mine { align-self: flex-end; flex-direction: row-reverse; }
        .msg-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--panel-secondary); border: 1px solid var(--panel-border); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: var(--accent); }
        .msg-bubble { background: var(--panel-secondary); border: 1px solid var(--panel-border); padding: 12px 16px; border-radius: 0 12px 12px 12px; color: var(--text-main); }
        .message.mine .msg-bubble { background: var(--accent); color: white; border: none; border-radius: 12px 0 12px 12px; }
        .msg-meta { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        
        .chat-input { padding: 16px; border-top: 1px solid var(--panel-border); display: flex; gap: 10px; background: var(--panel-secondary); border-radius: 0 0 12px 12px; }
        .chat-input input[type="text"] { flex: 1; background: #FFFFFF; border: 1px solid var(--panel-border); border-radius: 20px; padding: 10px 16px; color: var(--text-main); outline: none; }
        .chat-input input[type="text"]:focus { border-color: var(--accent); }
        .chat-input button { background: var(--accent); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: all 0.2s; }
        .chat-input button:hover { background: var(--accent-hover); }

        /* Responsive CSS */
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 768px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .sidebar.mobile-open { display: flex; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 100; }
            .mobile-toggle { display: block !important; }
            .mobile-close { display: block !important; }
            .main-view { padding: 20px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 16px; }
            .grid-4 { grid-template-columns: 1fr; }
        }
        .org-division-container {
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            background: rgba(18, 18, 18, 0.4);
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
        }
        .org-division-container:hover {
            box-shadow: 0 0 20px rgba(12, 53, 39,0.1);
            border-color: rgba(12, 53, 39,0.2);
        }
        .org-card {
            background: rgba(30, 30, 30, 0.7);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            margin-bottom: 16px;
            transition: all 0.3s;
        }
        .org-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        .org-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin: 0 auto 12px auto;
        }
        .org-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin: 12px 0;
        }
        .org-badge {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            background: var(--panel-border);
            color: white;
        }
        .badge-division { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-role { color: #9ca3af; background: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.3); }
        .badge-type { color: #9ca3af; background: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.3); }
        .badge-task { color: var(--text-accent); background: rgba(12, 53, 39, 0.1); border: 1px solid rgba(12, 53, 39, 0.3); cursor: pointer;}
        .badge-task-active { color: var(--text-accent); background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); cursor: pointer;}
        
        .org-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 12px;
        }
        .org-actions button {
            flex: 1;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
            padding: 6px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .org-actions button:hover {
            background: var(--panel-border);
            color: var(--text-accent);
        }
        .org-actions .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        /* Organization Tree CSS */
        .org-tree {
            display: flex; justify-content: center; overflow-x: auto; padding-bottom: 20px;
        }
        .org-tree ul {
            padding-top: 20px; position: relative;
            transition: all 0.5s;
            display: flex; justify-content: center;
            padding-left: 0; list-style-type: none;
        }
        .org-tree li {
            float: left; text-align: center;
            list-style-type: none; position: relative;
            padding: 20px 8px 0 8px; transition: all 0.5s;
        }
        .org-tree li::before, .org-tree li::after{
            content: ''; position: absolute; top: 0; right: 50%;
            border-top: 2px solid rgba(12, 53, 39,0.4);
            width: 50%; height: 20px;
        }
        .org-tree li::after{
            right: auto; left: 50%;
            border-left: 2px solid rgba(12, 53, 39,0.4);
        }
        .org-tree li:only-child::after, .org-tree li:only-child::before { display: none; }
        .org-tree li:only-child{ padding-top: 0;}
        .org-tree li:first-child::before, .org-tree li:last-child::after{ border: 0 none; }
        .org-tree li:last-child::before{
            border-right: 2px solid rgba(12, 53, 39,0.4);
            border-radius: 0 5px 0 0;
        }
        .org-tree li:first-child::after{ border-radius: 5px 0 0 0; }
        .org-tree ul ul::before{
            content: ''; position: absolute; top: 0; left: 50%;
            border-left: 2px solid rgba(12, 53, 39,0.4);
            width: 0; height: 20px;
        }
        .org-tree .org-card { margin: 0 auto; width: 260px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Initializer (Prevents FOUC) -->
    <script>
        (function() {
            var theme = localStorage.getItem('sikarya_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            var color = localStorage.getItem('sikarya_color');
            if(color) {
                document.documentElement.style.setProperty('--accent', color);
                document.documentElement.style.setProperty('--accent-hover', color);
                document.documentElement.style.setProperty('--accent-active', color);
            }
        })();
    </script>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <button class="mobile-close" onclick="document.querySelector('.sidebar').classList.remove('mobile-open')" style="display:none; position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer;">&times;</button>
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-logo" style="width: 170px; height: 45px; margin: 0 auto 10px auto; background-color: var(--accent); -webkit-mask: url('{{ asset('images/sikarya-logo.png') }}') no-repeat center; mask: url('{{ asset('images/sikarya-logo.png') }}') no-repeat center; -webkit-mask-size: contain; mask-size: contain;"></div>
            <small style="color: var(--text-muted); font-size: 12px;">Employee Hub</small>
        </div>
        <div class="brand">
            <small>{{ $company->name }}</small>
        </div>
        <div style="padding: 0 24px; margin-bottom: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: bold; margin-bottom: 8px;">{{ $user->isManager() ? 'Manager Workspace' : 'My Workspace' }}</div>
        </div>
        <a class="nav-item active" onclick="switchView('dashboard')"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a class="nav-item" onclick="switchView('attendance')"><i class="fa-solid fa-clock"></i> Attendance</a>
        <a class="nav-item" onclick="switchView('chat')"><i class="fa-solid fa-comments"></i> Internal Chat</a>
        <a class="nav-item" onclick="switchView('tasks')"><i class="fa-solid fa-list-check"></i> My Tasks</a>
        <a class="nav-item" onclick="switchView('hierarchy')"><i class="fa-solid fa-sitemap"></i> Struktur Organisasi</a>
        @if($user->isManager())
        <a class="nav-item" onclick="switchView('goals')"><i class="fa-solid fa-bullseye"></i> Set KPI Tim</a>
        @endif

        @if(\Illuminate\Support\Str::contains(strtolower($user->job_title ?? ''), ['kasir', 'toko', 'supervisor operational branch']))
            <a class="nav-item" onclick="switchView('pos')"><i class="fa-solid fa-cash-register"></i> Mesin Kasir (POS)</a>
        @endif

        @if(\Illuminate\Support\Str::contains(strtolower($user->job_title ?? ''), ['inventory', 'gudang', 'warehouse']))
            <a class="nav-item" href="{{ route('inventory.dashboard') }}"><i class="fa-solid fa-boxes-stacked"></i> Gudang & Stok & Warehouse</a>
        @endif

        @if(\Illuminate\Support\Str::contains(strtolower($user->job_title ?? ''), ['purchasing']))
            <a class="nav-item" onclick="switchView('purchasing')"><i class="fa-solid fa-cart-shopping"></i> Purchasing & Suplier</a>
        @endif

        @if(\Illuminate\Support\Str::contains(strtolower($user->job_title ?? ''), ['produksi', 'bakery', 'pastry']))
            <a class="nav-item" onclick="switchView('production')"><i class="fa-solid fa-industry"></i> Produksi (Resep / Komposisi)</a>
        @endif

        <a class="nav-item" onclick="switchView('payslips')"><i class="fa-solid fa-file-invoice-dollar"></i> Payslips</a>
        <a class="nav-item" onclick="switchView('profile')"><i class="fa-solid fa-user-gear"></i> Profil & Pengaturan</a>

        @if($user->isManager())
        <div style="padding: 16px 24px; margin-top: auto; margin-bottom: 8px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: bold;">Tim Saya</div>
        </div>
        <a class="nav-item" onclick="switchView('team-attendance')"><i class="fa-solid fa-users-rectangle"></i> Attendance Tim</a>
        @endif
        
        <div style="padding: 0 24px; margin-top: auto; margin-bottom: 24px;">
            <div class="card" style="padding: 16px; background: rgba(0,0,0,0.3); margin-bottom: 16px;">
                <h3 style="font-size: 12px; margin-bottom: 8px; padding-bottom: 4px;">Company Documents</h3>
                @php
                    $docs = \App\Models\CompanyDocument::where('company_id', $company->id)->get();
                @endphp
                @forelse($docs as $doc)
                    <a href="/storage/{{ $doc->file_path }}" target="_blank" style="display: block; font-size: 11px; color: var(--accent); text-decoration: none; margin-bottom: 4px;"><i class="fa-solid fa-download"></i> {{ $doc->title }}</a>
                @empty
                    <span style="font-size: 11px; color: var(--text-muted);">Tidak ada dokumen.</span>
                @endforelse
            </div>

            <div class="card" style="padding: 16px; background: rgba(0,0,0,0.3);">
                <h3 style="font-size: 12px; margin-bottom: 8px; padding-bottom: 4px;">Leave Balance</h3>
                @if($leaveQuotas && $leaveQuotas->count() > 0)
                    @foreach($leaveQuotas as $quota)
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span>Tahun {{ $quota->year }}</span>
                            <strong style="color: var(--success);">{{ $quota->remaining() }} Hari</strong>
                        </div>
                    @endforeach
                @else
                    <span style="font-size: 12px; color: var(--text-muted);">Tidak ada kuota cuti</span>
                @endif
                
                @php
                    $activeLeave = \App\Models\LeaveRequest::where('user_id', auth()->id())->whereIn('status', ['approved', 'pending_ceo', 'pending_manager'])->first();
                @endphp
                
                @if($activeLeave)
                    <form method="post" action="{{ route('master-demo.leave.cancel', $activeLeave->id) }}">
                        @csrf
                        <button type="submit" class="user-pill" style="width: 100%; margin-top: 10px; background: rgba(239,68,68,0.2); color: var(--danger); border: 1px solid var(--danger); font-size: 11px; cursor: pointer;">Batalkan Cuti ({{ $activeLeave->start_date->format('d/m') }})</button>
                    </form>
                @endif
            </div>
        </div>
    </aside>



    <!-- Main Content -->
    <main class="main-view">
        
        <!-- Top Bar -->
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('mobile-open')" style="background: none; border: none; color: var(--text-heading); font-size: 20px; cursor: pointer; display: none;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h2 id="view-title" style="margin:0; font-size:24px; color:var(--text-heading);">Dashboard</h2>
                    <p id="view-subtitle" style="margin:5px 0 0 0; color:var(--text-muted); font-size: 14px;">Selamat bekerja di divisi <strong>{{ $user->divisionLabel() }}</strong>.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 16px; align-items: center;">
                <div class="theme-controls" style="display: flex; gap: 8px; align-items: center; border-right: 1px solid var(--panel-border); padding-right: 16px;">
                    <input type="color" id="theme-color-picker" title="Kustomisasi Warna Utama" onchange="setPrimaryColor(this.value)" style="width: 28px; height: 28px; border: none; border-radius: 50%; cursor: pointer; background: transparent; overflow: hidden; padding: 0;">
                    <button onclick="toggleTheme()" title="Ubah Mode Gelap/Terang" style="background: transparent; border: 1px solid var(--panel-border); color: var(--text-heading); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>

                <div class="user-info-top">
                    <div style="text-align: right;">
                        <strong style="display: block; color: var(--text-heading);">{{ $user->name }}</strong>
                        <small style="color: var(--text-muted);">{{ $user->job_title }} · {{ $user->divisionLabel() }}</small>
                    </div>
                    @if($user->profile_picture_path)
                        <img src="/storage/{{ $user->profile_picture_path }}" alt="Profile" class="avatar-top">
                    @else
                        <div class="avatar-top">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                </div>
            </div>
            
            <button type="button" onclick="confirmLogout()" class="btn btn-danger" style="margin-left: 16px;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </button>
        </header>

        @if(session('status'))
            <div style="background: rgba(12, 53, 39,0.2); border: 1px solid var(--success); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div style="background: rgba(239,68,68,0.2); border: 1px solid var(--danger); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- DASHBOARD VIEW -->
        <div id="view-dashboard" class="view-section active">
            @if(session('attendance_success'))
                <div style="background: rgba(12, 53, 39,0.2); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                    <i class="fa-solid fa-check-circle"></i> {{ session('attendance_success') }}
                </div>
            @endif
            @if(session('attendance_error'))
                <div style="background: rgba(239,68,68,0.2); border: 1px solid var(--danger); color: var(--danger); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                    <i class="fa-solid fa-exclamation-circle"></i> {{ session('attendance_error') }}
                </div>
            @endif


            @if($latestAnnouncement)
                <div style="background: linear-gradient(135deg, #4f46e5, #0C3527); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);">
                    <i class="fa-solid fa-bullhorn" style="font-size: 24px; margin-top: 4px;"></i>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: white; border: none; padding: 0;">{{ $latestAnnouncement->title }}</h3>
                        <div style="font-size: 14px; line-height: 1.5; opacity: 0.9;">{{ $latestAnnouncement->content }}</div>
                        <div style="font-size: 11px; margin-top: 12px; opacity: 0.7;">Disiarkan oleh Management &bull; {{ $latestAnnouncement->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <!-- Announcement Popup Modal -->
                <div id="modal-announcement-popup" class="modal-overlay" style="display:none; z-index: 10000; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
                    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); box-shadow: 0 20px 40px rgba(0,0,0,0.2); padding: 32px 24px;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">Pengumuman Baru</h2>
                        </div>
                        <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: #1e293b; text-align: center;">{{ $latestAnnouncement->title }}</h3>
                        <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px; text-align: center; background: #f8fafc; padding: 16px; border-radius: 12px;">
                            {{ $latestAnnouncement->content }}
                        </div>
                        <button class="ios-btn ios-btn-primary" style="width: 100%;" onclick="dismissAnnouncementPopup('{{ $latestAnnouncement->id }}')">Saya Mengerti</button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const announcementId = '{{ $latestAnnouncement->id }}';
                        const dismissed = localStorage.getItem('announcement_dismissed_' + announcementId);
                        if (!dismissed) {
                            setTimeout(() => {
                                document.getElementById('modal-announcement-popup').style.display = 'flex';
                            }, 500);
                        }
                    });
                    function dismissAnnouncementPopup(id) {
                        localStorage.setItem('announcement_dismissed_' + id, 'true');
                        document.getElementById('modal-announcement-popup').style.display = 'none';
                    }
                </script>
            @endif

            <!-- Quick Attendance Card -->
            <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--accent);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0 0 4px 0; border: none; padding: 0;">Absensi Hari Ini ({{ now()->format('d M Y') }})</h3>
                        @if($activeAttendance)
                            <span style="font-size: 13px; color: var(--success);"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Clock in: {{ $activeAttendance->clock_in->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB</span>
                        @else
                            <span style="font-size: 13px; color: var(--text-muted);">Belum clock in hari ini</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 8px;">
                        @if(!$activeAttendance)
                            <form method="POST" action="{{ route('master-demo.attendance.clock-in') }}">
                                @csrf
                                <div style="margin-bottom: 12px;">
                                    <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 4px;">Pilih Shift Kerja</label>
                                    <select name="shift_id" class="form-control" style="font-size: 12px; padding: 6px; width: 100%; border: 1px solid var(--panel-border);  border-radius: 4px;" required>
                                        <option value="">-- Pilih Shift --</option>
                                        @foreach($shifts ?? [] as $shift)
                                            <option value="{{ $shift->id }}" {{ $user->default_shift_id == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn" style="background: var(--success); gap: 6px; width: 100%; justify-content: center;"><i class="fa-solid fa-right-to-bracket"></i> Clock In</button>
                            </form>
                        @elseif(!$activeAttendance->clock_out)
                            <form method="POST" action="{{ route('master-demo.attendance.clock-out') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="gap: 6px;"><i class="fa-solid fa-right-from-bracket"></i> Clock Out</button>
                            </form>
                        @else
                            <span class="pill success">Selesai ({{ $activeAttendance->clock_out->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') }})</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($user->isManager())
            <!-- Manager: Team Overview -->
            <div class="card" style="margin-bottom: 24px;">
                <h3>Tim {{ $user->divisionLabel() }}</h3>
                @php
                    $teamMembers = \App\Models\User::where('parent', $user->username)->where('is_active', true)->get();
                @endphp
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                    @foreach($teamMembers as $member)
                        @php $memberAtt = $member->attendances()->whereDate('clock_in', today())->first(); @endphp
                        <div style="background: rgba(0,0,0,0.04); padding: 12px; border-radius: 8px; border-left: 3px solid {{ $memberAtt ? 'var(--success)' : 'var(--warning)' }};">
                            <strong style="font-size: 13px; display: block;">{{ $member->name }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $member->job_title }}</span>
                            <div style="font-size: 11px; margin-top: 4px; color: {{ $memberAtt ? 'var(--success)' : 'var(--warning)' }};">
                                {{ $memberAtt ? 'Hadir ' . $memberAtt->clock_in->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') : 'Belum hadir' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="grid-2">
                <!-- Daily Report Form -->
                <section class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; border: none; padding: 0;">Daily Report ({{ today()->format('d M Y') }})</h3>
                        @if($todayReport)
                            <span class="pill success">Submitted</span>
                        @else
                            <span class="pill warning">Pending</span>
                        @endif
                    </div>

                    @if($todayReport)
                        <div style="background: var(--bg-main); padding: 16px; border-radius: 8px; border: 1px dashed var(--success);">
                            <p style="margin-top: 0; white-space: pre-wrap;">{{ $todayReport->content }}</p>
                            @if($todayReport->attachment_path)
                                <a href="{{ asset('storage/' . $todayReport->attachment_path) }}" target="_blank" style="color: var(--accent); font-size: 13px;"><i class="fa-solid fa-paperclip"></i> View Attachment</a>
                            @endif
                        </div>
                        <p style="color: var(--text-muted); font-size: 12px; margin-top: 12px;">Laporan Anda telah terekam dan akan otomatis terkunci (locked) dalam 24 jam.</p>
                    @else
                        <form method="POST" action="{{ route('master-demo.employee.report') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Pilih Pekerjaan (Assigned by CEO) <span style="color:var(--danger)">*</span></label>
                                <select name="task_id" class="form-control" style="background: rgba(0,0,0,0.5); margin-bottom: 12px;" required>
                                    <option value="">-- Pilih Pekerjaan / Goal --</option>
                                    @foreach($user->tasks()->where('type', 'goal')->where('status', '!=', 'completed')->get() as $goal)
                                        <option value="{{ $goal->id }}">{{ $goal->title }} (Deadline: {{ $goal->deadline ? $goal->deadline->format('d M Y') : '-' }})</option>
                                    @endforeach
                                    <option value="other">Pekerjaan Lainnya (Tulis manual di bawah)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Laporan Tambahan / Kuantitas Hari Ini</label>
                                <textarea name="content" class="form-control" required placeholder="Contoh: Mengadon 50kg terigu..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Lampiran (Opsional, foto bukti kerja)</label>
                                <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                            </div>
                            <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Submit Report</button>
                        </form>
                    @endif

                    <!-- Riwayat Sub-Task Selesai -->
                    <div style="margin-top: 32px;">
                        <h4 style="margin: 0 0 12px 0;">Riwayat Laporan & Tugas Selesai</h4>
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; background: var(--bg-main); border-radius: 8px; overflow: hidden;">
                            <thead>
                                <tr style="background: rgba(255,255,255,0.05); text-align: left; color: var(--text-muted);">
                                    <th style="padding: 10px;">Tanggal</th>
                                    <th style="padding: 10px;">Goal Utama</th>
                                    <th style="padding: 10px;">Laporan / Sub-Task</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->tasks()->where('type', 'daily')->where('status', 'completed')->orderByDesc('created_at')->take(10)->get() as $tugasSelesai)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="padding: 10px;">{{ $tugasSelesai->created_at->format('d M Y') }}</td>
                                    <td style="padding: 10px; color: var(--accent);">{{ $tugasSelesai->parent ? $tugasSelesai->parent->title : 'Lainnya' }}</td>
                                    <td style="padding: 10px;">{{ $tugasSelesai->title }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" style="padding: 16px; text-align: center; color: var(--text-muted);">Belum ada riwayat tugas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Profile & Bio Update -->
                <section class="card">
                    <h3>My Profile & Bio</h3>
                    <form method="POST" action="{{ route('master-demo.employee.profile') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Foto Profil (Rasio 1:1, Maks 2MB)</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Bio Singkat</label>
                            <textarea name="bio" class="form-control" placeholder="Tuliskan bio atau moto kerja Anda di sini...">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-outline"><i class="fa-solid fa-floppy-disk"></i> Update Profile</button>
                    </form>
                </section>
            </div>
        </div>
        
        <!-- ATTENDANCE VIEW -->
        <div id="view-attendance" class="view-section">
            <script>
                function getGPSAndSubmit(formId) {
                    let btn = document.querySelector('#' + formId + ' button[type="button"]');
                    if(btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mendapatkan GPS...';
                    
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            let gpsInput = document.getElementById(formId + '-gps');
                            if(gpsInput) gpsInput.value = position.coords.latitude + ',' + position.coords.longitude;
                            document.getElementById(formId).submit();
                        }, function(error) {
                            alert('Gagal mendapatkan GPS: ' + error.message + '. Melanjutkan tanpa koordinat presisi.');
                            document.getElementById(formId).submit();
                        }, { timeout: 10000 });
                    } else {
                        document.getElementById(formId).submit();
                    }
                }
            </script>

            <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--accent);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h3 style="margin: 0 0 4px 0; border: none; padding: 0;">Absensi Hari Ini ({{ now()->format('d M Y') }})</h3>
                        @if($activeAttendance)
                            <span style="font-size: 13px; color: var(--success);"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Clock in: {{ $activeAttendance->clock_in->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB</span>
                        @else
                            <span style="font-size: 13px; color: var(--text-muted);">Belum clock in hari ini</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if(!$activeAttendance)
                            <form id="form-clock-in" method="POST" action="{{ route('master-demo.attendance.clock-in') }}">
                                @csrf
                                <input type="hidden" name="location_coordinates" id="form-clock-in-gps" value="">
                                <div style="display: flex; gap: 8px;">
                                    <div style="min-width: 150px; flex: 1;">
                                        <select name="shift_id" class="ios-input" style="font-size: 13px; padding: 12px; border-radius: 12px; width: 100%;" required>
                                            <option value="">-- Pilih Shift --</option>
                                            @foreach($shifts ?? [] as $shift)
                                                <option value="{{ $shift->id }}" {{ $user->default_shift_id == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" onclick="getGPSAndSubmit('form-clock-in')" class="btn" style="background: var(--success); gap: 6px;"><i class="fa-solid fa-right-to-bracket"></i> Clock In</button>
                                </div>
                            </form>
                            <form id="form-out-hours" method="POST" action="{{ route('master-demo.attendance.clock-in') }}">
                                @csrf
                                <input type="hidden" name="is_out_of_hours" value="1">
                                <input type="hidden" name="location_coordinates" id="form-out-hours-gps" value="">
                                <button type="button" onclick="getGPSAndSubmit('form-out-hours')" class="btn" style="background: var(--warning); gap: 6px;"><i class="fa-solid fa-moon"></i> Luar Jam Kerja</button>
                            </form>
                        @elseif(!$activeAttendance->clock_out)
                            <!-- Istirahat Controls -->
                            @if(!$activeAttendance->rest_start)
                                <form method="POST" action="{{ route('master-demo.attendance.rest-start') }}">
                                    @csrf
                                    <button type="submit" class="btn" style="background: var(--warning); gap: 6px;"><i class="fa-solid fa-mug-hot"></i> Mulai Istirahat</button>
                                </form>
                            @elseif(!$activeAttendance->rest_end)
                                <form method="POST" action="{{ route('master-demo.attendance.rest-end') }}">
                                    @csrf
                                    <button type="submit" class="btn" style="background: var(--accent); gap: 6px;"><i class="fa-solid fa-briefcase"></i> Selesai Istirahat</button>
                                </form>
                            @endif

                            <form id="form-clock-out" method="POST" action="{{ route('master-demo.attendance.clock-out') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger" style="gap: 6px;"><i class="fa-solid fa-right-from-bracket"></i> Clock Out</button>
                            </form>
                        @else
                            <span class="pill success">Selesai ({{ $activeAttendance->clock_out->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') }})</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <!-- Pengajuan Cuti -->
                <div class="card">
                    <h3 style="margin-top: 0;"><i class="fa-solid fa-plane-departure"></i> Pengajuan Cuti Tahunan</h3>
                    <form method="POST" action="{{ route('master-demo.leave-request.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Alasan Cuti</label>
                            <textarea name="reason" class="form-control" placeholder="Keperluan keluarga, liburan, dll..." required></textarea>
                        </div>
                        <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;">Ajukan Cuti</button>
                    </form>
                </div>

                <!-- Ambil Lembur -->
                <div class="card" style="padding: 24px; border: 1px solid var(--panel-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-business-time" style="color: var(--accent); margin-right: 8px;"></i> Form Ambil Lembur</h3>
                    
                    @if(session('attendance_success'))
                        <div style="background: rgba(12, 53, 39,0.1); border-left: 4px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 8px; font-size: 16px;"></i>
                            {{ session('attendance_success') }}
                        </div>
                    @endif

                    <form id="form-overtime" method="POST" action="{{ route('master-demo.employee.overtime') }}" style="margin-bottom: 20px;">
                        @csrf
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px; display: block;">Jenis Lembur</label>
                            <select name="overtime_type_id" class="ios-input" required style="width: 100%; padding: 12px; border-radius: 12px;">
                                <option value="">-- Pilih Lembur --</option>
                                @foreach($overtimeTypes ?? [] as $ot)
                                    <option value="{{ $ot->id }}">{{ $ot->name }} (Rp {{ number_format($ot->rate_per_hour, 0, ',', '.') }}/jam)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px; display: block;">Tanggal Lembur</label>
                            <input type="date" name="date" class="ios-input" required style="width: 100%; padding: 12px; border-radius: 12px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px; display: block;">Durasi (Jam)</label>
                            <input type="number" step="0.5" name="hours" class="ios-input" placeholder="Contoh: 2.5" required style="width: 100%; padding: 12px; border-radius: 12px; box-sizing: border-box;">
                        </div>
                        <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%; border-radius: 12px; padding: 14px; font-weight: 600;"><i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Ajukan Lembur</button>
                    </form>

                    <!-- Riwayat Pengajuan Lembur -->
                    <div style="margin-top: 30px; border-top: 1px dashed var(--panel-border); padding-top: 20px;">
                        <h4 style="margin-top: 0; margin-bottom: 16px; font-size: 14px; color: var(--text-heading);">Riwayat Pengajuan Lembur Terakhir</h4>
                        @php
                            $myOvertimes = \App\Models\OvertimeRequest::where('user_id', $user->id)->orderByDesc('created_at')->take(5)->get();
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @forelse($myOvertimes as $otReq)
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 12px 16px; border-radius: 12px; border: 1px solid var(--panel-border);">
                                    <div>
                                        <div style="font-weight: 600; font-size: 13px;">{{ $otReq->overtimeType?->name ?? 'Lembur' }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ \Carbon\Carbon::parse($otReq->date)->format('d M Y') }} &bull; {{ $otReq->hours }} Jam</div>
                                    </div>
                                    <div>
                                        @if($otReq->status === 'pending')
                                            <span style="background: rgba(245,158,11,0.1); color: var(--warning); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Pending</span>
                                        @elseif($otReq->status === 'approved')
                                            <span style="background: rgba(12, 53, 39,0.1); color: var(--success); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Disetujui</span>
                                        @else
                                            <span style="background: rgba(239,68,68,0.1); color: var(--danger); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Ditolak</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 10px;">Belum ada pengajuan lembur.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Riwayat Attendance Saya</h3>
                @php
                    $myAttendances = $user->attendances()->orderByDesc('clock_in')->take(30)->get();
                @endphp
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--panel-border);">
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Tanggal</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Clock In</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Clock Out</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Durasi</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myAttendances as $att)
                                @php
                                    $tz = config('app.timezone', 'Asia/Jakarta');
                                    $cin = $att->clock_in?->timezone($tz);
                                    $cout = $att->clock_out?->timezone($tz);
                                    $dur = ($cin && $cout) ? $cin->diffInMinutes($cout) : null;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--panel-border);">
                                    <td style="padding: 10px 8px;">{{ $cin?->format('d M Y') ?? '-' }}</td>
                                    <td style="padding: 10px 8px; color: var(--success);">{{ $cin?->format('H:i') ?? '-' }} WIB</td>
                                    <td style="padding: 10px 8px; color: var(--danger);">{{ $cout ? $cout->format('H:i') . ' WIB' : '-' }}</td>
                                    <td style="padding: 10px 8px;">{{ $dur !== null ? floor($dur/60).'j '.($dur%60).'m' : '-' }}</td>
                                    <td style="padding: 10px 8px;"><span class="pill {{ $att->status === 'Present' ? 'success' : 'warning' }}">{{ $att->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada riwayat attendance.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($user->isManager())
        <!-- TEAM ATTENDANCE VIEW (Manager Only) -->
        <div id="view-team-attendance" class="view-section">
            <div class="card">
                <h3>Riwayat Attendance Tim {{ $user->divisionLabel() }}</h3>
                @php
                    $teamIds = \App\Models\User::where('parent', $user->username)->where('is_active', true)->pluck('id')->push($user->id);
                    $teamAttendances = \App\Models\Attendance::whereIn('user_id', $teamIds)->with('user')->orderByDesc('clock_in')->take(50)->get();
                @endphp
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--panel-border);">
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Karyawan</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Tanggal</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Clock In</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Clock Out</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Durasi</th>
                                <th style="text-align: left; padding: 10px 8px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamAttendances as $tAtt)
                                @php
                                    $tz = config('app.timezone', 'Asia/Jakarta');
                                    $tCin = $tAtt->clock_in?->timezone($tz);
                                    $tCout = $tAtt->clock_out?->timezone($tz);
                                    $tDur = ($tCin && $tCout) ? $tCin->diffInMinutes($tCout) : null;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--panel-border);">
                                    <td style="padding: 10px 8px; font-weight: 600;">{{ $tAtt->user?->name ?? 'Unknown' }}</td>
                                    <td style="padding: 10px 8px;">{{ $tCin?->format('d M Y') ?? '-' }}</td>
                                    <td style="padding: 10px 8px; color: var(--success);">{{ $tCin?->format('H:i') ?? '-' }} WIB</td>
                                    <td style="padding: 10px 8px; color: var(--danger);">{{ $tCout ? $tCout->format('H:i') . ' WIB' : '-' }}</td>
                                    <td style="padding: 10px 8px;">{{ $tDur !== null ? floor($tDur/60).'j '.($tDur%60).'m' : '-' }}</td>
                                    <td style="padding: 10px 8px;"><span class="pill {{ $tAtt->status === 'Present' ? 'success' : 'warning' }}">{{ $tAtt->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada riwayat attendance tim.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- CHAT VIEW -->
        <div id="view-chat" class="view-section">
            <div class="chat-layout">
                <div class="chat-channels">
                    <div id="employee-chat-divisions">
                        <div style="padding: 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border);">Grup Diskusi</div>
                    </div>
                    <div style="padding: 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border); margin-top: 10px;">Direct Messages</div>
                    <div class="channel-item" onclick="loadChat('Manager_{{ $user->reports_to_id ?? 0 }}', event)"><i class="fa-solid fa-user"></i> {{ $user->manager()?->name ?? 'Manager' }}</div>
                </div>
                
                <div class="chat-window">
                    <div class="chat-header">
                        <span><i class="fa-solid fa-hashtag" style="color: var(--accent)"></i> <span id="active-channel-name">Produksi_Tim</span></span>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages-container">
                        <div style="text-align: center; color: var(--text-muted); font-size: 12px; margin-bottom: 10px;">Memuat pesan...</div>
                    </div>
                    
                    <div class="chat-input">
                        <input type="text" id="chat-input-box" placeholder="Type a message...">
                        <button type="button" id="emp-chat-send-btn" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TASKS VIEW -->
        <div id="view-tasks" class="view-section">
            <div class="card">
                <h3>My Tasks & KPI Targets</h3>
                <p style="color: var(--text-muted);">Target kuantitas pekerjaan yang diset oleh Atasan/CEO.</p>
                
                @php
                    $myTasks = \App\Models\Task::where('user_id', auth()->id())->orderBy('deadline', 'asc')->get();
                @endphp
                @forelse($myTasks as $task)
                    <div style="background: var(--bg-main); padding: 16px; border-radius: 8px; border-left: 4px solid var(--accent); margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <strong style="font-size: 16px;">{{ $task->title }}</strong>
                            <span class="pill {{ $task->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($task->status) }}</span>
                        </div>
                        <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 12px;">Deadline: {{ $task->due_date ? $task->due_date->format('d M Y') : 'Tanpa Deadline' }}</div>
                        <div style="font-size: 13px; margin-bottom: 8px;">{{ $task->description }}</div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                        Belum ada tugas yang ditugaskan ke Anda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PAYSLIPS VIEW -->
        <div id="view-payslips" class="view-section">
            <div class="card">
                <h3>Monthly Payslips</h3>
                <p style="color: var(--text-muted);">Riwayat slip gaji bulanan Anda (berdasarkan absensi dan kinerja).</p>
                
                <div id="payslips-security-gate">
                    <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px; text-align: center;">
                        <i class="fa-solid fa-lock" style="font-size: 32px; color: var(--accent); margin-bottom: 16px;"></i>
                        <h4 style="margin: 0 0 8px;">Security Gate</h4>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Masukkan Password/PIN Anda untuk melihat rincian slip gaji (Rahasia Perusahaan).</p>
                        <input type="password" id="payslip-pin" class="form-control" style="width: 200px; margin: 0 auto 16px; text-align: center; letter-spacing: 4px;" placeholder="****">
                        <button class="btn btn-primary" onclick="unlockPayslips()"><i class="fa-solid fa-unlock"></i> Buka Kunci</button>
                    </div>
                </div>

                <div id="payslips-content" style="display: none; margin-top: 24px;">
                    @forelse($payrolls as $slip)
                        @if(in_array($slip->status, ['approved', 'paid']))
                        <details style="margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 16px;">
                            <summary style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; outline: none; list-style: none;">
                                <div>
                                    <strong style="display: block; font-size: 14px;">Periode: {{ $slip->period_start->format('M Y') }}</strong>
                                    <span style="font-size: 12px; color: var(--text-muted);">Total Take Home Pay: Rp {{ number_format($slip->net_amount, 0, ',', '.') }}</span>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    @if($slip->status === 'paid')
                                        <span class="user-pill" style="background: rgba(12, 53, 39,0.2); color: var(--success); font-size: 11px;"><i class="fa-solid fa-money-bill-transfer"></i> Paid</span>
                                    @else
                                        <span class="user-pill" style="background: rgba(52,199,89,0.2); color: #34C759; font-size: 11px;"><i class="fa-solid fa-check"></i> Approved</span>
                                    @endif
                                    <span class="user-pill" style="background: rgba(255,255,255,0.1); font-size: 11px;">Rincian &darr;</span>
                                </div>
                            </summary>
                            <div style="margin-top: 16px; padding: 16px; background: rgba(255,255,255,0.02); border-radius: 8px; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="color: var(--text-muted);">Gaji Pokok:</span>
                                    <strong>Rp {{ number_format($slip->base_amount, 0, ',', '.') }}</strong>
                                </div>
                                @if($slip->items && count($slip->items) > 0)
                                    <hr style="border: 0; border-top: 1px dashed rgba(255,255,255,0.1); margin: 12px 0;">
                                    @foreach($slip->items as $item)
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                            <span style="color: var(--text-muted);">{{ $item->description }}</span>
                                            @if($item->type === 'allowance')
                                                <strong style="color: var(--success);">+ Rp {{ number_format($item->amount, 0, ',', '.') }}</strong>
                                            @else
                                                <strong style="color: var(--danger);">- Rp {{ number_format($item->amount, 0, ',', '.') }}</strong>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                                <hr style="border: 0; border-top: 1px dashed var(--panel-border); margin: 12px 0;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span><strong>TOTAL BERSIH:</strong></span>
                                    <strong style="font-size: 16px; color: var(--accent);">Rp {{ number_format($slip->net_amount, 0, ',', '.') }}</strong>
                                </div>
                                <div style="margin-top: 16px; display: flex; gap: 8px;">
                                    <a href="{{ route('master-demo.payroll.slip.preview', $slip->user_id) }}" target="_blank" class="user-pill" style="background: rgba(255,255,255,0.1); color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; flex: 1;">
                                        <i class="fa-solid fa-print"></i> Print Preview
                                    </a>
                                    <button onclick="downloadSlipPdf({{ $slip->user_id }})" class="user-pill" style="background: var(--accent); color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; flex: 1;">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                                    </button>
                                </div>
                            </div>
                        </details>
                        @endif
                    @empty
                        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fa-solid fa-file-invoice" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Slip gaji bulan ini belum tersedia.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- PROFILE VIEW -->
        <div id="view-profile" class="view-section">
            <div class="card">
                <h3>Profil & Pengaturan Akun</h3>
                <form action="{{ route('master-demo.employee.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                    </div>
                    <div class="form-group">
                        <label>Bio / Kutipan Singkat</label>
                        <textarea name="bio" class="form-control" placeholder="Tuliskan bio singkat...">{{ $user->bio }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small style="color: var(--text-muted);">Minimal 8 karakter.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <!-- PRODUCTION VIEW -->
        <div id="view-production" class="view-section">
            @php
                $companyId = $user->company_id ?? 1;
                $boms = \App\Models\BillOfMaterial::with('lines.component')->where('company_id', $companyId)->get();
                $wos = \App\Models\ProductionOrder::with(['billOfMaterial', 'materials.product', 'wastes', 'qualityChecks', 'product'])
                    ->where('company_id', $companyId)->orderBy('id', 'desc')->get();
                $products = \App\Models\Product::where('company_id', $companyId)->get();
            @endphp
            
            <div class="grid-2">
                <!-- Real-Time Stok Gudang -->
                <div class="card">
                    <h4 style="margin: 0 0 16px 0; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px;"><i class="fa-solid fa-boxes-stacked"></i> Real-Time Stok Gudang</h4>
                    <div style="background: var(--bg-main); padding: 16px; border-radius: 8px; border: 1px solid var(--panel-border);">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--panel-border); color: var(--text-muted); text-align: left;">
                                    <th style="padding: 8px;">Bahan Baku</th>
                                    <th style="padding: 8px;">Stok Gudang</th>
                                    <th style="padding: 8px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\StockMovement::select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'))->where('company_id', $companyId)->groupBy('product_id')->with('product')->get() as $stock)
                                    @if($stock->product && $stock->product->type === 'raw_material')
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 8px;">{{ $stock->product->name }}</td>
                                        <td style="padding: 8px; font-weight: bold;">{{ number_format($stock->total_qty, 0, ',', '.') }} {{ $stock->product->unit ?? 'Gram' }}</td>
                                        <td style="padding: 8px;">
                                            @if($stock->total_qty > 50000)
                                                <span class="pill" style="background: rgba(12, 53, 39,0.2); color: var(--success);">Aman</span>
                                            @elseif($stock->total_qty > 10000)
                                                <span class="pill" style="background: rgba(245,158,11,0.2); color: var(--warning);">Limit</span>
                                            @else
                                                <span class="pill" style="background: rgba(239,68,68,0.2); color: var(--danger);">Kritis</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resep / Komposisi Section -->
                <div class="card">
                    <h4 style="margin: 0 0 16px 0; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px;"><i class="fa-solid fa-clipboard-list"></i> Resep / Komposisi (Resep / Komposisi)</h4>
                    
                    <div style="margin-bottom: 24px; background: var(--bg-main); padding: 16px; border-radius: 8px;">
                        <h5 style="margin: 0 0 12px 0;">Buat Resep / Komposisi Baru</h5>
                        <form method="POST" action="{{ route('master-demo.bom.store') }}">
                            @csrf
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label>Nama Resep / Komposisi / Resep</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Resep Kopi Susu Aren" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label>Produk Akhir (Finished Good)</label>
                                <select name="product_id" class="form-control" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="margin-bottom: 12px;">
                                <label>Bahan Baku 1</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="components[0][product_id]" class="form-control" style="flex: 2;" required>
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.001" name="components[0][quantity_per_unit]" class="form-control" placeholder="Qty/Unit" style="flex: 1;" required>
                                </div>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label>Bahan Baku 2 (Opsional)</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="components[1][product_id]" class="form-control" style="flex: 2;">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.001" name="components[1][quantity_per_unit]" class="form-control" placeholder="Qty/Unit" style="flex: 1;">
                                </div>
                            </div>

                            <div style="margin-bottom: 16px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                                <h6 style="margin: 0 0 8px 0; color: var(--accent);">Routing Produksi (Opsional)</h6>
                                <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                    <input type="text" name="routings[0][work_center]" class="form-control" placeholder="Work Center (Cth: Mixing)" style="flex: 2;">
                                    <input type="number" step="1" name="routings[0][expected_duration_minutes]" class="form-control" placeholder="Durasi (Menit)" style="flex: 1;">
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" name="routings[1][work_center]" class="form-control" placeholder="Work Center (Cth: Packaging)" style="flex: 2;">
                                    <input type="number" step="1" name="routings[1][expected_duration_minutes]" class="form-control" placeholder="Durasi (Menit)" style="flex: 1;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn" style="width: 100%; justify-content: center;"><i class="fa-solid fa-plus"></i> Simpan Resep / Komposisi</button>
                        </form>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @forelse($boms as $bom)
                            <div style="background: rgba(0,0,0,0.1); border: 1px solid var(--panel-border); border-radius: 8px; padding: 12px;">
                                <div style="font-weight: bold; margin-bottom: 4px;">{{ $bom->name }}</div>
                                <div style="font-size: 12px; color: var(--accent); margin-bottom: 8px;">For: {{ $bom->product?->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    @foreach($bom->lines as $line)
                                        <div>- {{ $line->component?->name }} ({{ $line->quantity_per_unit }})</div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 16px;">Belum ada Resep / Komposisi.</div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Work Orders Section -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px; margin-bottom: 16px;">
                        <h4 style="margin: 0;"><i class="fa-solid fa-helmet-safety"></i> Work Orders</h4>
                    </div>

                    <div style="margin-bottom: 24px; background: var(--bg-main); padding: 16px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h5 style="margin: 0;">Buat Batch Work Order (WO)</h5>
                            <button class="user-pill" onclick="addWoRow()" style="background: var(--panel-border); border: none; cursor: pointer; color: white;">+ Tambah Kerja</button>
                        </div>
                        <form method="POST" action="{{ route('master-demo.wo.store') }}">
                            @csrf
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label>Rencana Tanggal Produksi</label>
                                <input type="date" name="planned_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div id="wo-rows-container">
                                <div class="wo-row" style="display: flex; gap: 8px; margin-bottom: 12px;">
                                    <select name="work_orders[0][bill_of_material_id]" class="form-control" style="flex: 2;" required>
                                        <option value="">-- Pilih Resep / Komposisi / Resep --</option>
                                        @foreach($boms as $bom)
                                            <option value="{{ $bom->id }}">{{ $bom->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="1" name="work_orders[0][planned_quantity]" class="form-control" placeholder="Target Qty (Pcs)" style="flex: 1;" required>
                                </div>
                            </div>
                            <button type="submit" class="btn" style="width: 100%; justify-content: center; margin-top: 16px;"><i class="fa-solid fa-play"></i> Rilis Batch WO</button>
                        </form>
                        <script>
                            let woIndex = 1;
                            function addWoRow() {
                                const container = document.getElementById('wo-rows-container');
                                const firstRow = container.querySelector('.wo-row');
                                const newRow = firstRow.cloneNode(true);
                                newRow.querySelector('select').name = `work_orders[${woIndex}][bill_of_material_id]`;
                                newRow.querySelector('select').value = "";
                                newRow.querySelector('input').name = `work_orders[${woIndex}][planned_quantity]`;
                                newRow.querySelector('input').value = "";
                                container.appendChild(newRow);
                                woIndex++;
                            }
                        </script>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @forelse($wos as $wo)
                            <div style="border: 1px solid var(--panel-border); border-left: 4px solid {{ $wo->status === 'completed' ? 'var(--success)' : ($wo->status === 'in_progress' ? 'var(--warning)' : 'var(--accent)') }}; border-radius: 8px; padding: 16px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <div style="font-weight: bold; font-size: 14px;">{{ $wo->number }}</div>
                                    <span class="pill {{ $wo->status === 'completed' ? 'success' : ($wo->status === 'in_progress' ? 'warning' : '') }}">{{ strtoupper($wo->status) }}</span>
                                </div>
                                <div style="font-size: 12px; margin-bottom: 12px;">Target: <strong>{{ $wo->planned_quantity }}</strong> x {{ $wo->product?->name }} | Tgl: {{ $wo->planned_date?->format('d M Y') }}</div>
                                
                                @if($wo->status !== 'completed')
                                    <!-- Materials to Consume -->
                                    <div style="font-size: 11px; margin-bottom: 12px;">
                                        <strong>Bahan Baku:</strong>
                                        <ul style="padding-left: 16px; margin: 4px 0;">
                                            @foreach($wo->materials as $mat)
                                                <li style="margin-bottom: 6px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span>{{ $mat->product?->name }} (Butuh: {{ $mat->planned_quantity }}, Di-issue: {{ $mat->issued_quantity }}, Terpakai: <span style="color: {{ $mat->actual_quantity >= $mat->planned_quantity ? 'var(--success)' : 'var(--warning)' }}">{{ $mat->actual_quantity }}</span>)</span>
                                                        <div style="display: flex; gap: 4px;">
                                                            @if($mat->issued_quantity < $mat->planned_quantity)
                                                            <form method="POST" action="{{ route('master-demo.wo.issue', $wo->id) }}" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="material_id" value="{{ $mat->id }}">
                                                                <input type="hidden" name="quantity" value="{{ $mat->planned_quantity - $mat->issued_quantity }}">
                                                                <button type="submit" class="user-pill" style="border: 1px solid var(--warning); color: var(--warning); background: transparent; font-size: 10px; cursor: pointer;">Gudang: Issue</button>
                                                            </form>
                                                            @endif
                                                            @if($mat->actual_quantity < $mat->issued_quantity)
                                                            <form method="POST" action="{{ route('master-demo.wo.consume', $wo->id) }}" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="material_id" value="{{ $mat->id }}">
                                                                <input type="hidden" name="quantity" value="{{ $mat->issued_quantity - $mat->actual_quantity }}">
                                                                <button type="submit" class="user-pill" style="border: none; background: rgba(0,0,0,0.3); font-size: 10px; cursor: pointer;">Consume</button>
                                                            </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    
                                    <!-- Report Waste -->
                                    <form method="POST" action="{{ route('master-demo.wo.waste', $wo->id) }}" style="margin-bottom: 12px; display: flex; gap: 6px; align-items: center; background: rgba(239,68,68,0.05); padding: 8px; border-radius: 4px; border: 1px dashed var(--danger);">
                                        @csrf
                                        <select name="product_id" class="form-control" style="font-size: 10px; padding: 4px; flex: 2;" required>
                                            <option value="">Lapor Kegagalan...</option>
                                            @foreach($wo->materials as $mat)
                                                <option value="{{ $mat->product_id }}">{{ $mat->product?->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="type" class="form-control" style="font-size: 10px; padding: 4px; flex: 1.5;" required>
                                            <option value="waste">Waste (Pemborosan)</option>
                                            <option value="reject">Reject (Cacat)</option>
                                            <option value="scrap">Scrap (Sisa)</option>
                                        </select>
                                        <input type="number" step="0.1" name="quantity" class="form-control" placeholder="Qty" style="font-size: 10px; padding: 4px; flex: 1;" required>
                                        <input type="text" name="reason" class="form-control" placeholder="Alasan" style="font-size: 10px; padding: 4px; flex: 2;" required>
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 10px;">Lapor</button>
                                    </form>

                                    <!-- Complete WO -->
                                    <form method="POST" action="{{ route('master-demo.wo.complete', $wo->id) }}" style="text-align: right;">
                                        @csrf
                                        <input type="hidden" name="completed_quantity" value="{{ $wo->planned_quantity }}">
                                        <button type="submit" class="btn btn-outline" style="background: var(--success); color: white; border: none; font-size: 12px;"><i class="fa-solid fa-check-double"></i> Selesai ({{ $wo->planned_quantity }} unit)</button>
                                    </form>
                                @else
                                    <div style="font-size: 12px; color: var(--success);"><i class="fa-solid fa-check"></i> Produksi selesai: {{ $wo->completed_quantity }} unit.</div>
                                    @if($wo->wastes->count() > 0)
                                        <div style="font-size: 11px; color: var(--danger); margin-top: 4px;">Terjadi {{ $wo->wastes->count() }} pemborosan (waste) selama produksi.</div>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 16px;">Belum ada Work Order.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- PURCHASING VIEW -->
        <div id="view-purchasing" class="view-section">
            @include('purchasing.index')
        </div>
        
        <!-- POS VIEW -->
        <!-- HIERARKI ORGANISASI VIEW -->
        <div id="view-hierarchy" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h2>Struktur Organisasi</h2>
                    <p class="desc" style="margin-top: 4px;">Informasi departemen, divisi, dan jalur pelaporan perusahaan.</p>
                </div>
            </div>

            <style>
                .division-accordion {
                    background: var(--panel-bg);
                    border: 1px solid var(--panel-border);
                    border-radius: 12px;
                    margin-bottom: 12px;
                    overflow: hidden;
                }
                .division-summary {
                    padding: 16px 20px;
                    background: var(--panel-secondary);
                    font-weight: bold;
                    font-size: 16px;
                    color: var(--text-heading);
                    cursor: pointer;
                    list-style: none;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .division-summary::-webkit-details-marker { display: none; }
                .division-summary:hover { background: rgba(59, 130, 246, 0.05); }
                .division-content {
                    padding: 24px;
                    display: flex;
                    flex-direction: column;
                    gap: 24px;
                }
                .org-tier {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                .tier-label {
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    color: var(--text-muted);
                    font-weight: bold;
                    border-bottom: 1px dashed var(--panel-border);
                    padding-bottom: 8px;
                }
                .tier-cards {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 16px;
                }
                /* Ensure staffs wrap properly */
                .tier-cards.staff-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                }
            </style>

            @php
                $allUsersHierarchyStaff = \App\Models\User::where('company_id', $user->company_id)->where('is_active', true)->get();
                
                $owners = $allUsersHierarchyStaff->filter(fn($u) => strtolower($u->role) === 'owner');
                $ceos = $allUsersHierarchyStaff->filter(fn($u) => strtolower($u->role) === 'ceo' || str_contains(strtolower($u->job_title ?? ''), 'ceo'));
                
                $staffUnderCeo = $allUsersHierarchyStaff->reject(function($u) use ($owners, $ceos) {
                    return $owners->contains('id', $u->id) || $ceos->contains('id', $u->id);
                });
                
                $divisionsStaff = $staffUnderCeo->groupBy(function($u) {
                    return $u->division ?? $u->divisionLabel();
                });
                
                // Urutkan: Divisi user sendiri di paling atas
                $myDivision = $user->division ?? $user->divisionLabel();
                $myDivData = $divisionsStaff->pull($myDivision);
                if ($myDivData) {
                    $divisionsStaff->prepend($myDivData, $myDivision);
                }
                
                if (!function_exists('renderVerticalCard')) {
                    function renderVerticalCard($u, $currentUser, $isDummy = false, $dummyName = '', $dummyRole = '') {
                        if ($isDummy) {
                            $html = '<div class="org-card" style="width: 280px; border-color: rgba(245,158,11,0.5); border-style: dashed; background: rgba(245,158,11,0.05); margin: 0;">';
                            $html .= '<div class="org-avatar" style="background: #f59e0b; opacity: 0.7;"><i class="fa-solid fa-crown"></i></div>';
                            $html .= '<strong style="font-size: 15px; color: var(--text-heading); display: block;">'.$dummyName.'</strong>';
                            $html .= '<span style="font-size: 12px; color: var(--text-muted); font-style: italic;">'.$dummyRole.'</span>';
                            $html .= '</div>';
                            return $html;
                        }
                    
                        $isMe = ($currentUser->id == $u->id);
                        $borderStyle = $isMe ? 'border-color: var(--text-accent); box-shadow: 0 0 20px rgba(59,130,246,0.3); z-index: 10;' : 'border-color: rgba(12, 53, 39,0.3);';
                        
                        $initials = $u->getInitials();
                        $name = $u->name;
                        $title = $u->job_title ?? $u->role;
                        
                        $html = '<div class="org-card ' . ($isMe ? 'my-card-highlight' : '') . '" style="width: 280px; margin: 0; '.$borderStyle.'">';
                        $html .= '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">';
                        $html .= '<div class="org-avatar" style="margin: 0; '.($isMe ? 'background: #0C3527;' : '').'">'.$initials.'</div>';
                        $html .= '<div style="text-align: left;">';
                        $html .= '<strong style="font-size: 14px; color: var(--text-heading); display: block; line-height: 1.2;">'.$name.' ' . ($isMe ? '<i class="fa-solid fa-circle-check" style="color: var(--text-accent); font-size: 12px;" title="Ini Anda"></i>' : '') . '</strong>';
                        $html .= '<span style="font-size: 12px; color: var(--text-muted);">'.$title.'</span>';
                        $html .= '</div>';
                        $html .= '</div>';
                        
                        $html .= '<div class="org-badges" style="justify-content: flex-start;">';
                        $html .= '<span class="org-badge badge-role" style="'.($isMe ? 'font-weight: bold; background: rgba(59,130,246,0.2); color: var(--text-accent);' : '').'">'.strtoupper(explode("_", $u->role)[0]).'</span>';
                        if ($isMe) {
                            $tasks = $u->tasks()->where('status', 'pending')->count();
                            if ($tasks > 0) {
                                $html .= '<span class="org-badge badge-task-active">'.$tasks.' tugas aktif</span>';
                            } else {
                                $html .= '<span class="org-badge badge-task">On Track</span>';
                            }
                        }
                        $html .= '</div>';
                        
                        if ($isMe) {
                            $html .= '<div style="margin-top: 16px; padding-top: 12px; border-top: 1px dashed var(--panel-border); display: flex; gap: 8px;">';
                            $html .= '<button class="user-pill" onclick="alert(\'Menampilkan popup ubah password\')" style="flex: 1; background: rgba(59,130,246,0.1); color: var(--text-accent); border: none; padding: 6px; font-size: 11px; font-weight: bold;"><i class="fa-solid fa-key"></i> Password</button>';
                            $html .= '<button class="user-pill" onclick="alert(\'Menampilkan statistik performa Anda\')" style="flex: 1; background: rgba(12, 53, 39,0.1); color: var(--text-accent); border: none; padding: 6px; font-size: 11px; font-weight: bold;"><i class="fa-solid fa-chart-line"></i> Performa</button>';
                            $html .= '</div>';
                        }
                        
                        $html .= '</div>';
                        return $html;
                    }
                }
            @endphp

            <div class="org-vertical-container">
                
                <!-- EXECUTIVE BOARD -->
                <details class="division-accordion" open>
                    <summary class="division-summary">
                        <span><i class="fa-solid fa-building" style="color: var(--accent); margin-right: 8px;"></i> Executive Board</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 12px; color: var(--text-muted);"></i>
                    </summary>
                    <div class="division-content" style="background: rgba(0,0,0,0.02);">
                        
                        <!-- Owner Tier -->
                        <div class="org-tier">
                            <span class="tier-label">Owner / Board of Directors</span>
                            <div class="tier-cards">
                                @if($owners->isNotEmpty())
                                    @foreach($owners as $owner)
                                        {!! renderVerticalCard($owner, $user) !!}
                                    @endforeach
                                @else
                                    {!! renderVerticalCard(null, $user, true, 'Board of Directors', 'Owner / Shareholder') !!}
                                @endif
                            </div>
                        </div>

                        <!-- CEO Tier -->
                        <div class="org-tier">
                            <span class="tier-label">Chief Executive Officer</span>
                            <div class="tier-cards">
                                @if($ceos->isNotEmpty())
                                    @foreach($ceos as $ceo)
                                        {!! renderVerticalCard($ceo, $user) !!}
                                    @endforeach
                                @else
                                    {!! renderVerticalCard(null, $user, true, 'Chief Executive', 'CEO') !!}
                                @endif
                            </div>
                        </div>

                    </div>
                </details>

                <!-- DIVISIONS -->
                @foreach($divisionsStaff as $divName => $divUsers)
                    @php
                        $isMyDiv = ($divName === $myDivision);
                        
                        $managers = collect();
                        $spvs = collect();
                        $pics = collect();
                        $staffs = collect();
                        
                        foreach($divUsers as $u) {
                            $title = strtolower($u->job_title ?? '');
                            $role = strtolower($u->role ?? '');
                            
                            if (str_starts_with($role, 'mgr_') && !str_contains($title, 'supervisor') && !str_contains($title, 'spv') && !str_contains($title, 'pic')) {
                                $managers->push($u);
                            } elseif (str_contains($title, 'supervisor') || str_contains($title, 'spv')) {
                                $spvs->push($u);
                            } elseif (str_contains($title, 'pic') || str_contains($title, 'coordinator') || str_contains($title, 'lead')) {
                                $pics->push($u);
                            } else {
                                $staffs->push($u);
                            }
                        }
                    @endphp

                    <details class="division-accordion" {{ $isMyDiv ? 'open' : '' }}>
                        <summary class="division-summary">
                            <span>
                                <i class="fa-solid fa-layer-group" style="color: var(--accent); margin-right: 8px;"></i> 
                                {{ $divName ?: 'Unassigned Division' }}
                                @if($isMyDiv)
                                    <span style="background: rgba(12, 53, 39,0.2); color: var(--text-accent); font-size: 11px; padding: 2px 8px; border-radius: 12px; margin-left: 8px;">Divisi Anda</span>
                                @endif
                            </span>
                            <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">{{ $divUsers->count() }} orang <i class="fa-solid fa-chevron-down" style="margin-left: 8px;"></i></span>
                        </summary>
                        
                        <div class="division-content">
                            <!-- Manager Tier -->
                            <div class="org-tier">
                                <span class="tier-label">Manager</span>
                                <div class="tier-cards">
                                    @if($managers->isNotEmpty())
                                        @foreach($managers as $mgr)
                                            {!! renderVerticalCard($mgr, $user) !!}
                                        @endforeach
                                    @else
                                        {!! renderVerticalCard(null, $user, true, 'Direct Report to CEO', 'Interim Oversight') !!}
                                    @endif
                                </div>
                            </div>

                            <!-- SPV Tier -->
                            @if($spvs->isNotEmpty())
                            <div class="org-tier">
                                <span class="tier-label">Supervisor</span>
                                <div class="tier-cards">
                                    @foreach($spvs as $spv)
                                        {!! renderVerticalCard($spv, $user) !!}
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- PIC Tier -->
                            @if($pics->isNotEmpty())
                            <div class="org-tier">
                                <span class="tier-label">PIC / Coordinator</span>
                                <div class="tier-cards">
                                    @foreach($pics as $pic)
                                        {!! renderVerticalCard($pic, $user) !!}
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Staff Tier -->
                            @if($staffs->isNotEmpty())
                            <div class="org-tier">
                                <span class="tier-label">Staff</span>
                                <div class="tier-cards staff-grid">
                                    @foreach($staffs as $stf)
                                        {!! renderVerticalCard($stf, $user) !!}
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </details>
                @endforeach

            </div>
        </div>
        <div id="view-pos" class="view-section">
            @php
                $activeSession = \App\Models\PosSession::where('cashier_id', $user->id)->where('status', 'open')->first();
            @endphp
            
            @if(!$activeSession)
                <div class="card" style="max-width: 400px; margin: 40px auto; text-align: center;">
                    <i class="fa-solid fa-cash-register" style="font-size: 48px; color: var(--accent); margin-bottom: 16px;"></i>
                    <h3 style="margin-bottom: 8px;">Buka Sesi Kasir</h3>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">Anda harus membuka sesi sebelum dapat melakukan transaksi penjualan.</p>
                    
                    <form method="post" action="{{ route('master-demo.pos.open') }}">
                        @csrf
                        <div class="form-group" style="text-align: left; margin-bottom: 16px;">
                            <label>Uang Modal/Laci Awal (Rp)</label>
                            <input type="number" name="opening_cash" class="form-control" placeholder="Contoh: 50000" required>
                        </div>
                        <button type="submit" class="btn" style="width: 100%; justify-content: center; padding: 12px; font-size: 15px;"><i class="fa-solid fa-lock-open"></i> Buka Sesi (Mulai Shift)</button>
                    </form>
                </div>
            @else
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h3 style="margin: 0;">Mesin Kasir (POS)</h3>
                        <div style="font-size: 12px; color: var(--success);"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Sesi Aktif: Sejak {{ $activeSession->opened_at->format('H:i') }}</div>
                    </div>
                    <form method="post" action="{{ route('master-demo.pos.close') }}">
                        @csrf
                        <input type="hidden" name="closing_cash" value="0" id="closing_cash_input">
                        <button type="button" onclick="closePosSession()" class="user-pill" style="background: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid var(--danger); cursor: pointer;"><i class="fa-solid fa-lock"></i> Akhiri Sesi (Tutup Kasir)</button>
                    </form>
                </div>
                
                <div class="grid-2">
                    <!-- Product Catalog (Right side conceptually) -->
                    <div class="card">
                        <h4 style="margin: 0 0 16px 0; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px;">Katalog Produk (Barang Jadi)</h4>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            @foreach(\App\Models\Product::where('company_id', $user->company_id ?? 1)->where('standard_cost', '>', 0)->get() as $product)
                            <div style="background: var(--bg-main); border: 1px solid var(--panel-border); border-radius: 8px; padding: 12px; cursor: pointer; transition: all 0.2s;" class="pos-item" onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->standard_cost * 2 }})">
                                <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">{{ $product->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $product->sku }}</div>
                                <div style="color: var(--accent); font-weight: bold; margin-top: 8px;">Rp {{ number_format($product->standard_cost * 2, 0, ',', '.') }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Cart & Payment (Left side conceptually) -->
                    <div class="card" style="display: flex; flex-direction: column; height: calc(100vh - 220px);">
                        <h4 style="margin: 0 0 16px 0; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px;">Keranjang Belanja</h4>
                        
                        <div id="pos-cart-items" style="flex: 1; overflow-y: auto; margin-bottom: 16px;">
                            <!-- Cart items injected via JS -->
                            <div style="text-align: center; color: var(--text-muted); font-size: 13px; margin-top: 40px;" id="pos-empty-state">
                                <i class="fa-solid fa-basket-shopping" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                                <div>Keranjang masih kosong.<br>Pilih produk di katalog.</div>
                            </div>
                        </div>
                        
                        <div style="border-top: 1px dashed var(--panel-border); padding-top: 16px;">
                            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                                <span>Subtotal</span>
                                <span id="pos-subtotal">Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 20px; color: var(--accent); margin-bottom: 16px;">
                                <span>Total Tagihan</span>
                                <span id="pos-total">Rp 0</span>
                            </div>
                            
                            <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                                <button type="button" class="btn" style="flex: 1; justify-content: center; background: var(--panel-border); color: var(--text-main);" onclick="setPaymentMethod('cash')" id="btn-pay-cash">TUNAI (CASH)</button>
                                <button type="button" class="btn" style="flex: 1; justify-content: center; background: var(--panel-border); color: var(--text-main);" onclick="setPaymentMethod('qris')" id="btn-pay-qris">QRIS / TRANSFER</button>
                            </div>
                            
                            <button type="button" class="btn" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px; font-weight: bold;" onclick="processSale()" id="btn-process-sale" disabled><i class="fa-solid fa-print"></i> Bayar & Cetak Struk</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- GOALS & KPI VIEW (Manager Only) -->
        @if($user->isManager())
        <div id="view-goals" class="view-section">
            <div class="content-header" style="margin-bottom: 24px;">
                <div>
                    <h2>Setup KPI Tim</h2>
                    <p class="desc">Turunkan Corporate Goals dari CEO menjadi KPI/Task yang spesifik untuk tim Anda.</p>
                </div>
                <button class="btn" style="background: var(--accent);" onclick="document.getElementById('modal-add-kpi').style.display='flex'">
                    <i class="fa-solid fa-plus"></i> Set KPI Baru
                </button>
            </div>
            
            <div class="card" style="margin-bottom: 24px; padding: 24px;">
                <h3 style="margin-bottom: 16px;">Corporate Goals Anda (Dari CEO)</h3>
                @php
                    $myGoals = \App\Models\Task::where('user_id', $user->id)
                        ->whereNull('parent_id')
                        ->get();
                @endphp
                @if($myGoals->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($myGoals as $goal)
                            <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); border-radius: 8px; padding: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <strong style="font-size: 16px;">{{ $goal->title }}</strong>
                                    <span class="org-badge badge-role">{{ $goal->status }}</span>
                                </div>
                                <p class="desc" style="font-size: 13px; margin-bottom: 12px;">{{ $goal->description ?? 'Tidak ada deskripsi' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="desc" style="text-align: center; padding: 24px;">Anda belum mendapatkan Corporate Goals dari CEO.</p>
                @endif
            </div>

            <div class="card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">KPI Tim / Breakdown Task</h3>
                @php
                    $myStaffIds = \App\Models\User::where('company_id', $user->company_id)
                        ->where(function($q) use ($user) {
                            $q->where('reports_to_id', $user->id)
                              ->orWhere(function($sq) use ($user) {
                                  $sq->whereNull('reports_to_id')->where('division', $user->division);
                              });
                        })
                        ->where('id', '!=', $user->id)
                        ->pluck('id');
                        
                    $teamKpis = \App\Models\Task::whereIn('user_id', $myStaffIds)
                        ->whereNotNull('parent_id')
                        ->get();
                @endphp
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>KPI / TASK</th>
                            <th>STAFF</th>
                            <th>TERKAIT GOAL</th>
                            <th>DEADLINE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamKpis as $kpi)
                        <tr>
                            <td>{{ $kpi->title }}</td>
                            <td>{{ $kpi->user->name ?? 'N/A' }}</td>
                            <td>{{ $kpi->parent->title ?? 'N/A' }}</td>
                            <td>{{ $kpi->deadline ? $kpi->deadline->format('d M Y') : 'N/A' }}</td>
                            <td><span class="org-badge badge-role" style="font-size:10px;">{{ $kpi->status }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px;" class="desc">Belum ada KPI/Task yang dibuat untuk tim.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Add KPI -->
        <div id="modal-add-kpi" class="modal-overlay">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0;">Set KPI / Breakdown Task</h4>
                    <button onclick="document.getElementById('modal-add-kpi').style.display='none'" style="background: none; border: none; color: var(--text-main); cursor: pointer; font-size: 16px;">&times;</button>
                </div>
                <form method="POST" action="{{ route('master-demo.tasks.store') ?? '#' }}">
                    @csrf
                    <div class="form-group">
                        <label>Terkait Corporate Goal (CEO)</label>
                        <select name="parent_id" class="form-control" required>
                            <option value="">-- Pilih Goal --</option>
                            @foreach($myGoals ?? [] as $goal)
                                <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Judul KPI / Task</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tugaskan Kepada (Staf Anda)</label>
                        <select name="username" class="form-control" required>
                            @php
                                $myStaffsList = \App\Models\User::whereIn('id', $myStaffIds ?? [])->get();
                            @endphp
                            @foreach($myStaffsList as $stf)
                                <option value="{{ $stf->username }}">{{ $stf->name }} ({{ $stf->job_title ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Deadline (Opsional)</label>
                        <input type="date" name="deadline" class="form-control">
                    </div>
                    <button type="submit" class="btn" style="width: 100%; justify-content: center; background: var(--accent);">
                        <i class="fa-solid fa-paper-plane"></i> Tetapkan KPI
                    </button>
                </form>
            </div>
        </div>
        @endif

    </main>

    <!-- Modal Edit User Profile (Hierarchy) -->
    <div id="modal-edit-user" class="modal-overlay">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="margin: 0;">Edit Profil & Akses</h4>
                <button onclick="document.getElementById('modal-edit-user').style.display='none'" style="background: none; border: none; color: var(--text-main); cursor: pointer; font-size: 16px;">&times;</button>
            </div>
            <form method="POST" action="{{ route('master-demo.hris.updateUser') ?? '#' }}">
                @csrf
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="edit-user-name" class="form-control" disabled>
                </div>
                <div style="display: flex; gap: 16px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Jabatan (Role)</label>
                        <input type="text" name="job_title" id="edit-user-job" class="form-control">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Tipe Pekerjaan</label>
                        <select name="employment_type" id="edit-user-type" class="form-control">
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                            <option value="Paid Internship">Paid Internship</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Target Jam Kerja per Bulan</label>
                    <input type="number" step="1" name="target_hours_per_month" id="edit-user-target" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Reset Password (Opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                </div>
                <button type="submit" class="btn" style="width: 100%; justify-content: center; background: var(--accent);">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Global Confirm Modal -->
    <div id="modal-confirm" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 40px; color: var(--danger); margin-bottom: 16px;"></i>
            <h3 id="confirm-title" style="margin-bottom: 8px;">Konfirmasi</h3>
            <p id="confirm-msg" class="desc" style="margin-bottom: 24px;">Apakah Anda yakin?</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="user-pill" onclick="document.getElementById('modal-confirm').style.display='none'" style="background: var(--panel-border); border: none; padding: 10px 24px; cursor: pointer;">Batal</button>
                <form id="confirm-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="confirm-method" value="POST">
                    <button type="submit" class="user-pill" id="confirm-btn" style="background: var(--danger); color: white; border: none; padding: 10px 24px; cursor: pointer; font-weight: bold;">Ya, Lanjutkan</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openEditProfileModal(id, name, job, type, target) {
            document.getElementById('edit-user-id').value = id;
            document.getElementById('edit-user-name').value = name;
            document.getElementById('edit-user-job').value = job;
            document.getElementById('edit-user-type').value = type;
            document.getElementById('edit-user-target').value = target;
            document.getElementById('modal-edit-user').style.display = 'flex';
        }
        
        function confirmDeleteUser(id) {
            document.getElementById('confirm-title').innerText = "Hapus Staf";
            document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin menonaktifkan/menghapus staf ini dari sistem?";
            document.getElementById('confirm-form').action = "/master-demo/employee/" + id + "/delete"; 
            document.getElementById('modal-confirm').style.display = 'flex';
        }
        
        function confirmLogout() {
            document.getElementById('confirm-title').innerText = "Logout";
            document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin keluar dari sistem?";
            document.getElementById('confirm-form').action = "/master-demo/logout"; 
            document.getElementById('modal-confirm').style.display = 'flex';
        }

        function switchView(viewId) {
            // Update Nav
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            const targetNav = Array.from(document.querySelectorAll('.nav-item')).find(el => el.getAttribute('onclick') === `switchView('${viewId}')`);
            if(targetNav) targetNav.classList.add('active');

            // Hide all views, show target
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.getElementById(`view-${viewId}`).classList.add('active');
            
            // Update Title
            const titles = {
                'dashboard': ['Dashboard', '{{ $user->isManager() ? "Kelola tim dan pantau performa divisi " . $user->divisionLabel() . "." : "Overview performa kerja Anda." }}'],
                'attendance': ['Attendance', 'Clock in/out dan riwayat kehadiran Anda.'],
                'chat': ['Internal Chat', 'Diskusi antar tim.'],
                'tasks': ['My Tasks', 'Target KPI Anda.'],
                'payslips': ['Payslips', 'Riwayat gaji bulanan.'],
                'profile': ['Profil & Pengaturan Akun', 'Kelola data diri dan keamanan akun Anda.'],
                'purchasing': ['Purchasing', 'Pengajuan pembelian dan penerimaan barang.'],
                'pos': ['Mesin Kasir (POS)', 'Point of Sale untuk transaksi penjualan.'],
                'team-attendance': ['Attendance Tim', 'Riwayat kehadiran seluruh anggota tim Anda.']
            };
            if (titles[viewId]) {
                document.getElementById('view-title').innerText = titles[viewId][0];
                document.getElementById('view-subtitle').innerText = titles[viewId][1];
            }
            
            if (viewId === 'purchasing') {
                if (typeof window.purchasingApp !== 'undefined') window.purchasingApp.init();
            }
        }

        // Theme Management
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sikarya_theme', next);
        }

        function setPrimaryColor(color) {
            document.documentElement.style.setProperty('--accent', color);
            document.documentElement.style.setProperty('--accent-hover', color);
            document.documentElement.style.setProperty('--accent-active', color);
            localStorage.setItem('sikarya_color', color);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Theme Controls
            const savedColor = localStorage.getItem('sikarya_color');
            if(savedColor) {
                const picker = document.getElementById('theme-color-picker');
                if(picker) picker.value = savedColor;
            }
            
            switchView('dashboard');
            fetchEmployeeChannels();
            loadChat('general');
        });
        
        let currentChannel = 'general';
        let currentUserId = {{ auth()->id() }};
        let employeeChatInterval = null;
        
        async function fetchEmployeeChannels() {
            try {
                const res = await fetch('/master-demo/chat/channels/list');
                if(res.ok) {
                    const data = await res.json();
                    const container = document.getElementById('employee-chat-divisions');
                    if(!container) return;
                    
                    let html = '<div style="padding: 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border);">Grup Diskusi</div>';
                    html += `<div class="channel-item" onclick="loadChat('general', event)" id="emp-chat-general">
                                <i class="fa-solid fa-bullhorn"></i> Pengumuman General
                             </div>`;
                             
                    if(data.divisions && data.divisions.length > 0) {
                        data.divisions.forEach(d => {
                            const id = 'emp-chat-' + d.name.replace(/\s+/g, '-');
                            html += `<div class="channel-item" onclick="loadChat('${d.name}', event)" id="${id}">
                                        <i class="fa-solid fa-users"></i> Grup ${d.name}
                                     </div>`;
                        });
                    }
                    if(data.custom && data.custom.length > 0) {
                        html += `<div style="padding: 16px 16px 8px 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border); color: var(--text-muted); font-size: 12px; border-top: 1px solid var(--panel-border);">Grup Kustom</div>`;
                        data.custom.forEach(c => {
                            const id = 'emp-chat-' + c.name.replace(/\s+/g, '-');
                            html += `<div class="channel-item" onclick="loadChat('${c.name}', event)" id="${id}">
                                        <i class="fa-solid fa-hashtag"></i> ${c.name}
                                     </div>`;
                        });
                    }
                    container.innerHTML = html;
                    
                    if(currentChannel) {
                        document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
                        const activeEl = document.getElementById('emp-chat-' + currentChannel.replace(/\s+/g, '-'));
                        if(activeEl) activeEl.classList.add('active');
                    }
                }
            } catch(err) { console.error('Error fetching channels', err); }
        }

        async function loadChat(channel, event) {
            currentChannel = channel;
            document.getElementById('active-channel-name').innerText = channel;
            
            if(event) {
                document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
                event.currentTarget.classList.add('active');
            } else {
                // Try to highlight if event not provided
                document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
                const activeEl = document.getElementById('emp-chat-' + channel.replace(/\s+/g, '-'));
                if(activeEl) activeEl.classList.add('active');
            }
            
            fetchChatData();
            
            if(!employeeChatInterval) {
                employeeChatInterval = setInterval(fetchChatData, 3000);
            }
        }
        
        async function fetchChatData() {
            if(!document.getElementById('view-chat').classList.contains('active')) return;
            
            const container = document.getElementById('chat-messages-container');
            
            try {
                const response = await fetch('/master-demo/chat/' + currentChannel);
                const messages = await response.json();
                
                let html = '';
                if(messages.length === 0) {
                    html = '<div style="text-align: center; color: var(--text-muted); font-size: 12px; margin-bottom: 10px;">Belum ada pesan. Mulai percakapan!</div>';
                }
                
                messages.forEach(msg => {
                    const isMine = msg.sender_id === currentUserId;
                    const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const senderName = msg.sender ? msg.sender.name : 'Unknown';
                    const initial = senderName.charAt(0).toUpperCase();
                    
                    html += `
                        <div class="message ${isMine ? 'mine' : ''}">
                            <div class="msg-avatar">${initial}</div>
                            <div>
                                <div class="msg-meta" style="${isMine ? 'text-align: right;' : ''}">${senderName} • ${time}</div>
                                <div class="msg-bubble">${msg.message || ''}</div>
                            </div>
                        </div>
                    `;
                });
                
                // Only scroll if we added new messages, or keep it simple: always set innerHTML
                // A better approach for a real app is to diff, but we'll do this for now:
                const isAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                container.innerHTML = html;
                if(isAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (e) {
                console.error(e);
            }
        }
        
        async function sendMessage() {
            const input = document.getElementById('chat-input-box');
            const btn = document.getElementById('emp-chat-send-btn');
            const message = input.value.trim();
            if(!message) return;
            
            input.value = '';
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            try {
                await fetch('/master-demo/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        channel: currentChannel,
                        message: message
                    })
                });
                fetchChatData();
            } catch (e) {
                console.error(e);
                fetchChatData();
            } finally {
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
                btn.disabled = false;
            }
        }
        
        document.getElementById('chat-input-box').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
        <script>
        // POS JavaScript Logic
        let posCart = [];
        let posPaymentMethod = '';
        
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }
        
        function closePosSession() {
            const cash = prompt("Masukkan total uang fisik (tunai) di laci kasir saat ini untuk rekonsiliasi:");
            if(cash !== null && cash !== "") {
                document.getElementById('closing_cash_input').value = cash;
                document.getElementById('closing_cash_input').form.submit();
            }
        }
        
        function addToCart(productId, name, price) {
            const existingItem = posCart.find(i => i.product_id === productId);
            if(existingItem) {
                existingItem.quantity += 1;
            } else {
                posCart.push({
                    product_id: productId,
                    name: name,
                    unit_price: price,
                    quantity: 1
                });
            }
            renderCart();
        }
        
        function updateQuantity(productId, delta) {
            const item = posCart.find(i => i.product_id === productId);
            if(item) {
                item.quantity += delta;
                if(item.quantity <= 0) {
                    posCart = posCart.filter(i => i.product_id !== productId);
                }
                renderCart();
            }
        }
        
        function setPaymentMethod(method) {
            posPaymentMethod = method;
            document.getElementById('btn-pay-cash').style.background = method === 'cash' ? 'var(--accent)' : 'rgba(255,255,255,0.1)';
            document.getElementById('btn-pay-cash').style.color = method === 'cash' ? 'white' : 'white';
            
            document.getElementById('btn-pay-qris').style.background = method === 'qris' ? 'var(--accent)' : 'rgba(255,255,255,0.1)';
            document.getElementById('btn-pay-qris').style.color = method === 'qris' ? 'white' : 'white';
            
            checkCheckoutBtn();
        }
        
        function checkCheckoutBtn() {
            const btn = document.getElementById('btn-process-sale');
            if(posCart.length > 0 && posPaymentMethod !== '') {
                btn.removeAttribute('disabled');
            } else {
                btn.setAttribute('disabled', 'true');
            }
        }
        
        function renderCart() {
            const container = document.getElementById('pos-cart-items');
            if(posCart.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-muted); font-size: 13px; margin-top: 40px;" id="pos-empty-state">
                        <i class="fa-solid fa-basket-shopping" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <div>Keranjang masih kosong.<br>Pilih produk di katalog.</div>
                    </div>
                `;
                document.getElementById('pos-subtotal').innerText = 'Rp 0';
                document.getElementById('pos-total').innerText = 'Rp 0';
                checkCheckoutBtn();
                return;
            }
            
            let html = '';
            let total = 0;
            
            posCart.forEach(item => {
                const itemTotal = item.quantity * item.unit_price;
                total += itemTotal;
                
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 8px 0;">
                        <div style="flex: 1;">
                            <div style="font-weight: bold; font-size: 13px;">${item.name}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">${formatRupiah(item.unit_price)}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button onclick="updateQuantity(${item.product_id}, -1)" style="background: var(--panel-border); color: var(--text-main); border: none; width: 24px; height: 24px; border-radius: 4px; cursor: pointer;">-</button>
                            <span style="font-size: 13px; font-weight: bold; min-width: 20px; text-align: center;">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.product_id}, 1)" style="background: var(--panel-border); color: var(--text-main); border: none; width: 24px; height: 24px; border-radius: 4px; cursor: pointer;">+</button>
                        </div>
                        <div style="font-weight: bold; font-size: 13px; min-width: 80px; text-align: right;">${formatRupiah(itemTotal)}</div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            document.getElementById('pos-subtotal').innerText = formatRupiah(total);
            document.getElementById('pos-total').innerText = formatRupiah(total);
            checkCheckoutBtn();
        }
        
        async function processSale() {
            if(posCart.length === 0 || !posPaymentMethod) return;
            
            const btn = document.getElementById('btn-process-sale');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i> Memproses...';
            btn.disabled = true;
            
            try {
                const response = await fetch('{{ route("master-demo.pos.sale") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        payment_method: posPaymentMethod,
                        items: JSON.stringify(posCart)
                    })
                });
                
                const data = await response.json();
                if(data.success) {
                    alert(data.message);
                    posCart = []; // Clear cart
                    posPaymentMethod = ''; // Reset payment method
                    document.getElementById('btn-pay-cash').style.background = 'rgba(255,255,255,0.1)';
                    document.getElementById('btn-pay-qris').style.background = 'rgba(255,255,255,0.1)';
                    renderCart();
                } else {
                    alert("Gagal memproses: " + data.message);
                }
            } catch(e) {
                alert("Terjadi kesalahan jaringan.");
            }
            
            btn.innerHTML = '<i class="fa-solid fa-print"></i> Bayar & Cetak Struk';
            checkCheckoutBtn();
        }
        function checkCheckoutBtn() {
            let total = 0;
            for(let id in posCart) {
                total += posCart[id].price * posCart[id].qty;
            }
            if(total > 0) {
                document.getElementById('pos-checkout-btn').disabled = false;
                document.getElementById('pos-checkout-btn').classList.remove('ios-btn-secondary');
                document.getElementById('pos-checkout-btn').classList.add('ios-btn-primary');
            } else {
                document.getElementById('pos-checkout-btn').disabled = true;
                document.getElementById('pos-checkout-btn').classList.remove('ios-btn-primary');
                document.getElementById('pos-checkout-btn').classList.add('ios-btn-secondary');
            }
        }
    </script>
    
    <script>
        // Payslip Security Gate
        function unlockPayslips() {
            const pin = document.getElementById('payslip-pin').value;
            // In a real system, you would verify this via AJAX. 
            // For now, any PIN length >= 4 works as a demo
            if (pin.length >= 4) {
                document.getElementById('payslips-security-gate').style.display = 'none';
                document.getElementById('payslips-content').style.display = 'block';
                document.getElementById('payslip-pin').value = '';
            } else {
                alert('PIN tidak valid. Masukkan minimal 4 digit PIN.');
            }
        }

        // Generate PDF
        function downloadSlipPdf(userId) {
            // Note: Since this is an MVP without dompdf installed yet, 
            // we will simulate the download action or open the print preview.
            window.open(`/master-demo/payroll/slip/${userId}/preview?print=1`, '_blank');
        }
    </script>
@include('components.global-loading')
@include('components.chat-widget')
</body>
</html>

