<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Northstar Purchasing</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Instrument Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gradient-text {
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .toast-enter {
            animation: slideInRight 0.3s ease-out forwards;
        }
        .toast-exit {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative pb-20">

    <!-- Header -->
    <header class="glass sticky top-0 z-40 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('master-demo', ['company' => $company->id])); ?>" class="text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <p class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Northstar OS &middot; Purchasing</p>
                <h1 class="text-xl font-bold gradient-text"><?php echo e($company->name); ?></h1>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-500/30">
                    <?php echo e(substr($currentUser->name, 0, 1)); ?>

                </div>
                <div class="hidden md:block text-sm">
                    <p class="font-medium text-slate-200"><?php echo e($currentUser->name); ?></p>
                    <p class="text-xs text-slate-400 capitalize"><?php echo e(str_replace('_', ' ', $currentUser->role)); ?></p>
                </div>
            </div>
            <form method="post" action="<?php echo e(route('master-demo.logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm font-medium text-slate-400 hover:text-rose-400 transition flex items-center gap-1">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Controls & Tenant Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-hide">
                <span class="text-sm text-slate-400 mr-2">Tenant:</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a href="<?php echo e(route('master-demo.purchasing', ['company' => $c->id])); ?>" 
                       class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-all <?php echo e($c->id === $company->id ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-slate-800/50 text-slate-400 border border-slate-700/50 hover:bg-slate-800 hover:text-slate-300'); ?>">
                        <?php echo e($c->name); ?>

                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            
            <button onclick="openPoModal()" class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium hover-lift transition-all focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 border border-indigo-500 shadow-[0_0_15px_rgba(79,70,229,0.3)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Create Pesanan Pembelian
            </button>
        </div>

        <!-- Orders Table -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-indigo-400"></i>
                    Pesanan Pembelians
                </h2>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 text-xs font-medium text-slate-300 border border-white/5"><?php echo e($orders->count()); ?> Orders</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-800/40 text-slate-400 border-b border-white/5">
                            <th class="px-6 py-4 font-medium">Nomor PO</th>
                            <th class="px-6 py-4 font-medium">Supplier</th>
                            <th class="px-6 py-4 font-medium">Tanggal</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Total (Rp)</th>
                            <th class="px-6 py-4 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-200">
                                    <?php echo e($o->number); ?>

                                    <div class="text-xs text-slate-500 mt-0.5"><?php echo e($o->lines->count()); ?> items</div>
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    <?php echo e($o->supplier->name ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    <?php echo e(\Carbon\Carbon::parse($o->order_date)->format('d M Y')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                            'submitted' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                            'partially_received' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                            'received' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                        ];
                                        $color = $statusColors[$o->status] ?? $statusColors['draft'];
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo e($color); ?> capitalize">
                                        <?php echo e(str_replace('_', ' ', $o->status)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-200">
                                    <?php echo e(number_format($o->total_amount, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4 flex items-center justify-center gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($o->status === 'draft'): ?>
                                        <button onclick="submitPO(<?php echo e($o->id); ?>)" class="px-3 py-1.5 rounded bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 transition text-xs font-medium border border-amber-500/30">Submit</button>
                                    <?php elseif($o->status === 'submitted'): ?>
                                        <button onclick="decidePO(<?php echo e($o->id); ?>, 'approved')" class="px-3 py-1.5 rounded bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 transition text-xs font-medium border border-emerald-500/30">Approve</button>
                                        <button onclick="decidePO(<?php echo e($o->id); ?>, 'rejected')" class="px-3 py-1.5 rounded bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition text-xs font-medium border border-rose-500/30">Reject</button>
                                    <?php elseif(in_array($o->status, ['approved', 'partially_received'])): ?>
                                        <button onclick="openReceiptModal(<?php echo e($o->id); ?>, <?php echo e($o->lines->toJson()); ?>)" class="px-3 py-1.5 rounded bg-cyan-500/20 text-cyan-300 hover:bg-cyan-500/30 transition text-xs font-medium border border-cyan-500/30">Receive Goods</button>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-500">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
                                    <p>Belum ada Pesanan Pembelian untuk tenant ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Barang Masuks Table -->
        <div class="glass rounded-2xl overflow-hidden mt-8">
            <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="package-check" class="w-5 h-5 text-emerald-400"></i>
                    Barang Masuks
                </h2>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 text-xs font-medium text-slate-300 border border-white/5"><?php echo e($receipts->count()); ?> Receipts</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-800/40 text-slate-400 border-b border-white/5">
                            <th class="px-6 py-4 font-medium">Nomor GR</th>
                            <th class="px-6 py-4 font-medium">Nomor PO</th>
                            <th class="px-6 py-4 font-medium">Tanggal Terima</th>
                            <th class="px-6 py-4 font-medium">Penerima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-200">
                                    <?php echo e($r->number); ?>

                                </td>
                                <td class="px-6 py-4 text-indigo-300">
                                    <?php echo e($r->purchaseOrder->number ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    <?php echo e(\Carbon\Carbon::parse($r->received_date)->format('d M Y')); ?>

                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    <?php echo e($r->receivedBy->name ?? '-'); ?>

                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
                                    <p>Belum ada penerimaan barang.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Create PO Slide-over -->
    <div id="poModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closePoModal()"></div>
        <div class="absolute inset-y-0 right-0 w-full max-w-4xl glass-panel shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out" id="poModalPanel">
            
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between bg-slate-900/50">
                <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-5 h-5 text-indigo-400"></i>
                    Buat Pesanan Pembelian Baru
                </h3>
                <button onclick="closePoModal()" class="text-slate-400 hover:text-white transition rounded-lg p-1 hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Head Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Supplier</label>
                        <select id="poSupplier" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                            <option value="">-- Pilih Supplier --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?> (<?php echo e($s->code); ?>)</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Expected Date</label>
                        <input type="date" id="poExpectedDate" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition [color-scheme:dark]">
                    </div>
                </div>

                <!-- Lines -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Order Lines</label>
                        <button onclick="addPoLine()" class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Baris
                        </button>
                    </div>
                    
                    <div class="bg-slate-900/50 border border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-slate-800/50 text-slate-400">
                                    <th class="px-4 py-3 font-medium w-1/2">Produk</th>
                                    <th class="px-4 py-3 font-medium w-1/6">Qty</th>
                                    <th class="px-4 py-3 font-medium w-1/4">Harga Satuan (Rp)</th>
                                    <th class="px-4 py-3 font-medium text-right w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody id="poLinesContainer" class="divide-y divide-slate-800">
                                <!-- Lines injected by JS -->
                            </tbody>
                            <tfoot class="bg-slate-800/30">
                                <tr>
                                    <td colspan="2" class="px-4 py-3 text-right font-medium text-slate-400">Total Keseluruhan:</td>
                                    <td colspan="2" class="px-4 py-3 font-bold text-emerald-400 text-lg" id="poGrandTotal">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-white/10 bg-slate-900/50 flex justify-end gap-3">
                <button onclick="closePoModal()" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">Batal</button>
                <button onclick="savePO()" class="px-5 py-2.5 rounded-lg text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white transition hover-lift shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Draft PO
                </button>
            </div>
        </div>
    </div>

    <!-- Barang Masuk Modal -->
    <div id="grModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeGrModal()"></div>
        <div class="relative w-full max-w-3xl glass rounded-2xl shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200" id="grModalPanel">
            
            <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between bg-slate-900/50">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-5 h-5 text-emerald-400"></i>
                    Barang Masuk
                </h3>
                <button onclick="closeGrModal()" class="text-slate-400 hover:text-white transition rounded-lg p-1 hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="grPoId">
                <div class="space-y-2">
                    <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Gudang Penerima</label>
                    <select id="grWarehouse" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        <option value="">-- Pilih Gudang --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?> (<?php echo e($w->code); ?>)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-medium text-slate-400 uppercase tracking-wider">Item Diterima</label>
                    <div class="bg-slate-900/50 border border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-slate-800/50 text-slate-400">
                                    <th class="px-4 py-3 font-medium">Produk</th>
                                    <th class="px-4 py-3 font-medium text-center">Dipesan</th>
                                    <th class="px-4 py-3 font-medium text-center">Sudah Terima</th>
                                    <th class="px-4 py-3 font-medium text-right w-1/4">Terima Sekarang</th>
                                </tr>
                            </thead>
                            <tbody id="grLinesContainer" class="divide-y divide-slate-800">
                                <!-- Lines injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-white/10 bg-slate-900/50 flex justify-end gap-3">
                <button onclick="closeGrModal()" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">Batal</button>
                <button onclick="saveGR()" class="px-5 py-2.5 rounded-lg text-sm font-medium bg-emerald-600 hover:bg-emerald-500 text-white transition hover-lift shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                    <i data-lucide="check-check" class="w-4 h-4"></i> Konfirmasi Penerimaan
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3"></div>

    <!-- JavaScript Data & Logic -->
    <script>
        // Init icons
        lucide.createIcons();

        // Data from backend
        const products = <?php echo json_encode($products, 15, 512) ?>;
        let poLineCounter = 0;

        // --- Toast System ---
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            
            const isSuccess = type === 'success';
            const bgColor = isSuccess ? 'bg-emerald-500/10' : 'bg-rose-500/10';
            const borderColor = isSuccess ? 'border-emerald-500/20' : 'border-rose-500/20';
            const textColor = isSuccess ? 'text-emerald-400' : 'text-rose-400';
            const icon = isSuccess ? 'check-circle' : 'alert-circle';
            
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg border ${bgColor} ${borderColor} glass toast-enter min-w-[300px] shadow-lg`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-5 h-5 ${textColor}"></i>
                <p class="text-sm font-medium text-white">${message}</p>
            `;
            
            container.appendChild(toast);
            lucide.createIcons({ root: toast });
            
            setTimeout(() => {
                toast.classList.replace('toast-enter', 'toast-exit');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // --- CSRF Token for Fetch ---
        const csrfToken = '<?php echo e(csrf_token()); ?>';
        
        async function apiRequest(url, method = 'POST', body = null) {
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: body ? JSON.stringify(body) : null
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan sistem.');
                }
                
                return data;
            } catch (error) {
                showToast(error.message, 'error');
                throw error;
            }
        }

        // --- PO Create Workflow ---
        function openPoModal() {
            document.getElementById('poModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('poModalPanel').classList.remove('translate-x-full');
            }, 10);
            
            // Reset form
            document.getElementById('poSupplier').value = '';
            document.getElementById('poExpectedDate').value = '';
            document.getElementById('poLinesContainer').innerHTML = '';
            poLineCounter = 0;
            addPoLine(); // add first empty line
            updateGrandTotal();
        }

        function closePoModal() {
            document.getElementById('poModalPanel').classList.add('translate-x-full');
            setTimeout(() => {
                document.getElementById('poModal').classList.add('hidden');
            }, 300);
        }

        function addPoLine() {
            poLineCounter++;
            const tbody = document.getElementById('poLinesContainer');
            const tr = document.createElement('tr');
            tr.id = `poline-${poLineCounter}`;
            tr.className = 'group';
            
            let productOptions = '<option value="">-- Produk --</option>';
            products.forEach(p => {
                productOptions += `<option value="${p.id}">${p.name} (${p.sku})</option>`;
            });

            tr.innerHTML = `
                <td class="px-4 py-2">
                    <select class="po-line-product w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-md px-3 py-2 focus:ring-1 focus:ring-indigo-500 outline-none transition">
                        ${productOptions}
                    </select>
                </td>
                <td class="px-4 py-2">
                    <input type="number" min="1" value="1" class="po-line-qty w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-md px-3 py-2 focus:ring-1 focus:ring-indigo-500 outline-none transition text-center" onchange="updateGrandTotal()" onkeyup="updateGrandTotal()">
                </td>
                <td class="px-4 py-2">
                    <input type="number" min="0" value="0" class="po-line-price w-full bg-slate-800/50 border border-slate-700 text-white text-sm rounded-md px-3 py-2 focus:ring-1 focus:ring-indigo-500 outline-none transition text-right" onchange="updateGrandTotal()" onkeyup="updateGrandTotal()">
                </td>
                <td class="px-4 py-2 text-right">
                    <button onclick="removePoLine(${poLineCounter})" class="text-slate-500 hover:text-rose-400 transition p-1.5 rounded hover:bg-rose-400/10 opacity-0 group-hover:opacity-100 focus:opacity-100">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            lucide.createIcons({ root: tr });
        }

        function removePoLine(id) {
            const row = document.getElementById(`poline-${id}`);
            if (row) {
                row.remove();
                updateGrandTotal();
            }
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('#poLinesContainer tr').forEach(row => {
                const qty = parseFloat(row.querySelector('.po-line-qty').value) || 0;
                const price = parseFloat(row.querySelector('.po-line-price').value) || 0;
                total += (qty * price);
            });
            document.getElementById('poGrandTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        async function savePO() {
            const supplier_id = document.getElementById('poSupplier').value;
            const expected_date = document.getElementById('poExpectedDate').value;
            
            const lines = [];
            document.querySelectorAll('#poLinesContainer tr').forEach(row => {
                const product_id = row.querySelector('.po-line-product').value;
                const quantity = row.querySelector('.po-line-qty').value;
                const unit_price = row.querySelector('.po-line-price').value;
                if (product_id && quantity > 0) {
                    lines.push({ product_id, quantity, unit_price });
                }
            });

            if (!supplier_id) return showToast('Pilih supplier terlebih dahulu', 'error');
            if (lines.length === 0) return showToast('Minimal satu baris produk harus diisi', 'error');

            const payload = { supplier_id, expected_date, lines };
            
            try {
                await apiRequest('/api/purchasing/orders', 'POST', payload);
                showToast('Pesanan Pembelian berhasil disimpan sebagai Draft');
                closePoModal();
                setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                // Handled in apiRequest
            }
        }

        // --- PO Actions ---
        async function submitPO(id) {
            try {
                await apiRequest(`/api/purchasing/orders/${id}/submit`, 'POST');
                showToast('Pesanan Pembelian berhasil disubmit');
                setTimeout(() => window.location.reload(), 1000);
            } catch(e) {}
        }

        async function decidePO(id, decision) {
            try {
                await apiRequest(`/api/purchasing/orders/${id}/decision`, 'POST', { decision });
                showToast(`Pesanan Pembelian berhasil di-${decision}`);
                setTimeout(() => window.location.reload(), 1000);
            } catch(e) {}
        }

        // --- Barang Masuk Workflow ---
        function openReceiptModal(poId, lines) {
            document.getElementById('grModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('grModalPanel').classList.remove('opacity-0', 'scale-95');
                document.getElementById('grModalPanel').classList.add('opacity-100', 'scale-100');
            }, 10);
            
            document.getElementById('grPoId').value = poId;
            document.getElementById('grWarehouse').value = '';
            
            const tbody = document.getElementById('grLinesContainer');
            tbody.innerHTML = '';
            
            lines.forEach(line => {
                const remaining = line.ordered_quantity - line.received_quantity;
                if (remaining <= 0) return; // Skip fully received lines
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-200">${line.product.name}</div>
                        <div class="text-xs text-slate-500">${line.product.sku}</div>
                        <input type="hidden" class="gr-line-id" value="${line.id}">
                    </td>
                    <td class="px-4 py-3 text-center text-slate-400">${line.ordered_quantity}</td>
                    <td class="px-4 py-3 text-center text-emerald-400 font-medium">${line.received_quantity}</td>
                    <td class="px-4 py-3 text-right">
                        <input type="number" min="0" max="${remaining}" value="${remaining}" class="gr-line-qty w-24 bg-slate-900 border border-slate-700 text-white rounded-md px-3 py-1.5 focus:ring-1 focus:ring-emerald-500 outline-none transition text-center">
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            if (tbody.children.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Semua item sudah diterima sepenuhnya.</td></tr>`;
            }
        }

        function closeGrModal() {
            document.getElementById('grModalPanel').classList.remove('opacity-100', 'scale-100');
            document.getElementById('grModalPanel').classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                document.getElementById('grModal').classList.add('hidden');
            }, 200);
        }

        async function saveGR() {
            const purchase_order_id = document.getElementById('grPoId').value;
            const warehouse_id = document.getElementById('grWarehouse').value;
            
            if (!warehouse_id) return showToast('Pilih gudang penerima', 'error');

            const lines = [];
            document.querySelectorAll('#grLinesContainer tr').forEach(row => {
                const inputId = row.querySelector('.gr-line-id');
                const inputQty = row.querySelector('.gr-line-qty');
                
                if (inputId && inputQty) {
                    const quantity = parseFloat(inputQty.value);
                    if (quantity > 0) {
                        lines.push({
                            purchase_order_line_id: inputId.value,
                            quantity: quantity
                        });
                    }
                }
            });

            if (lines.length === 0) return showToast('Tidak ada item yang diterima', 'error');

            try {
                await apiRequest('/api/purchasing/goods-receipts', 'POST', {
                    purchase_order_id,
                    warehouse_id,
                    lines
                });
                showToast('Penerimaan Barang (Barang Masuk) berhasil dicatat', 'success');
                closeGrModal();
                setTimeout(() => window.location.reload(), 1500);
            } catch(e) {}
        }
    </script>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\master-demo-purchasing.blade.php ENDPATH**/ ?>