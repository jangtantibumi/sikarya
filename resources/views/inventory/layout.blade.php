<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Manajemen Gudang & Stok') - Suba ERP</title>
    <script>
        // Check theme preference before Tailwind loads
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        erp: {
                            green: '#0C3527',
                            dark: '#0f1115',
                            card: '#1a1d24',
                            border: '#2a2e37'
                        }
                    }
                }
            }
        }
        
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; transition: background-color 0.3s, color 0.3s; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: background-color 0.3s, border-color 0.3s;
        }
        .dark .glass-panel {
            background: rgba(26, 29, 36, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(12, 53, 39, 0.12);
            color: #0C3527;
            border-left: 3px solid #0C3527;
        }
        .dark .sidebar-link:hover, .dark .sidebar-link.active {
            color: #D9EFE9;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #2a2e37; }
        ::-webkit-scrollbar-thumb:hover { background: #0C3527; }
    </style>
</head>
<body class="flex h-screen overflow-hidden antialiased bg-gray-50 dark:bg-erp-dark text-gray-900 dark:text-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 glass-panel border-r border-gray-200 dark:border-erp-border flex flex-col h-full z-20 overflow-y-auto">
        <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-erp-border sticky top-0 bg-white dark:bg-erp-card z-30">
            <i class="ph ph-hexagon-fill text-erp-green text-2xl mr-3"></i>
            <div>
                <span class="font-bold text-lg tracking-wide text-gray-900 dark:text-white block">Suba ERP</span>
                <span class="text-xs text-erp-green font-medium">Sistem Manajemen Gudang</span>
            </div>
        </div>

        <div class="p-3 space-y-6">
            <!-- MAIN -->
            <div>
                <div class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Main</div>
                <nav class="space-y-0.5">
                    <a href="{{ route('inventory.dashboard') }}" class="sidebar-link {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-squares-four text-base mr-2.5"></i> Dashboard
                    </a>
                </nav>
            </div>

            <!-- MASTER DATA -->
            <div>
                <div class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Data Dasar (Master Data)</div>
                <nav class="space-y-0.5">
                    <a href="{{ route('inventory.items.index') }}" class="sidebar-link {{ request()->routeIs('inventory.items.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-box-cube text-base mr-2.5"></i> Daftar Produk / Barang
                    </a>
                    <a href="{{ route('inventory.categories.index') }}" class="sidebar-link {{ request()->routeIs('inventory.categories.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-folder text-base mr-2.5"></i> Kategori Produk
                    </a>
                    <a href="{{ route('inventory.brands.index') }}" class="sidebar-link {{ request()->routeIs('inventory.brands.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-tag text-base mr-2.5"></i> Merek (Brands)
                    </a>
                    <a href="{{ route('inventory.uoms.index') }}" class="sidebar-link {{ request()->routeIs('inventory.uoms.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-ruler text-base mr-2.5"></i> Satuan Ukur (Pcs, Kg)
                    </a>
                    <a href="{{ route('inventory.warehouses.index') }}" class="sidebar-link {{ request()->routeIs('inventory.warehouses.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-warehouse text-base mr-2.5"></i> Daftar Gudang
                    </a>
                    <a href="{{ route('inventory.locations.index') }}" class="sidebar-link {{ request()->routeIs('inventory.locations.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-map-pin text-base mr-2.5"></i> Lokasi (Rak/Zona)
                    </a>
                </nav>
            </div>

            <!-- STOCK & TRANSACTIONS -->
            <div>
                <div class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Operasional Stok</div>
                <nav class="space-y-0.5">
                    <a href="{{ route('inventory.stock-summary.index') }}" class="sidebar-link {{ request()->routeIs('inventory.stock-summary.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-stack text-base mr-2.5"></i> Ringkasan Stok
                    </a>
                    <a href="{{ route('inventory.stock-in.index') }}" class="sidebar-link {{ request()->routeIs('inventory.stock-in.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-arrow-down-right text-base mr-2.5"></i> Barang Masuk (Stock In)
                    </a>
                    <a href="{{ route('inventory.stock-out.index') }}" class="sidebar-link {{ request()->routeIs('inventory.stock-out.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-arrow-up-right text-base mr-2.5"></i> Barang Keluar (Stock Out)
                    </a>
                    <a href="{{ route('inventory.transfers.index') }}" class="sidebar-link {{ request()->routeIs('inventory.transfers.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-arrows-left-right text-base mr-2.5"></i> Pindah Gudang (Transfer)
                    </a>
                    <a href="{{ route('inventory.adjustments.index') }}" class="sidebar-link {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-sliders text-base mr-2.5"></i> Penyesuaian Stok (Rusak/Hilang)
                    </a>
                    <a href="{{ route('inventory.cycle-counts.index') }}" class="sidebar-link {{ request()->routeIs('inventory.cycle-counts.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-clipboard-text text-base mr-2.5"></i> Stock Opname
                    </a>
                </nav>
            </div>

            <!-- FULFILLMENT & LOGISTICS -->
            <div>
                <div class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Pengiriman & Logistik</div>
                <nav class="space-y-0.5">
                    <a href="{{ route('inventory.reservations.index') }}" class="sidebar-link {{ request()->routeIs('inventory.reservations.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-bookmark text-base mr-2.5"></i> Pemesanan Stok
                    </a>
                    <a href="{{ route('inventory.pickings.index') }}" class="sidebar-link {{ request()->routeIs('inventory.pickings.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-hand-pointing text-base mr-2.5"></i> Pengambilan Barang (Picking)
                    </a>
                    <a href="{{ route('inventory.packings.index') }}" class="sidebar-link {{ request()->routeIs('inventory.packings.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-package text-base mr-2.5"></i> Pengemasan Barang (Packing)
                    </a>
                    <a href="{{ route('inventory.deliveries.index') }}" class="sidebar-link {{ request()->routeIs('inventory.deliveries.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-truck text-base mr-2.5"></i> Pengiriman Truk (Delivery)
                    </a>
                </nav>
            </div>

            <!-- TRACKING & REPORTS -->
            <div>
                <div class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Pelacakan & Laporan</div>
                <nav class="space-y-0.5">
                    <a href="{{ route('inventory.stock-Buku Catatan.index') }}" class="sidebar-link {{ request()->routeIs('inventory.stock-Buku Catatan.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-notebook text-base mr-2.5"></i> Buku Mutasi Stok
                    </a>
                    <a href="{{ route('inventory.serial-numbers.index') }}" class="sidebar-link {{ request()->routeIs('inventory.serial-numbers.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-hash text-base mr-2.5"></i> Nomor Seri (SN)
                    </a>
                    <a href="{{ route('inventory.batch-numbers.index') }}" class="sidebar-link {{ request()->routeIs('inventory.batch-numbers.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-pulse text-base mr-2.5"></i> Nomor Batch (Kedaluwarsa)
                    </a>
                    <a href="{{ route('inventory.barcodes.index') }}" class="sidebar-link {{ request()->routeIs('inventory.barcodes.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-barcode text-base mr-2.5"></i> Cetak Barcode
                    </a>
                    <a href="{{ route('inventory.reports.index') }}" class="sidebar-link {{ request()->routeIs('inventory.reports.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-chart-line-up text-base mr-2.5"></i> Laporan Gudang
                    </a>
                    <a href="{{ route('inventory.analytics.index') }}" class="sidebar-link {{ request()->routeIs('inventory.analytics.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-chart-pie-slice text-base mr-2.5"></i> Analisis Pintar
                    </a>
                    <a href="{{ route('inventory.settings.index') }}" class="sidebar-link {{ request()->routeIs('inventory.settings.*') ? 'active' : '' }} flex items-center px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300">
                        <i class="ph ph-gear text-base mr-2.5"></i> Pengaturan Gudang
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 glass-panel border-b border-gray-200 dark:border-erp-border flex items-center justify-between px-8 z-10">
            <div class="flex items-center space-x-4">
                <button onclick="window.history.back()" class="p-2 rounded-lg bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white hover:border-erp-green transition-colors text-xs flex items-center">
                    <i class="ph ph-arrow-left text-sm mr-1"></i> Kembali
                </button>
                <div class="flex items-center w-80 relative">
                    <i class="ph ph-magnifying-glass absolute left-3 text-gray-600 dark:text-gray-400"></i>
                    <input type="text" placeholder="Cari Gudang... (Ctrl+K)" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg pl-10 pr-4 py-1.5 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:border-erp-green">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="toggleTheme()" class="text-xs text-gray-600 dark:text-gray-400 hover:text-erp-green flex items-center p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Toggle Light/Dark Mode">
                    <i class="ph ph-moon text-base dark:hidden"></i>
                    <i class="ph ph-sun text-base hidden dark:block"></i>
                </button>
                <a href="{{ url('/master-demo') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-erp-green flex items-center">
                    <i class="ph ph-grid-four text-base mr-1"></i> Main Portal
                </a>
                <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-erp-green to-emerald-300 flex items-center justify-center text-gray-900 font-bold shadow-lg cursor-pointer text-xs">
                    SA
                </div>
            </div>
        </header>

        <!-- Flash Banners -->
        <div class="px-8 pt-4">
            @if(session('success'))
                <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow-lg mb-4">
                    <div class="flex items-center">
                        <i class="ph ph-check-circle text-xl mr-3 text-emerald-400"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-gray-900 dark:text-white">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-900/50 border border-rose-500 text-rose-200 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow-lg mb-4">
                    <div class="flex items-center">
                        <i class="ph ph-warning-circle text-xl mr-3 text-rose-400"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-gray-900 dark:text-white">&times;</button>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <div class="flex-1 overflow-auto p-8 relative">
            @yield('content')
        </div>
    </main>

    @yield('scripts')
@include('components.global-loading')
</body>
</html>
