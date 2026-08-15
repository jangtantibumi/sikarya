<?php $__env->startSection('title', $customer->name . ' - CRM Detail'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .header-profile { display: flex; gap: 24px; align-items: center; }
    .avatar { width: 72px; height: 72px; border-radius: 20px; background: var(--crm-secondary); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; color: var(--crm-primary); border: 1px solid var(--crm-border); }
    .page-title-group h1 { margin: 0; font-size: 28px; font-weight: 700; color: var(--crm-primary); display: flex; align-items: center; gap: 12px; }
    
    /* Tabs System */
    .tabs-header { display: flex; gap: 8px; border-bottom: 1px solid var(--crm-border); margin-bottom: 24px; overflow-x: auto; padding-bottom: 0; }
    .tab-btn { padding: 14px 24px; color: #64748b; font-size: 14px; font-weight: 600; cursor: pointer; transition: var(--crm-transition); border-bottom: 2px solid transparent; white-space: nowrap; }
    .tab-btn:hover { color: var(--crm-primary); background: rgba(0,0,0,0.02); }
    .tab-btn.active { color: var(--crm-primary); border-bottom-color: var(--crm-primary); }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* Cards and Lists */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .info-card { background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow); }
    .info-card h3 { margin: 0 0 20px; font-size: 16px; font-weight: 600; color: var(--crm-primary); border-bottom: 1px solid var(--crm-border); padding-bottom: 16px; }
    .info-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
    .info-row:last-child { margin-bottom: 0; }
    .info-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 14px; color: #1e293b; font-weight: 500; }

    /* Timeline */
    .timeline { position: relative; padding-left: 28px; }
    .timeline::before { content: ''; position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: rgba(12, 53, 39, 0.1); }
    .timeline-item { position: relative; margin-bottom: 28px; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-item::before { content: ''; position: absolute; left: -26px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--crm-secondary); border: 2px solid var(--crm-primary); }
    .timeline-date { font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; }
    .timeline-title { font-size: 14px; font-weight: 600; color: var(--crm-primary); margin-bottom: 4px; }
    .timeline-desc { font-size: 14px; color: #475569; line-height: 1.5; }

    .form-input-sm { background: #fff; border: 1px solid var(--crm-border); color: #1e293b; padding: 10px 14px; border-radius: 8px; font-family: inherit; font-size: 13.5px; outline: none; width: 100%; margin-bottom: 12px; transition: var(--crm-transition); }
    .form-input-sm:focus { border-color: var(--crm-primary); box-shadow: 0 0 0 3px rgba(12, 53, 39, 0.15); }
    
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.8; }
    .empty-state h3 { border: none; margin-bottom: 8px; justify-content: center; font-size: 18px; color: var(--crm-primary); }
    .empty-state p { color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto; line-height: 1.5; }

    .tag-chip { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #fff; margin-right: 6px; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header" style="align-items: center;">
    <div class="header-profile">
        <div class="avatar"><?php echo e(substr($customer->name, 0, 1)); ?></div>
        <div class="page-title-group">
            <h1>
                <?php echo e($customer->name); ?>

                <span class="badge badge-success"><?php echo e($customer->membership_level); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->is_blacklisted): ?>
                    <span class="badge badge-danger"><i class="ph ph-prohibit"></i> Blacklisted</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h1>
            <p><?php echo e($customer->customer_code); ?> • Terdaftar sejak <?php echo e($customer->created_at->format('d F Y')); ?></p>
        </div>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <form action="<?php echo e(route('crm.customers.blacklist', $customer->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->is_blacklisted): ?>
                <button type="submit" class="btn btn-outline" style="border-color: var(--text-accent); color: var(--text-accent);"><i class="ph ph-check-circle"></i> Lepas Blacklist</button>
            <?php else: ?>
                <button type="submit" class="btn btn-outline" style="border-color: #dc2626; color: #dc2626;" onclick="return confirm('Apakah Anda yakin ingin memasukkan customer ini ke blacklist?');"><i class="ph ph-prohibit"></i> Set Blacklist</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
        <a href="<?php echo e(route('crm.customers.edit', $customer->id)); ?>" class="btn btn-outline"><i class="ph ph-pencil-simple"></i> Edit Profil</a>
        <form action="<?php echo e(route('crm.customers.destroy', $customer->id)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus customer ini?');" style="display:inline;">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus</button>
        </form>
    </div>
</div>

<div class="tabs-header">
    <div class="tab-btn active" onclick="switchTab('overview', this)">Overview</div>
    <div class="tab-btn" onclick="switchTab('timeline', this)">Timeline</div>
    <div class="tab-btn" onclick="switchTab('referral', this)">Referral Program</div>
    <div class="tab-btn" onclick="switchTab('reservation', this)">Reservation</div>
    <div class="tab-btn" onclick="switchTab('points', this)">Point Management</div>
    <div class="tab-btn" onclick="switchTab('feedback', this)">Feedback</div>
</div>

<!-- 1. OVERVIEW -->
<div id="tab-overview" class="tab-content active">
    <div class="grid-2">
        <div class="info-card">
            <h3>Data Profil Pribadi</h3>
            <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value"><?php echo e($customer->name); ?></span></div>
            <div class="info-row"><span class="info-label">No. Telepon / WhatsApp</span><span class="info-value"><?php echo e($customer->phone); ?></span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?php echo e($customer->email ?? '-'); ?></span></div>
            <div class="info-row"><span class="info-label">Kode Referral</span><span class="info-value" style="font-family: monospace; font-weight: 700; color: var(--crm-primary);"><?php echo e($customer->referral_code ?? '-'); ?></span></div>
            <div class="info-row"><span class="info-label">Gender</span><span class="info-value"><?php echo e($customer->gender ? ucfirst($customer->gender) : '-'); ?></span></div>
            <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value"><?php echo e($customer->birth_date ? $customer->birth_date->format('d F Y') : '-'); ?></span></div>
            <div class="info-row"><span class="info-label">Last Visit</span><span class="info-value"><?php echo e($customer->last_visit ? $customer->last_visit->format('d F Y H:i') : 'Belum Pernah'); ?></span></div>
            <div class="info-row"><span class="info-label">Alamat Lengkap</span><span class="info-value"><?php echo e($customer->address ?? '-'); ?></span></div>
            <div class="info-row">
                <span class="info-label">Tags Customer</span>
                <span class="info-value">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customer->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <span class="tag-chip" style="background-color: <?php echo e($t->color); ?>;"><?php echo e($t->name); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <span style="color: #94a3b8; font-size: 13px;">Belum ada tag</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->is_blacklisted): ?>
                <div class="info-row" style="background: rgba(220,38,38,0.05); padding: 12px; border-radius: 8px; border: 1px solid rgba(220,38,38,0.2);">
                    <span class="info-label" style="color: #dc2626;">Alasan Blacklist</span>
                    <span class="info-value" style="color: #dc2626;"><?php echo e($customer->blacklist_reason ?? 'Tidak ada alasan khusus.'); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="info-card" style="background: var(--crm-primary); border-color: var(--crm-primary);">
                <h3 style="border-color: rgba(255,255,255,0.1); color: white;">Tingkat Membership</h3>
                <div style="font-size: 36px; font-weight: 800; color: var(--crm-secondary); margin-bottom: 12px; letter-spacing: -0.5px;"><?php echo e($customer->membership_level); ?></div>
                <div style="color: rgba(255,255,255,0.8); font-size: 14px;">Total Spending: <strong style="color: white; font-size: 16px;">Rp <?php echo e(number_format($customer->total_spending, 0, ',', '.')); ?></strong></div>
                <div style="color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px;">Total Point: <strong style="color: white; font-size: 16px;"><?php echo e(number_format($customer->total_points)); ?> pts</strong></div>
            </div>
        </div>
    </div>
</div>

<!-- 2. TIMELINE -->
<div id="tab-timeline" class="tab-content">
    <div class="info-card">
        <h3>Riwayat Aktivitas & Timeline Customer</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->timelines->count() > 0): ?>
            <div class="timeline">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->timelines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo e($timeline->created_at->format('d M Y, H:i')); ?></div>
                        <div class="timeline-title"><?php echo e($timeline->action); ?></div>
                        <div class="timeline-desc"><?php echo e($timeline->description); ?></div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 30px;">
                <p>Belum ada riwayat timeline.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- 3. REFERRAL PROGRAM -->
