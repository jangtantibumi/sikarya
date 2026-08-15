<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'left',
    'secondary' => false
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
    'secondary' => false
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
    
    $textColor = $secondary ? 'text-slate-500' : 'text-slate-800 font-medium';
?>

<td <?php echo e($attributes->merge(['class' => "px-6 py-4 whitespace-nowrap text-sm {$textColor} {$alignmentClass} transition-colors group-hover:bg-slate-50/50"])); ?>>
    <?php echo e($slot); ?>

</td>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\ui\table-cell.blade.php ENDPATH**/ ?>