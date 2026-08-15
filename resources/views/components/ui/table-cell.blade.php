@props([
    'align' => 'left',
    'secondary' => false
])

@php
    $alignmentClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align];
    
    $textColor = $secondary ? 'text-slate-500' : 'text-slate-800 font-medium';
@endphp

<td {{ $attributes->merge(['class' => "px-6 py-4 whitespace-nowrap text-sm {$textColor} {$alignmentClass} transition-colors group-hover:bg-slate-50/50"]) }}>
    {{ $slot }}
</td>
