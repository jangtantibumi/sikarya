@extends('inventory.layout')

@section('title', 'Detail Gudang - ' . $warehouse->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $warehouse->name }}</h1>
            <p class="text-xs text-erp-green font-mono">Kode: {{ $warehouse->code }} | Manager: {{ $warehouse->manager_name ?? '-' }}</p>
        </div>
        <a href="{{ route('inventory.warehouses.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <!-- Zones List -->
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border mb-8">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Hirarki Zona, Rak & Bin</h2>
        <div class="space-y-4">
            @forelse($warehouse->zones as $zone)
                <div class="bg-gray-50 dark:bg-erp-dark p-4 rounded-lg border border-gray-200 dark:border-erp-border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-emerald-400">{{ $zone->name }} ({{ $zone->code }})</span>
                        <span class="text-[10px] text-gray-600 dark:text-gray-400 font-mono">{{ $zone->racks->count() }} Racks</span>
                    </div>
                    <div class="pl-4 space-y-2 border-l border-gray-200 dark:border-erp-border">
                        @foreach($zone->racks as $rack)
                            <div>
                                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $rack->name }} ({{ $rack->code }})</div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach($rack->bins as $bin)
                                        <span class="text-[10px] bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border px-2 py-0.5 rounded font-mono text-gray-700 dark:text-gray-300">
                                            <i class="ph ph-map-pin text-erp-green mr-1"></i>{{ $bin->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-xs text-gray-500 dark:text-gray-500 py-4 text-center">Belum ada zona lokasi dikonfigurasi.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
