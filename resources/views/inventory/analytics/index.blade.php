@extends('inventory.layout')
@section('title', 'Analytics Gudang & Stok')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Gudang & Stok Analytics</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Grafik tren transaksi dan distribusi stok.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Ringkasan Tren Transaksi Mutasi</h3>
        <div class="space-y-3">
            @foreach($movementsByMonth as $m)
                <div class="flex justify-between items-center bg-gray-50 dark:bg-erp-dark p-3 rounded-lg border border-gray-200 dark:border-erp-border">
                    <div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white block">{{ $m->month }}</span>
                        <span class="text-[10px] text-erp-green uppercase font-mono">{{ str_replace('_', ' ', $m->transaction_type) }}</span>
                    </div>
                    <span class="text-sm font-bold text-emerald-400 font-mono">{{ number_format($m->total_trans) }} Transaksi</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Sebaran Item Per Kategori</h3>
        <div class="space-y-2">
            @foreach($categoryStock as $cat)
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                    <span class="font-bold text-erp-green font-mono">{{ $cat->items_count }} Items</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
