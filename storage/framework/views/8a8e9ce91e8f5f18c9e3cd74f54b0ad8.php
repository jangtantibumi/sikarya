<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'left',
    'sortable' => false,
    'direction' => 'asc'
]));

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

foreach (array_filter(([
    'align' => 'left',
    'sortable' => false,
    'direction' => 'asc'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $alignmentClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align];
?>

<th scope="col" <?php echo e($attributes->merge(['class' => "px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap {$alignmentClass}"])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortable): ?>
        <button type="button" class="group inline-flex items-center space-x-1 focus:outline-none hover:text-slate-800 transition-colors w-full <?php echo e($alignmentClass == 'text-right' ? 'justify-end' : ($alignmentClass == 'text-center' ? 'justify-center' : 'justify-start')); ?>">
            <span><?php echo e($slot); ?></span>
            <span class="flex flex-col text-[8px] opacity-40 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-chevron-up <?php echo e($direction === 'asc' ? 'text-[#0C3527] font-bold' : ''); ?>"></i>
                <i class="fa-solid fa-chevron-down <?php echo e($direction === 'desc' ? 'text-[#0C3527] font-bold' : '-mt-1'); ?>"></i>
            </span>
        </button>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</th>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\ui\table-heading.blade.php ENDPATH**/ ?>