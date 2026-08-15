<?php if (isset($component)) { $__componentOriginalc7e07e13154e334f31746521cec25b2d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7e07e13154e334f31746521cec25b2d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'automation::components.layouts.master','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('automation::layouts.master'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <h1>Hello World</h1>

    <p>Module: <?php echo config('automation.name'); ?></p>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7e07e13154e334f31746521cec25b2d)): ?>
<?php $attributes = $__attributesOriginalc7e07e13154e334f31746521cec25b2d; ?>
<?php unset($__attributesOriginalc7e07e13154e334f31746521cec25b2d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7e07e13154e334f31746521cec25b2d)): ?>
<?php $component = $__componentOriginalc7e07e13154e334f31746521cec25b2d; ?>
<?php unset($__componentOriginalc7e07e13154e334f31746521cec25b2d); ?>
<?php endif; ?>
<?php /**PATH D:\suba-erp-master-local-latest\Modules\Automation\resources\views\index.blade.php ENDPATH**/ ?>