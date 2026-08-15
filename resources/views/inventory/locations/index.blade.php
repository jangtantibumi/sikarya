@extends('inventory.layout')

@section('title', 'Lokasi Gudang (Zone/Rack/Bin)')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Hirarki Lokasi (Warehouse -> Zone -> Rack -> Bin)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Pemetaan tata letak penyimpanan fisik barang di dalam gudang.</p>
    </div>
</div>

<div class="space-y-6">
    @foreach($warehouses as $wh)
        <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <i class="ph ph-warehouse text-erp-green mr-2"></i> {{ $wh->name }} <span class="text-xs text-gray-600 dark:text-gray-400 font-mono ml-2">({{ $wh->code }})</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($wh->zones as $zone)
                    <div class="bg-gray-50 dark:bg-erp-dark p-4 rounded-lg border border-gray-200 dark:border-erp-border">
                        <div class="text-xs font-bold text-emerald-400 mb-2">{{ $zone->name }} ({{ $zone->code }})</div>
                        @foreach($zone->racks as $rack)
                            <div class="text-xs text-gray-700 dark:text-gray-300 font-semibold mt-2">{{ $rack->name }}</div>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($rack->bins as $bin)
                                    <span class="text-[10px] bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border px-2 py-1 rounded font-mono text-gray-800 dark:text-gray-200 flex items-center">
                                        <i class="ph ph-box text-erp-green mr-1"></i> {{ $bin->name }}
                                        <form method="POST" action="{{ route('inventory.locations.bin.destroy', $bin->id) }}" class="ml-2 inline" onsubmit="return confirm('Hapus bin ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-300">&times;</button>
                                        </form>
                                    </span>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
