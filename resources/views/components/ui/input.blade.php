@props([
    'type' => 'text',
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'icon' => null,
    'error' => null
])

@php
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
@endphp

<div class="mb-4 w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-{{ $icon }}"></i>
            </div>
        @endif
        
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => $baseClasses]) }}
        >
    </div>
    
    @if($hasError)
        <p class="mt-1 text-sm text-red-600">
            {{ $errors->first($name) ?? $error }}
        </p>
    @endif
</div>
