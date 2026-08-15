<div 
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:notify.window="
        message = $event.detail.message;
        type = $event.detail.type || 'success';
        show = true;
        setTimeout(() => show = false, 3000);
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    style="display: none; z-index: 9999;"
    class="fixed bottom-6 right-6 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-lg backdrop-blur-md font-medium text-sm text-white"
    :class="{
        'bg-green-600/90 shadow-green-500/20': type === 'success',
        'bg-red-600/90 shadow-red-500/20': type === 'error',
        'bg-blue-600/90 shadow-blue-500/20': type === 'info'
    }"
>
    <i class="fa-solid" :class="{
        'fa-check-circle': type === 'success',
        'fa-triangle-exclamation': type === 'error',
        'fa-circle-info': type === 'info'
    }"></i>
    <span x-text="message"></span>
</div>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views/components/ui/toast.blade.php ENDPATH**/ ?>