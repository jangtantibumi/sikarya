<?php $__env->startSection('title', 'Marketing CRM - Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Marketing CRM</h1>
        <p>Kelola campaign, broadcast WhatsApp & Email, promo engine, birthday reminder, dan program referral.</p>
    </div>
</div>

<!-- STATS CARDS -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Total Campaign</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--crm-primary);"><?php echo e(number_format($campaignsCount)); ?></div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Broadcast Terkirim</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-accent);"><?php echo e(number_format($broadcastsCount)); ?></div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Promo Aktif</div>
        <div style="font-size: 28px; font-weight: 800; color: #d97706;"><?php echo e(number_format($promotionsCount)); ?></div>
    </div>
    <div class="stat-card" style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Total Referral</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--text-accent);"><?php echo e(number_format($referralsCount)); ?></div>
    </div>
</div>

<!-- MARKETING NAVIGATION CARDS -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
    <a href="<?php echo e(route('crm.marketing.campaigns')); ?>" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(12, 53, 39, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--crm-primary); margin-bottom: 16px;">
                <i class="ph ph-megaphone"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Campaign & Broadcast</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Buat dan kirim pesan masal WhatsApp & Email ke segmen/tag customer.</p>
        </div>
    </a>

    <a href="<?php echo e(route('crm.marketing.birthdays')); ?>" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(217, 119, 6, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #d97706; margin-bottom: 16px;">
                <i class="ph ph-cake"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Birthday Reminder</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Otomatisasi pengiriman ucapan & poin hadiah ulang tahun customer bulan ini.</p>
        </div>
    </a>

    <a href="<?php echo e(route('crm.marketing.promotions')); ?>" style="text-decoration: none; color: inherit;">
        <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--crm-primary)'" onmouseout="this.style.borderColor='var(--crm-border)'">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(22, 163, 74, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--text-accent); margin-bottom: 16px;">
                <i class="ph ph-ticket"></i>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 8px;">Promotion & Coupon Engine</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">Atur diskon promosi otomatis, batas belanja minimum, dan kupon khusus.</p>
        </div>
    </a>
</div>

<!-- RECENT CAMPAIGNS & UPCOMING BIRTHDAYS -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Campaign Terbaru</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo e($c->title); ?></div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Channel: <?php echo e(strtoupper($c->channel)); ?> • Target: <?php echo e(ucfirst($c->target_type)); ?></div>
                </div>
                <span class="badge badge-success"><?php echo e(ucfirst($c->status)); ?></span>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p style="color: #64748b; font-size: 13px;">Belum ada campaign.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 16px;">Ulang Tahun Bulan Ini</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $upcomingBirthdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo e($b->name); ?></div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?php echo e($b->birth_date ? $b->birth_date->format('d F') : '-'); ?> • <?php echo e($b->phone); ?></div>
                </div>
                <form action="<?php echo e(route('crm.marketing.birthdays.reward', $b->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Kirim Reward</button>
                </form>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p style="color: #64748b; font-size: 13px;">Tidak ada customer ulang tahun bulan ini.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\index.blade.php ENDPATH**/ ?>