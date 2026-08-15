@extends('inventory.layout')

@section('title', 'Stock Summary')

@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Ringkasan Stok (Stock Summary)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Posisi real-time kuantitas stok per item, per gudang, dan bin lokasi.</p>
    </div>
    <a href="{{ route('inventory.stock-summary.export') }}" class="bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border hover:bg-erp-border text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-download-simple mr-1.5 text-sm"></i> Export CSV
    </a>
</div>

<form method="GET" action="{{ route('inventory.stock-summary.index') }}" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Item atau SKU..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
    </div>
    <div class="w-48">
        <select name="warehouse_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-erp-border text-gray-900 dark:text-white font-medium py-1.5 px-4 rounded-lg text-xs">Filter</button>
</form>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">Item SKU & Nama</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Bin Lokasi</th>
                <th class="py-3 px-4">Stok Fisik (Qty)</th>
                <th class="py-3 px-4">Reserved</th>
                <th class="py-3 px-4">Allocated</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @forelse($stockSummaries as $s)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4">
                        <div class="font-bold text-gray-900 dark:text-white">{{ optional($s->item)->name }}</div>
                        <div class="text-[10px] text-erp-green font-mono">{{ optional($s->item)->sku }}</div>
                    </td>
                    <td class="py-3 px-4 font-medium">{{ optional($s->warehouse)->name }}</td>
                    <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-400">{{ optional($s->bin)->name ?? 'General Bin' }}</td>
                    <td class="py-3 px-4 font-bold text-emerald-400">{{ number_format($s->quantity) }}</td>
                    <td class="py-3 px-4 text-amber-400 font-medium">{{ number_format($s->reserved_qty) }}</td>
                    <td class="py-3 px-4 text-blue-400 font-medium">{{ number_format($s->allocated_qty) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-500">Tidak ada data stok ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    {{ $stockSummaries->links() }}
</div>
@endsection
