<div class="overflow-hidden bg-white border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-2xl">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            @if(isset($head))
                <thead class="bg-slate-50/50">
                    <tr>
                        {{ $head }}
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-50 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    
    @if(isset($footer))
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $footer }}
        </div>
    @endif
</div>
