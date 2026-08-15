<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIKARYA - System Managemen Karyawan</title>
    <style>
        :root {
            --bg-main: #F8FAFC;
            --panel: #FFFFFF;
            --panel-secondary: #F1F5F9;
            --panel-border: #E2E8F0;
            --text-heading: #0F172A;
            --text-main: #334155;
            --text-muted: #64748B;
            --accent: #0C3527;
            --accent-hover: #0C3527;
            --disabled: #CBD5E1;
            --gold: #f59e0b;
        }
        body {
            margin: 0;
            background: var(--bg-main);
            color: var(--text-main);
            font-family: 'Inter', system-ui, Arial, sans-serif;
            min-height: 100vh;
        }
        .wrap { max-width: 1180px; margin: auto; padding: 36px 22px 60px }
        .eyebrow { color: var(--accent); font-weight: 800; letter-spacing: .12em; font-size: 11px; text-transform: uppercase }
        .hero { display: flex; justify-content: space-between; gap: 24px; align-items: end; margin-bottom: 26px }
        .hero h1 { margin: 8px 0; font-size: 32px; color: var(--text-heading); }
        .hero p { color: var(--text-muted); max-width: 670px; line-height: 1.6 }
        .local { border: 1px solid var(--accent); background: rgba(12, 53, 39, 0.1); color: var(--accent-hover); border-radius: 99px; padding: 8px 12px; font-weight: 700 }
        .grid { display: grid; grid-template-columns: 280px 1fr; gap: 18px }
        .card { background: var(--panel); border: 1px solid var(--panel-border); border-radius: 16px; padding: 18px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .card h2 { font-size: 16px; margin: 0 0 14px; color: var(--text-heading); }
        .company { display: block; text-decoration: none; color: var(--text-heading); border: 1px solid var(--panel-border); border-radius: 11px; padding: 12px; margin: 8px 0; background: var(--panel-secondary); transition: all 0.2s; }
        .company:hover { border-color: var(--accent); background: #ffffff; }
        .company.active { border-color: var(--accent); background: rgba(12, 53, 39, 0.05); box-shadow: 0 0 0 2px rgba(12, 53, 39,0.1); }
        .company small, .muted { display: block; color: var(--text-muted); margin-top: 4px }
        .metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px }
        .metric { padding: 14px; border-radius: 12px; background: var(--panel-secondary); border: 1px solid var(--panel-border) }
        .metric strong { font-size: 22px; display: block; margin-top: 6px; color: var(--text-heading); }
        .module { display: grid; grid-template-columns: 1fr 125px; gap: 14px; align-items: center; border-top: 1px solid var(--panel-border); padding: 13px 0 }
        .module:first-child { border-top: 0 }
        .tag { font-size: 11px; font-weight: 800; color: var(--text-muted) }
        .permanent { color: var(--accent) }
        select, button { width: 100%; border-radius: 9px; padding: 9px; border: 1px solid var(--panel-border); background: var(--panel-secondary); color: var(--text-main); font-family: inherit; }
        button { background: var(--accent); color: #ffffff; border: 0; font-weight: 800; cursor: pointer; margin-top: 6px; transition: all 0.2s }
        button:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(12, 53, 39,0.2); }
        .notice { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 11px; border-radius: 10px; margin-bottom: 14px; font-weight: 500; }
        .error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 11px; border-radius: 10px; margin-bottom: 14px; font-weight: 500; }
        .lead { padding: 11px 0; border-top: 1px solid var(--panel-border) }
        .lead:first-child { border: 0 }
        @media(max-width:760px) { .hero, .grid { display: block } .local { display: inline-block } .grid>*, .hero>* { margin-bottom: 16px } .metrics { grid-template-columns: 1fr 1fr } .module { grid-template-columns: 1fr } }
    </style>
</head>
<body><main class="wrap">
    <form method="post" action="{{ route('master-demo.logout') }}" style="position:fixed;top:16px;right:16px;z-index:10;width:120px">@csrf<button type="submit" style="background:#ef4444; color:white; box-shadow:none;">Logout</button></form>
    <a href="{{ route('master-demo.app') }}" style="position:fixed;top:16px;right:150px;z-index:10;background:var(--accent);color:white;padding:10px 14px;border-radius:9px;text-decoration:none;font-weight:800;box-shadow: 0 4px 12px rgba(12, 53, 39,0.2); transition: all 0.2s;">Buka Dashboard ERP</a>
    <section class="hero"><div><img src="{{ asset('images/sikarya-logo.png') }}" style="height:60px; margin-bottom:10px;"><span class="eyebrow" style="display:block;">SIKARYA · Tenant & Module Demo</span><h1>{{ $company->name }}</h1><p>Simulasi Superadmin/CEO untuk produk ERP multi-company. Data dan modul ditampilkan dalam konteks tenant yang dipilih.</p></div><span class="local">LOCALHOST ONLY</span></section>
    @if(session('demo_notice'))<div class="notice">{{ session('demo_notice') }}</div>@endif
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <section class="grid"><aside class="card"><h2>Tenant Demo</h2>@foreach($companies as $item)<a class="company {{ $company->id === $item->id ? 'active' : '' }}" href="{{ route('master-demo', ['company' => $item->id]) }}"><strong>{{ $item->name }}</strong><small>{{ $item->industry }} · {{ $item->currency }}</small></a>@endforeach</aside>
    <div><section class="metrics"><div class="metric"><span class="muted">Tenant status</span><strong>{{ strtoupper($company->status) }}</strong></div><div class="metric"><span class="muted">Contoh data CRM</span><strong>{{ $company->leads_count }}</strong></div><div class="metric"><span class="muted">Lokasi</span><strong>{{ $company->timezone }}</strong></div></section>
    <section class="card"><h2>Module Control Center</h2><span class="muted">Core selalu aktif. Modul lain dapat dijadikan Active, Read-only, atau Off. Dependency diuji saat aktivasi.</span>@foreach($features as $module)<div class="module"><div><strong>{{ $module['label'] }}</strong><span class="tag {{ $module['permanent'] ? 'permanent' : '' }}">{{ $module['group'] }} · {{ $module['permanent'] ? 'PERMANENT' : ('Dependency: '.(count($module['dependencies']) ? implode(', ', $module['dependencies']) : 'none')) }}</span></div><div>@if($module['permanent'])<span class="local">ACTIVE</span>@else<form method="post" action="{{ route('master-demo.feature', ['company' => $company->id, 'feature' => $module['key']]) }}">@csrf @method('patch')<select name="state" onchange="this.form.submit()" style="cursor:pointer; font-weight:bold; background-color: {{ $module['state'] === 'active' ? 'var(--blue)' : ($module['state'] === 'off' ? '#2c1e1e' : '#1e2532') }}">@foreach(['active' => 'Active','read_only' => 'Read-only','off' => 'Off'] as $value => $label)<option value="{{ $value }}" @selected($module['state'] === $value)>{{ $label }}</option>@endforeach</select><noscript><button>Simpan</button></noscript></form>@endif</div></div>@endforeach</section>
    <section class="card" style="margin-top:18px"><h2>Contoh Data Terisolasi — CRM</h2>@forelse($leads as $lead)<div class="lead"><strong>{{ $lead->client_name }}</strong><span class="muted">{{ $lead->status }} · Rp {{ number_format((float) $lead->project_value, 0, ',', '.') }} · {{ $lead->source }}</span></div>@empty<div class="muted">Belum ada data untuk tenant ini.</div>@endforelse</section>
    </div></section>
@include('components.global-loading')
</body></html>