<div id="tab-referral" class="tab-content">
    <div class="info-card">
        <h3>Statistik Referral Customer</h3>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
            Kode Referral Unik: <strong style="font-family: monospace; font-size: 16px; color: var(--crm-primary);"><?php echo e($customer->referral_code); ?></strong>
        </p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->referrals->count() > 0): ?>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Customer Ditingkatkan (Referee)</th>
                        <th>Reward Point</th>
                        <th>Status</th>
                        <th>Tanggal Join</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><strong><?php echo e($ref->referee->name ?? 'Unknown'); ?></strong> (<?php echo e($ref->referee->phone ?? '-'); ?>)</td>
                            <td style="color: var(--text-accent); font-weight: 700;">+<?php echo e($ref->reward_points); ?> Pts</td>
                            <td><span class="badge badge-success"><?php echo e(ucfirst($ref->status)); ?></span></td>
                            <td><?php echo e($ref->created_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state" style="padding: 30px;">
                <p>Customer ini belum pernah merekrut teman melalui kode referral.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- 4. RESERVATION -->
<div id="tab-reservation" class="tab-content">
    <div class="info-card">
        <h3>Riwayat Reservasi</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->reservations->count() > 0): ?>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Jumlah Pax</th>
                        <th>Preferensi Meja</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($res->reservation_date->format('d/m/Y')); ?> <?php echo e($res->reservation_time); ?></td>
                            <td><strong><?php echo e($res->pax); ?> Pax</strong></td>
                            <td><?php echo e($res->table_preference ?? 'Bebas'); ?></td>
                            <td><span class="badge badge-success"><?php echo e($res->status); ?></span></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state" style="padding: 30px;">
                <p>Customer belum memiliki riwayat reservasi.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- 5. LOYALTY POINT -->
