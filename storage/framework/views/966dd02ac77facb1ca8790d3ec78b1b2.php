<div class="overflow-hidden bg-white border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-2xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($head)): ?>
                <thead class="bg-slate-50/50">
                    <tr>
                        <?php echo e($head); ?>

                    </tr>
                </thead>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <tbody class="divide-y divide-slate-50 bg-white">
                <?php echo e($slot); ?>

            </tbody>
        </table>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($footer)): ?>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\ui\table.blade.php ENDPATH**/ ?>