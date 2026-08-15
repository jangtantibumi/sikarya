@props([
    'name',
    'title' => null,
    'maxWidth' => '2xl'
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: false,
        name: '{{ $name }}',
    }"
    x-show="show"
    x-on:open-modal.window="$event.detail == name ? show = true : null"
    x-on:close-modal.window="$event.detail == name ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <!-- Glassmorphism Backdrop -->
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Content -->
    <div
        x-show="show"
        class="mb-6 bg-white rounded-2xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.15)] transform transition-all sm:w-full sm:mx-auto {{ $maxWidthClass }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
    >
        @if($title)
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">{{ $title }}</h3>
                <button x-on:click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors bg-white rounded-full p-1 hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-lg leading-none w-5 h-5 flex items-center justify-center"></i>
                </button>
            </div>
        @endif
        
        <div class="px-6 py-6">
            {{ $slot }}
        </div>
        
        @if(isset($footer))
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
