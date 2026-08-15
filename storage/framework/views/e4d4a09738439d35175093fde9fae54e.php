<?php $__env->startSection('title', 'Daftar Customer - CRM Portal'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* Filters */
    .filter-bar { background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 20px; margin-bottom: 24px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center; box-shadow: var(--crm-shadow); }
    .filter-input { background: rgba(255,255,255,0.7); border: 1px solid var(--crm-border); color: #1e293b; padding: 10px 14px; border-radius: 12px; font-family: 'Outfit', sans-serif; font-size: 13.5px; outline: none; transition: var(--crm-transition); }
    .filter-input:focus { border-color: var(--crm-primary); box-shadow: 0 0 0 4px rgba(12, 53, 39, 0.18); background: #fff; }
    .filter-group { display: flex; flex-direction: column; gap: 8px; }
    .filter-label { font-size: 11px; font-weight: 700; color: var(--crm-primary); text-transform: uppercase; letter-spacing: 0.5px; }
    
    /* Pagination */
    .pagination-container { padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5); }
    .pagination-info { font-size: 13px; color: #64748b; font-weight: 500; }
    .pagination-links { display: flex; gap: 6px; }
    .page-link { padding: 8px 14px; border-radius: 8px; background: rgba(0,0,0,0.03); color: #475569; text-decoration: none; font-size: 13px; font-weight: 500; transition: var(--crm-transition); }
    .page-link.active { background: var(--crm-primary); color: #fff; }
    .page-link:hover:not(.active) { background: var(--crm-secondary); color: var(--crm-primary); }

    .tag-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; color: #fff; margin-right: 4px; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Daftar Customer</h1>
        <p>Kelola data, tagging, status blacklist, dan membership customer F&B Anda.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <form action="<?php echo e(route('crm.customers.import.csv')); ?>" method="POST" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center;">
            <?php echo csrf_field(); ?>
            <input type="file" name="import_file" accept=".csv" required style="font-size: 12px; color: #64748b; width: 170px; padding: 6px; border: 1px solid var(--crm-border); border-radius: 8px; background: #fff;">
            <button type="submit" class="btn btn-outline"><i class="ph ph-upload-simple"></i> Import</button>
        </form>
        <a href="<?php echo e(route('crm.customers.merge.form')); ?>" class="btn btn-outline" style="border-color: #d97706; color: #d97706;"><i class="ph ph-git-merge"></i> Merge Duplikat</a>
        <a href="<?php echo e(route('crm.customers.export.excel', request()->all())); ?>" class="btn btn-outline" style="border-color: var(--text-accent); color: var(--text-accent);"><i class="ph ph-file-xls"></i> Export Excel</a>
        <a href="<?php echo e(route('crm.customers.export.pdf', request()->all())); ?>" class="btn btn-outline" target="_blank"><i class="ph ph-printer"></i> PDF</a>
        <a href="<?php echo e(route('crm.customers.create')); ?>" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Customer</a>
    </div>
</div>

<form method="GET" action="<?php echo e(route('crm.customers.index')); ?>" class="filter-bar">
    <div class="filter-group" style="flex: 1; min-width: 220px;">
        <label class="filter-label">Pencarian</label>
        <div style="position: relative;">
            <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: #94a3b8; font-size: 18px;"></i>
            <input type="text" name="search" class="filter-input" placeholder="Cari nama, kode, hp, email, referral..." value="<?php echo e(request('search')); ?>" style="width: 100%; padding-left: 38px;">
        </div>
    </div>
    <div class="filter-group">
        <label class="filter-label">Membership</label>
        <select name="membership_level" class="filter-input">
            <option value="">Semua Level</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Guest', 'Silver', 'Gold', 'Platinum', 'Diamond']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($level); ?>" <?php echo e(request('membership_level') == $level ? 'selected' : ''); ?>><?php echo e($level); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Tag Customer</label>
        <select name="tag_id" class="filter-input">
            <option value="">Semua Tag</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($tag->id); ?>" <?php echo e(request('tag_id') == $tag->id ? 'selected' : ''); ?>><?php echo e($tag->name); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Blacklist</label>
        <select name="is_blacklisted" class="filter-input">
            <option value="">Semua</option>
            <option value="1" <?php echo e(request('is_blacklisted') === '1' ? 'selected' : ''); ?>>Hanya Blacklist</option>
            <option value="0" <?php echo e(request('is_blacklisted') === '0' ? 'selected' : ''); ?>>Normal</option>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Urutkan</label>
        <select name="sort_by" class="filter-input">
            <option value="created_at" <?php echo e(request('sort_by') == 'created_at' ? 'selected' : ''); ?>>Tgl Daftar</option>
            <option value="name" <?php echo e(request('sort_by') == 'name' ? 'selected' : ''); ?>>Nama</option>
            <option value="total_spending" <?php echo e(request('sort_by') == 'total_spending' ? 'selected' : ''); ?>>Total Spending</option>
            <option value="total_points" <?php echo e(request('sort_by') == 'total_points' ? 'selected' : ''); ?>>Total Point</option>
        </select>
    </div>
    <div class="filter-group" style="justify-content: flex-end;">
        <button type="submit" class="btn btn-secondary" style="height: 42px; margin-top: 19px;"><i class="ph ph-faders"></i> Terapkan</button>
    </div>
</form>

<div class="table-wrapper">
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Kontak & Referral</th>
                    <th>Tag & Segment</th>
                    <th>Membership</th>
                    <th>Total Pts</th>
                    <th>Spending</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td style="font-family: monospace; font-size: 13px; color: #64748b; font-weight: 500;">
                            <?php echo e($cust->customer_code); ?>

                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">
                                <?php echo e($cust->name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cust->is_blacklisted): ?>
                                    <span class="badge badge-danger" style="margin-left: 6px;"><i class="ph ph-prohibit"></i> Blacklisted</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?php echo e($cust->gender ? ucfirst($cust->gender) : '-'); ?> • <?php echo e($cust->birth_date ? $cust->birth_date->format('d/m/Y') : '-'); ?></div>
                        </td>
                        <td>
                            <div style="color: #334155; font-weight: 500;"><i class="ph ph-phone" style="color: #94a3b8;"></i> <?php echo e($cust->phone); ?></div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Ref Code: <span style="font-family: monospace; font-weight: 700; color: #475569;"><?php echo e($cust->referral_code ?? '-'); ?></span></div>
                        </td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $cust->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <span class="tag-badge" style="background-color: <?php echo e($t->color); ?>;"><?php echo e($t->name); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <span style="font-size: 12px; color: #94a3b8;">-</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-success"><?php echo e($cust->membership_level); ?></span>
                        </td>
                        <td style="color: var(--crm-primary); font-weight: 700;"><?php echo e(number_format($cust->total_points)); ?></td>
                        <td style="color: var(--text-accent); font-weight: 700;">Rp <?php echo e(number_format($cust->total_spending, 0, ',', '.')); ?></td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cust->is_active): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="<?php echo e(route('crm.customers.show', $cust->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Detail</a>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Data customer tidak ditemukan.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container">
        <div class="pagination-info">
            Menampilkan <?php echo e($customers->firstItem() ?? 0); ?> - <?php echo e($customers->lastItem() ?? 0); ?> dari <?php echo e($customers->total()); ?> customer
        </div>
        <div class="pagination-links">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customers->onFirstPage()): ?>
                <span class="page-link" style="opacity: 0.5; cursor: not-allowed;"><i class="ph ph-caret-left"></i> Prev</span>
            <?php else: ?>
                <a href="<?php echo e($customers->previousPageUrl()); ?>" class="page-link"><i class="ph ph-caret-left"></i> Prev</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customers->hasMorePages()): ?>
                <a href="<?php echo e($customers->nextPageUrl()); ?>" class="page-link">Next <i class="ph ph-caret-right"></i></a>
            <?php else: ?>
                <span class="page-link" style="opacity: 0.5; cursor: not-allowed;">Next <i class="ph ph-caret-right"></i></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\customers\index.blade.php ENDPATH**/ ?>