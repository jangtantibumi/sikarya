@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow duration-300 ' . $class]) }}>
    @if($title || $subtitle || isset($header))
        <div class="px-6 py-5 border-b border-slate-50 flex justify-between items-center">
            <div>
                @if($title)
                    <h3 class="text-lg font-bold text-slate-800 leading-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            
            @if(isset($header))
                <div>
                    {{ $header }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 rounded-b-2xl">
            {{ $footer }}
        </div>
    @endif
</div>
