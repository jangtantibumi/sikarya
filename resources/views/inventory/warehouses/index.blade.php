@extends('inventory.layout')

@section('title', 'Master Warehouses')

@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Master Gudang (Warehouses)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Jaringan lokasi gudang, cold storage, dan pusat distribusi.</p>
    </div>
    <a href="{{ route('inventory.warehouses.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Tambah Gudang Baru
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    @foreach($warehouses as $wh)
        <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-3">
                    <span class="text-xs font-mono text-erp-green font-bold bg-emerald-500/10 px-2 py-0.5 rounded">{{ $wh->code }}</span>
                    <span class="text-[10px] text-emerald-400 bg-emerald-900/40 px-2 py-0.5 rounded border border-emerald-500/30">Aktif</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $wh->name }}</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">{{ $wh->address }}</p>
                <div class="space-y-1 text-xs text-gray-700 dark:text-gray-300">
                    <div><i class="ph ph-user text-gray-600 dark:text-gray-400 mr-2"></i> Manager: {{ $wh->manager_name ?? 'N/A' }}</div>
                    <div><i class="ph ph-phone text-gray-600 dark:text-gray-400 mr-2"></i> {{ $wh->phone ?? 'N/A' }}</div>
                    <div><i class="ph ph-envelope text-gray-600 dark:text-gray-400 mr-2"></i> {{ $wh->email ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="pt-6 mt-6 border-t border-gray-200 dark:border-erp-border flex justify-between items-center text-xs">
                <span class="text-gray-600 dark:text-gray-400 font-mono">{{ $wh->zones->count() }} Zones</span>
                <a href="{{ route('inventory.warehouses.show', $wh->id) }}" class="text-erp-green hover:underline flex items-center font-medium">
                    Lihat Detail <i class="ph ph-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
