@props([
    'align' => 'left',
    'sortable' => false,
    'direction' => 'asc'
])

@php
    $alignmentClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align];
@endphp

<th scope="col" {{ $attributes->merge(['class' => "px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap {$alignmentClass}"]) }}>
    @if($sortable)
        <button type="button" class="group inline-flex items-center space-x-1 focus:outline-none hover:text-slate-800 transition-colors w-full {{ $alignmentClass == 'text-right' ? 'justify-end' : ($alignmentClass == 'text-center' ? 'justify-center' : 'justify-start') }}">
            <span>{{ $slot }}</span>
            <span class="flex flex-col text-[8px] opacity-40 group-hover:opacity-100 transition-opacity">
                <i class="fa-solid fa-chevron-up {{ $direction === 'asc' ? 'text-[#0C3527] font-bold' : '' }}"></i>
                <i class="fa-solid fa-chevron-down {{ $direction === 'desc' ? 'text-[#0C3527] font-bold' : '-mt-1' }}"></i>
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</th>
