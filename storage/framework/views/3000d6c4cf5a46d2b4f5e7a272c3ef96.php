<?php
    $level ??= 0;
    $levelNestingClass = match($level) {
        0 => "sl-ml-px",
        default => "sl-ml-7"
    };
    $expandable ??= !isset($fields["[]"]);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <div class="<?php echo e($expandable ? 'expandable' : ''); ?> sl-text-sm sl-border-l <?php echo e($levelNestingClass); ?>">
        <?php $__env->startComponent('scribe::themes.elements.components.field-details', [
          'name' => $name,
          'type' => $field['type'] ?? 'string',
          'required' => $field['required'] ?? false,
          'deprecated' => $field['deprecated'] ?? false,
          'description' => $field['description'] ?? '',
          'example' => $field['example'] ?? '',
          'enumValues' => $field['enumValues'] ?? null,
          'endpointId' => $endpointId,
          'hasChildren' => !empty($field['__fields']),
          'component' => 'body',
        ]); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($field['__fields'])): ?>
            <div class="children" style="<?php echo e($expandable ? 'display: none;' : ''); ?>">
                <?php $__env->startComponent('scribe::themes.elements.components.nested-fields', [
                  'fields' => $field['__fields'],
                  'endpointId' => $endpointId,
                  'level' => $level + 1,
                  'expandable'=> $expandable,
                ]); ?>
                <?php echo $__env->renderComponent(); ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
<?php /**PATH D:\suba-erp-master-local-latest\vendor\knuckleswtf\scribe\resources\views\themes\elements\components\nested-fields.blade.php ENDPATH**/ ?>