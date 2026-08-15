@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => []
])

<div class="px-6 py-8 md:px-8 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            @if(!empty($breadcrumbs))
                <nav class="flex text-sm text-slate-500 mb-2 font-medium" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        @foreach($breadcrumbs as $label => $url)
                            <li class="inline-flex items-center">
                                @if(!$loop->first)
                                    <i class="fa-solid fa-chevron-right text-[10px] mx-2 text-slate-400"></i>
                                @endif
                                @if(is_numeric($label))
                                    <span class="text-slate-400">{{ $url }}</span>
                                @else
                                    <a href="{{ $url }}" class="hover:text-[#0C3527] transition-colors" wire:navigate>{{ $label }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            <h1 class="text-2xl font-bold text-slate-800 tracking-tight leading-none">{{ $title }}</h1>
            
            @if($subtitle)
                <p class="mt-1.5 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-3 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>

    <!-- Main Content Section -->
    <div class="space-y-6">
        {{ $slot }}
    </div>
</div>
