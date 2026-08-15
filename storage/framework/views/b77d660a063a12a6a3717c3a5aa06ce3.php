<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'value', 'icon' => null, 'trend' => null, 'trendDirection' => 'up', 'interactive' => false, 'class' => '']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title', 'value', 'icon' => null, 'trend' => null, 'trendDirection' => 'up', 'interactive' => false, 'class' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'card ' . ($interactive ? 'interactive ' : '') . $class])); ?>>
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <h3><?php echo e($title); ?></h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(var(--accent-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--accent);">
                <i class="fa-solid <?php echo e($icon); ?> fa-lg"></i>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="value"><?php echo e($value); ?></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trend): ?>
        <div class="trend" style="color: <?php echo e($trendDirection === 'up' ? 'var(--success)' : 'var(--danger)'); ?>">
            <i class="fa-solid <?php echo e($trendDirection === 'up' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'); ?>"></i>
            <?php echo e($trend); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\ui\card.blade.php ENDPATH**/ ?>