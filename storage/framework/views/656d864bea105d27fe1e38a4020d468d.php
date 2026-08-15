<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center">
        <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Master Data Center</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola semua data referensi utama sistem dalam satu kendali terpusat.</p>
        </div>
        
        <!-- Global Search Component -->
        <?php if (isset($component)) { $__componentOriginalad82a17c92e369ba8b4d935ed99bd88d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad82a17c92e369ba8b4d935ed99bd88d = $attributes; } ?>
<?php $component = App\View\Components\GlobalSearch::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('global-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GlobalSearch::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad82a17c92e369ba8b4d935ed99bd88d)): ?>
<?php $attributes = $__attributesOriginalad82a17c92e369ba8b4d935ed99bd88d; ?>
<?php unset($__attributesOriginalad82a17c92e369ba8b4d935ed99bd88d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad82a17c92e369ba8b4d935ed99bd88d)): ?>
<?php $component = $__componentOriginalad82a17c92e369ba8b4d935ed99bd88d; ?>
<?php unset($__componentOriginalad82a17c92e369ba8b4d935ed99bd88d); ?>
<?php endif; ?>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500">Total Barang</h2>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['products'] ?? 0, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-2xl bg-purple-50 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500">Data Supplier</h2>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['suppliers'] ?? 0, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Card 3 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-2xl bg-green-50 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500">Pengguna Aktif</h2>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['users'] ?? 0, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-2xl bg-orange-50 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500">Jadwal Shift</h2>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['shifts'] ?? 0, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('masterdata::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\Modules/MasterData\resources/views/index.blade.php ENDPATH**/ ?>