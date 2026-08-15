@extends('inventory.layout')

@section('title', 'Detail Item - ' . $item->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $item->name }}</h1>
            <p class="text-xs text-erp-green font-mono">SKU: {{ $item->sku }} | Barcode: {{ $item->barcode ?? '-' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.items.print', $item->id) }}" target="_blank" class="bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border hover:bg-erp-border text-gray-700 dark:text-gray-300 font-medium py-2 px-3 rounded-lg text-xs flex items-center">
                <i class="ph ph-printer mr-1.5 text-sm"></i> Print Label
            </a>
            <a href="{{ route('inventory.items.edit', $item->id) }}" class="bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
                <i class="ph ph-pencil-simple mr-1.5 text-sm"></i> Edit Item
            </a>
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
            <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Kategori & Brand</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white block">{{ optional($item->category)->name }}</span>
            <span class="text-xs text-erp-green block">{{ optional($item->brand)->name }}</span>
        </div>

        <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
            <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Harga Beli / Jual</span>
            <span class="text-sm font-mono text-gray-700 dark:text-gray-300 block">Beli: Rp {{ number_format($item->cost_price) }}</span>
            <span class="text-sm font-mono text-emerald-400 block font-bold">Jual: Rp {{ number_format($item->selling_price) }}</span>
        </div>

        <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
            <span class="text-xs text-gray-600 dark:text-gray-400 block mb-1">Total Stok Fisik</span>
            <span class="text-2xl font-bold text-emerald-400 block">{{ number_format($item->total_stock) }} {{ optional($item->uom)->symbol }}</span>
            <span class="text-[11px] text-gray-600 dark:text-gray-400">Min: {{ $item->min_stock }} | Max: {{ $item->max_stock }}</span>
        </div>
    </div>

    <!-- Stock Locations -->
    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Sebaran Stok di Gudang</h2>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Gudang</th>
                    <th class="py-3 px-4">Bin Lokasi</th>
                    <th class="py-3 px-4">Quantity</th>
                    <th class="py-3 px-4">Reserved Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @forelse($item->stockSummaries as $s)
                    <tr>
                        <td class="py-3 px-4 font-semibold">{{ optional($s->warehouse)->name }}</td>
                        <td class="py-3 px-4 font-mono">{{ optional($s->bin)->name ?? '-' }}</td>
                        <td class="py-3 px-4 font-bold text-emerald-400">{{ number_format($s->quantity) }}</td>
                        <td class="py-3 px-4 text-amber-400">{{ number_format($s->reserved_qty) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-500">Stok belum dialokasikan di gudang manapun.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
