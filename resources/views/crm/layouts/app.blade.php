<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRM & Customer Portal')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/styles.css?v={{ filemtime(public_path('css/styles.css')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --crm-primary: #0C3527;
            --crm-primary-hover: #124836;
            --crm-primary-active: #08261C;
            --crm-secondary: #D9EFE9;
            --crm-success: #D9EFE9;
            --crm-danger: #DC2626;
            --crm-warning: #F59E0B;
            --crm-info: #0C3527;
            --crm-text-on-primary: #FFFFFF;
            --crm-text-on-secondary: #0C3527;
            --crm-border: #D9EFE9;
            --crm-bg-glass: rgba(255, 255, 255, 0.9);
            --crm-bg-card: rgba(255, 255, 255, 0.95);
            --crm-radius: 18px;
            --crm-shadow: 0 8px 30px rgba(12, 53, 39, 0.08);
            --crm-transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { 
            margin: 0; 
            padding: 0; 
            background: var(--bg-main, #f8fafc); 
            font-family: -apple-system, BlinkMacSystemFont, 'Outfit', sans-serif; 
            color: #1e293b; 
            min-height: 100vh; 
            -webkit-font-smoothing: antialiased;
        }

        .crm-layout { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .crm-sidebar { 
            width: 260px; 
            min-height: 100vh; 
            background: var(--crm-primary); 
            display: flex; 
            flex-direction: column; 
            position: sticky; 
            top: 0; 
            height: 100vh; 
            overflow-y: auto; 
            box-shadow: 4px 0 24px rgba(12, 53, 39, 0.1);
        }
        .sidebar-logo { padding: 32px 24px 24px; }
        .sidebar-logo h1 { margin: 0; font-size: 22px; font-weight: 800; color: var(--crm-text-on-primary); display: flex; align-items: center; gap: 8px; }
        .sidebar-logo p { margin: 6px 0 0; font-size: 11px; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
        
        .sidebar-nav { padding: 16px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .nav-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1px; padding: 0 12px; margin: 20px 0 8px; }
        .nav-item { 
            display: flex; align-items: center; gap: 12px; padding: 12px 14px; 
            border-radius: 12px; color: rgba(255,255,255,0.8); font-size: 13.5px; 
            font-weight: 500; text-decoration: none; cursor: pointer; 
            transition: var(--crm-transition);
        }
        .nav-item:hover { background: rgba(217, 239, 233, 0.1); color: #fff; transform: translateX(4px); }
        .nav-item.active { background: var(--crm-secondary); color: var(--crm-text-on-secondary); font-weight: 600; box-shadow: 0 4px 12px rgba(217, 239, 233, 0.2); }
        .nav-item .nav-icon { font-size: 18px; }
        
        /* Main */
        .crm-main { flex: 1; min-width: 0; padding: 40px; overflow-x: hidden; }
        
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px; }
        .page-title-group h1 { margin: 0; font-size: 28px; font-weight: 700; color: var(--crm-primary); display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px; }
        .page-title-group p { margin: 8px 0 0; color: #64748b; font-size: 14px; }
        
        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: var(--crm-transition); font-family: 'Outfit', sans-serif; }
        .btn-primary { background: var(--crm-primary); color: var(--crm-text-on-primary); box-shadow: 0 4px 12px rgba(12, 53, 39, 0.2); }
        .btn-primary:hover { background: var(--crm-primary-hover); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(12, 53, 39, 0.25); }
        .btn-primary:active { background: var(--crm-primary-active); transform: translateY(0); }
        
        .btn-secondary { background: var(--crm-secondary); color: var(--crm-text-on-secondary); }
        .btn-secondary:hover { filter: brightness(0.95); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(217, 239, 233, 0.4); }
        
        .btn-outline { background: transparent; color: var(--crm-primary); border: 1.5px solid var(--crm-primary); }
        .btn-outline:hover { background: var(--crm-secondary); border-color: var(--crm-secondary); transform: translateY(-2px); }
        
        .btn-danger { background: rgba(220, 38, 38, 0.1); color: var(--crm-danger); }
        .btn-danger:hover { background: var(--crm-danger); color: white; transform: translateY(-2px); }
        
        .btn-ghost { background: transparent; color: #64748b; }
        .btn-ghost:hover { background: rgba(0,0,0,0.05); color: var(--crm-primary); }

        /* Cards */
        .crm-card {
            background: var(--crm-bg-card);
            border: 1px solid var(--crm-border);
            border-radius: var(--crm-radius);
            padding: 24px;
            box-shadow: var(--crm-shadow);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: var(--crm-transition);
        }
        .crm-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(12, 53, 39, 0.12);
        }

        /* Stat Card */
        .stat-card {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            background: var(--crm-secondary);
            color: var(--crm-primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .stat-content { flex: 1; }
        .stat-title { font-size: 13px; color: #64748b; font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--crm-primary); line-height: 1; letter-spacing: -0.5px; }

        /* Tables */
        .table-wrapper {
            background: var(--crm-bg-card);
            border: 1px solid var(--crm-border);
            border-radius: var(--crm-radius);
            overflow: hidden;
            box-shadow: var(--crm-shadow);
        }
        .table-toolbar {
            padding: 16px 24px;
            border-bottom: 1px solid var(--crm-border);
            display: flex; gap: 12px; align-items: center; background: rgba(255,255,255,0.5);
        }
        .crm-table { width: 100%; border-collapse: collapse; text-align: left; }
        .crm-table th {
            background: var(--crm-primary);
            color: white;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .crm-table td {
            padding: 16px 24px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: #334155;
            font-size: 14px;
            transition: var(--crm-transition);
        }
        .crm-table tr:last-child td { border-bottom: none; }
        .crm-table tbody tr:hover td { background: var(--crm-secondary); }
        .crm-table tbody tr.selected td { background: rgba(12,53,39,0.08); }

        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--crm-primary); margin-bottom: 8px; }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            background: rgba(255,255,255,0.7);
            font-family: inherit;
            font-size: 14px;
            color: #1e293b;
            transition: var(--crm-transition);
            outline: none;
        }
        .form-control:focus {
            border-color: var(--crm-primary);
            box-shadow: 0 0 0 4px rgba(12, 53, 39, 0.18);
            background: #fff;
        }

        /* Badges */
        .badge { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-success { background: var(--crm-secondary); color: var(--crm-primary); }
        .badge-danger { background: rgba(220, 38, 38, 0.1); color: var(--crm-danger); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--crm-warning); }
        
        .alert { padding: 16px 20px; border-radius: 14px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: center; gap: 12px; border: 1px solid transparent; }
        .alert-success { background: var(--crm-secondary); color: var(--crm-primary); border-color: rgba(12, 53, 39, 0.1); }
        .alert-error { background: rgba(220, 38, 38, 0.05); color: var(--crm-danger); border-color: rgba(220, 38, 38, 0.2); }

        @yield('styles')
    </style>
</head>
<body>
<div class="crm-layout">
    <aside class="crm-sidebar">
        <div class="sidebar-logo">
            <h1><i class="ph ph-buildings"></i> CRM F&B</h1>
            <p>Customer Portal & Loyalty</p>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="{{ route('crm.dashboard') }}" class="nav-item {{ request()->routeIs('crm.dashboard') ? 'active' : '' }}">
                <i class="ph ph-squares-four nav-icon"></i> Dashboard
            </a>
            <a href="{{ route('crm.customers.index') }}" class="nav-item {{ request()->routeIs('crm.customers.*') ? 'active' : '' }}">
                <i class="ph ph-users nav-icon"></i> Customer
            </a>
            <a href="{{ route('crm.memberships.index') }}" class="nav-item {{ request()->routeIs('crm.memberships.*') ? 'active' : '' }}">
                <i class="ph ph-crown nav-icon"></i> Membership
            </a>
            <a href="{{ route('crm.loyalties.index') }}" class="nav-item {{ request()->routeIs('crm.loyalties.*') ? 'active' : '' }}">
                <i class="ph ph-gift nav-icon"></i> Loyalty
            </a>
            <a href="{{ route('crm.vouchers.index') }}" class="nav-item {{ request()->routeIs('crm.vouchers.*') ? 'active' : '' }}">
                <i class="ph ph-ticket nav-icon"></i> Voucher
            </a>
            <a href="{{ route('crm.reservations.index') }}" class="nav-item {{ request()->routeIs('crm.reservations.*') ? 'active' : '' }}">
                <i class="ph ph-calendar-check nav-icon"></i> Reservation
            </a>
            <a href="{{ route('crm.feedbacks.index') }}" class="nav-item {{ request()->routeIs('crm.feedbacks.*') ? 'active' : '' }}">
                <i class="ph ph-star nav-icon"></i> Feedback
            </a>
            <a href="{{ route('crm.analytics.index') }}" class="nav-item {{ request()->routeIs('crm.analytics.*') ? 'active' : '' }}">
                <i class="ph ph-chart-line-up nav-icon"></i> Analytics
            </a>
            <a href="{{ route('portal.login') }}" class="nav-item" target="_blank">
                <i class="ph ph-browser nav-icon"></i> Customer Portal
            </a>
            
            <div class="nav-label" style="margin-top: 24px;">System</div>
            <a href="/" class="nav-item" style="color: rgba(255,255,255,0.5);">
                <i class="ph ph-arrow-left nav-icon"></i> Back to ERP
            </a>
        </nav>
    </aside>

    <main class="crm-main">
        @if(session('success'))
            <div class="alert alert-success"><i class="ph ph-check-circle" style="font-size: 20px;"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error"><i class="ph ph-warning-circle" style="font-size: 20px;"></i> {{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</div>
@yield('scripts')
@include('components.global-loading')
</body>
</html>