<div id="tab-points" class="tab-content">
    <div class="grid-2">
        <div class="info-card">
            <h3>Riwayat Point</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->pointHistories->count() > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->pointHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <div>
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px;"><?php echo e($ph->description); ?></div>
                                <div style="font-size: 12px; color: #64748b;"><?php echo e($ph->created_at->format('d M Y, H:i')); ?></div>
                            </div>
                            <div style="font-weight: 700; font-size: 16px; color: <?php echo e($ph->points > 0 ? '#0C3527' : '#dc2626'); ?>; background: <?php echo e($ph->points > 0 ? 'rgba(22, 163, 74, 0.1)' : 'rgba(220, 38, 38, 0.1)'); ?>; padding: 6px 12px; border-radius: 8px;">
                                <?php echo e($ph->points > 0 ? '+' : ''); ?><?php echo e($ph->points); ?>

                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 20px;">
                    <p>Belum ada riwayat point.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="info-card">
                <h3>Tambah Point Manual</h3>
                <form action="<?php echo e(route('crm.customers.points.add', $customer->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="number" name="points" class="form-input-sm" placeholder="Jumlah Point (Misal: 100)" required min="1">
                    <input type="text" name="description" class="form-input-sm" placeholder="Keterangan (Misal: Bonus Ultah)" required>
                    <button type="submit" class="btn btn-outline" style="width: 100%; margin-top: 12px;"><i class="ph ph-plus"></i> Tambah Point</button>
                </form>
            </div>
            <div class="info-card">
                <h3 style="color: var(--crm-danger); border-color: rgba(220,38,38,0.1);">Redeem Point Manual</h3>
                <form action="<?php echo e(route('crm.customers.points.redeem', $customer->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="number" name="points" class="form-input-sm" placeholder="Jumlah Point (Misal: 50)" required min="1" max="<?php echo e($customer->total_points); ?>">
                    <input type="text" name="description" class="form-input-sm" placeholder="Keterangan (Misal: Ditukar Minuman)" required>
                    <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 12px;"><i class="ph ph-minus"></i> Redeem Point</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 6. FEEDBACK -->
<div id="tab-feedback" class="tab-content">
    <div class="info-card">
        <h3>Ulasan & Feedback Customer</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->feedbacks->count() > 0): ?>
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Rating</th>
                        <th>Kategori</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td style="color: #f59e0b; font-weight: 700;">★ <?php echo e($fb->rating); ?>/5</td>
                            <td><?php echo e($fb->category); ?></td>
                            <td><?php echo e($fb->message); ?></td>
                            <td><span class="badge badge-success"><?php echo e($fb->status); ?></span></td>
                            <td><?php echo e($fb->created_at->format('d/m/Y')); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state" style="padding: 30px;">
                <p>Belum ada ulasan atau feedback dari customer ini.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function switchTab(tabId, el) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    el.classList.add('active');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\customers\show.blade.php ENDPATH**/ ?>