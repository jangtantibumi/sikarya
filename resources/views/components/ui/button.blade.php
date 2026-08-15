@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
    'icon' => null,
    'href' => null
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 ease-in-out active:scale-[0.98] outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-4 py-2 text-sm rounded-xl gap-2',
        'lg' => 'px-6 py-3 text-base rounded-2xl gap-2.5',
    ][$size];

    $variantClasses = [
        'primary' => 'bg-[#0C3527] text-white shadow-md shadow-[#0C3527]/20 hover:bg-[#124836] hover:shadow-lg focus:ring-[#0C3527]',
        'secondary' => 'bg-[#D9EFE9] text-[#0C3527] hover:bg-[#c2ded7] focus:ring-[#0C3527]',
        'danger' => 'bg-red-600 text-white shadow-md shadow-red-500/20 hover:bg-red-700 hover:shadow-lg focus:ring-red-500',
        'outline' => 'border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-gray-200 shadow-sm',
        'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-200',
    ][$variant];

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
        @if($icon)
            <i class="fa-solid fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="fa-solid fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
