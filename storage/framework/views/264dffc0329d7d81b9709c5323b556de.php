<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Finance Master Data - ERP Engine'); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .glass-panel { background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .gradient-brand { background: linear-gradient(135deg, #059669, #0d9488); }
        .gradient-card { background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95)); }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between p-4 sticky top-0 h-screen overflow-y-auto">
        <div>
            <div class="flex items-center gap-3 px-2 py-4 mb-6 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center text-white font-bold text-xl shadow-lg">
                    <i class="ph-bold ph-coins"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-lg text-white tracking-wide">ERP Finance</h2>
                    <p class="text-xs text-emerald-400 font-semibold tracking-wider uppercase">Foundation Master</p>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="<?php echo e(route('finance.chart-of-accounts.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.chart-of-accounts.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-tree-structure text-lg"></i> Chart of Accounts
                </a>
                <a href="<?php echo e(route('finance.account-groups.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.account-groups.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-folders text-lg"></i> Account Groups
                </a>
                <a href="<?php echo e(route('finance.currencies.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.currencies.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-currency-circle-dollar text-lg"></i> Currencies
                </a>
                <a href="<?php echo e(route('finance.exchange-rates.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.exchange-rates.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-arrows-left-right text-lg"></i> Exchange Rates
                </a>
                <a href="<?php echo e(route('finance.fiscal-years.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.fiscal-years.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-calendar-blank text-lg"></i> Fiscal Year & Periods
                </a>
                <a href="<?php echo e(route('finance.cost-centers.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.cost-centers.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-buildings text-lg"></i> Cost Centers
                </a>
                <a href="<?php echo e(route('finance.profit-centers.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.profit-centers.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-chart-line-up text-lg"></i> Profit Centers
                </a>
                <a href="<?php echo e(route('finance.tax-masters.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.tax-masters.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-receipt text-lg"></i> Tax Master
                </a>
                <a href="<?php echo e(route('finance.payment-terms.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.payment-terms.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-clock-clockwise text-lg"></i> Payment Terms
                </a>
                <a href="<?php echo e(route('finance.numbering-series.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?php echo e(request()->routeIs('finance.numbering-series.*') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'); ?>">
                    <i class="ph-bold ph-hash text-lg"></i> Numbering Series
                </a>
            </nav>
        </div>

        <div class="p-3 border-t border-slate-800 text-xs text-slate-500 text-center">
            ERP Finance Architecture &bull; Sprint 1
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-slate-800 bg-slate-900/60 backdrop-blur px-6 flex items-center justify-between sticky top-0 z-30">
            <h1 class="text-xl font-bold text-white"><?php echo $__env->yieldContent('page_heading', 'Finance Master Data'); ?></h1>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-xs font-semibold">
                    <i class="ph-bold ph-check-circle"></i> Active Tenant Scope
                </span>
            </div>
        </header>

        <!-- Content View -->
        <div class="p-6 flex-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm flex items-center gap-3">
                    <i class="ph-bold ph-check-circle text-lg"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\Modules\Finance\resources\views\layouts\master.blade.php ENDPATH**/ ?>