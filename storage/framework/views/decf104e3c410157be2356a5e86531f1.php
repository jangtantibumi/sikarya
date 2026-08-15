<?php $__env->startSection('title', 'Campaign & Broadcast - Marketing CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Campaign & Broadcast</h1>
        <p>Kirim pesan massal WhatsApp & Email ke seluruh customer atau segmen terpolarisasi.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?php echo e(route('crm.marketing.broadcast-logs')); ?>" class="btn btn-outline"><i class="ph ph-receipt"></i> Log Broadcast</a>
        <a href="<?php echo e(route('crm.marketing.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Buat Campaign -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Buat Campaign Baru</h3>

        <form action="<?php echo e(route('crm.marketing.campaigns.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Judul Campaign</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Promo Weekend Diskon 20%" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Channel</label>
                    <select name="channel" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="broadcast">Semua Channel</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Target Audiens</label>
                    <select name="target_type" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="all">Semua Customer</option>
                        <option value="tag">Berdasarkan Tag</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Subjek (Khusus Email)</label>
                <input type="text" name="subject" class="form-control" placeholder="Subjek email menarik..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Isi Pesan / Template</label>
                <textarea name="message_body" rows="5" class="form-control" required placeholder="Halo {name}, dapatkan diskon 20% minggu ini karena kamu adalah member {membership}!" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border); font-family: inherit;"></textarea>
                <span style="font-size: 11px; color: #64748b;">Tag variabel: <code>{name}</code>, <code>{membership}</code>, <code>{points}</code>, <code>{customer_code}</code></span>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" name="send_now" value="1" id="send_now" checked>
                <label for="send_now" style="font-size: 13px; color: #334155; font-weight: 500;">Kirimkan Broadcast Langsung Sekarang</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-paper-plane-right"></i> Proses & Simpan Campaign</button>
        </form>
    </div>

    <!-- Tabel Daftar Campaign -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Campaign</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Judul Campaign</th>
                        <th>Channel</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Log Sent</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $camp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;"><?php echo e($camp->title); ?></div>
                                <div style="font-size: 12px; color: #64748b;">Dibuat: <?php echo e($camp->created_at->format('d/m/Y H:i')); ?></div>
                            </td>
                            <td><span class="badge badge-success"><?php echo e(strtoupper($camp->channel)); ?></span></td>
                            <td><?php echo e(ucfirst($camp->target_type)); ?></td>
                            <td><span class="badge badge-success"><?php echo e(ucfirst($camp->status)); ?></span></td>
                            <td><strong><?php echo e($camp->broadcast_logs_count); ?></strong> logs</td>
                            <td style="text-align: right;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($camp->status === 'draft'): ?>
                                    <form action="<?php echo e(route('crm.marketing.campaigns.send', $camp->id)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;">Exekusi Broadcast</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--text-accent); font-weight: 600;">Terkirim</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada campaign broadcast.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\campaigns.blade.php ENDPATH**/ ?>