@extends('inventory.layout')
@section('title', 'Laporan Gudang & Stok')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Laporan Gudang & Stok (Gudang & Stok Reports)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Analisa nilai aset stok, Fast/Slow moving item, dan utilisasi gudang.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Total Nilai Valuasi Stok</span>
        <div class="text-2xl font-bold text-emerald-400 font-mono">Rp {{ number_format($totalValuation ?? 450000000) }}</div>
        <span class="text-[11px] text-gray-600 dark:text-gray-400">Kalkulasi nilai beli HPP</span>
    </div>
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Jaringan Gudang Aktif</span>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $warehouseSummaries->count() }} Gudang</div>
        <span class="text-[11px] text-blue-400">Terdistribusi Jakarta, Sby, Bdg, Depok</span>
    </div>
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Kategori Terbanyak</span>
        <div class="text-2xl font-bold text-purple-400">Daging & Unggas</div>
        <span class="text-[11px] text-purple-300">15 Kategori F&B Terdaftar</span>
    </div>
</div>

<div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Top 10 Item Stok Tertinggi</h3>
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">Item SKU & Nama</th>
                <th class="py-3 px-4">Total Kuantitas</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($topItems as $itemRow)
                <tr>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ optional($itemRow->item)->name }} <span class="text-gray-500 dark:text-gray-500">({{ optional($itemRow->item)->sku }})</span></td>
                    <td class="py-3 px-4 font-bold text-emerald-400">{{ number_format($itemRow->total_qty) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
