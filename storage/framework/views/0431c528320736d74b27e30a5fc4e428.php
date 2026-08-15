<?php
    $summary = $summary ?? [
        'balance_sheet' => ['value' => 0],
        'profit_loss'   => ['value' => 0],
        'cash_flow'     => ['value' => 0],
        'equity'        => ['value' => 0],
    ];
    $journals = $journals ?? collect();
?>
<div class="finance-dashboard">
    <h2 class="section-title">Dashboard Keuangan</h2>
    <div class="grid-4 finance-summary" style="margin-bottom: 24px;">
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(170, 60%, 45%), hsl(200, 55%, 45%)); color: white;">
            <h3>Balance Sheet</h3>
            <p class="summary-value"><?php echo e(number_format($summary['balance_sheet']['value'] ?? 0, 2)); ?></p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(45, 70%, 45%), hsl(30, 65%, 45%)); color: white;">
            <h3>Profit &amp; Loss</h3>
            <p class="summary-value"><?php echo e(number_format($summary['profit_loss']['value'] ?? 0, 2)); ?></p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(340, 60%, 45%), hsl(310, 55%, 45%)); color: white;">
            <h3>Cash Flow</h3>
            <p class="summary-value"><?php echo e(number_format($summary['cash_flow']['value'] ?? 0, 2)); ?></p>
        </div>
        <div class="card summary-card" style="background: linear-gradient(135deg, hsl(120, 60%, 45%), hsl(100, 55%, 45%)); color: white;">
            <h3>Equity</h3>
            <p class="summary-value"><?php echo e(number_format($summary['equity']['value'] ?? 0, 2)); ?></p>
        </div>
    </div>
    <h3 class="section-subtitle">Jurnal Terbaru</h3>
    <div class="card" style="overflow-x:auto;">
        <table class="finance-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background: var(--panel-bg);">
                    <th>Tanggal</th>
                    <th>Akun</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $journals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($j->entry_date->format('Y-m-d')); ?></td>
                    <td><?php echo e($j->account->name ?? $j->system_key); ?></td>
                    <td style="color: var(--success);"><?php echo e(number_format($j->debit, 2)); ?></td>
                    <td style="color: var(--danger);"><?php echo e(number_format($j->credit, 2)); ?></td>
                    <td><?php echo e($j->memo); ?></td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Tidak ada jurnal.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views/finance/index.blade.php ENDPATH**/ ?>