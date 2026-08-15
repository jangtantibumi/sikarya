@props([
    'type' => 'block', // text, block, avatar, card
    'lines' => 1,
    'class' => ''
])

@if($type === 'card')
    <div class="card p-6 w-full {{ $class }}">
        <div class="animate-pulse flex space-x-4">
            <div class="rounded-full bg-slate-200 h-10 w-10"></div>
            <div class="flex-1 space-y-6 py-1">
                <div class="h-2 bg-slate-200 rounded"></div>
                <div class="space-y-3">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="h-2 bg-slate-200 rounded col-span-2"></div>
                        <div class="h-2 bg-slate-200 rounded col-span-1"></div>
                    </div>
                    <div class="h-2 bg-slate-200 rounded"></div>
                </div>
            </div>
        </div>
    </div>
@elseif($type === 'avatar')
    <div class="animate-pulse rounded-full bg-slate-200 {{ $class ?: 'h-10 w-10' }}"></div>
@elseif($type === 'text')
    <div class="space-y-3 w-full {{ $class }}">
        @for($i = 0; $i < $lines; $i++)
            <div class="animate-pulse h-2.5 bg-slate-200 rounded-full w-full @if($i === $lines - 1 && $lines > 1) max-w-[70%] @endif"></div>
        @endfor
    </div>
@else
    <div class="animate-pulse bg-slate-200 rounded-lg w-full {{ $class ?: 'h-32' }}"></div>
@endif
