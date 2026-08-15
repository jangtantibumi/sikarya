@props(['variant' => 'primary', 'icon' => null, 'class' => ''])

@php
    $baseClass = 'ios-btn';
    $typeClass = match($variant) {
        'secondary' => 'ios-btn-secondary',
        'danger' => 'ios-btn-danger',
        default => 'ios-btn-primary',
    };
@endphp

<button {{ $attributes->merge(['class' => "$baseClass $typeClass $class"]) }}>
    @if($icon)
        <i class="fa-solid {{ $icon }}" style="margin-right: 8px;"></i>
    @endif
    {{ $slot }}
</button>
