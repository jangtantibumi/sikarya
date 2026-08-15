<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'text',
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'icon' => null,
    'error' => null
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
    'type' => 'text',
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'icon' => null,
    'error' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $id = $id ?? $name;
    $hasError = $errors->has($name) || $error;
    
    $baseClasses = 'block w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0C3527] focus:ring focus:ring-[#0C3527]/20 focus:ring-opacity-50 transition-colors duration-200 text-sm';
    
    if ($icon) {
        $baseClasses .= ' pl-10';
    }
    
    if ($hasError) {
        $baseClasses = str_replace('border-gray-300', 'border-red-500', $baseClasses);
        $baseClasses = str_replace('focus:border-[#0C3527]', 'focus:border-red-500', $baseClasses);
        $baseClasses = str_replace('focus:ring-[#0C3527]/20', 'focus:ring-red-500/20', $baseClasses);
    }
?>

<div class="mb-4 w-full">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label for="<?php echo e($id); ?>" class="block text-sm font-medium text-gray-700 mb-1">
            <?php echo e($label); ?>

        </label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <div class="relative">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-<?php echo e($icon); ?>"></i>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <input 
            type="<?php echo e($type); ?>" 
            name="<?php echo e($name); ?>" 
            id="<?php echo e($id); ?>"
            value="<?php echo e(old($name, $value)); ?>"
            placeholder="<?php echo e($placeholder); ?>"
            <?php echo e($attributes->merge(['class' => $baseClasses])); ?>

        >
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasError): ?>
        <p class="mt-1 text-sm text-red-600">
            <?php echo e($errors->first($name) ?? $error); ?>

        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\ui\input.blade.php ENDPATH**/ ?>