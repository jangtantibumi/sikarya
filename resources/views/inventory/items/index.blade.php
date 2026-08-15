@extends('inventory.layout')

@section('title', 'Master Items')

@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Master Data Items</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Kelola katalog barang, SKU, harga, dan reorder point.</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('inventory.items.export') }}" class="bg-white dark:bg-erp-card hover:bg-erp-border text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-erp-border font-medium py-2 px-3 rounded-lg text-xs flex items-center">
            <i class="ph ph-download-simple mr-1.5 text-sm"></i> Export CSV
        </a>
        <a href="{{ route('inventory.items.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg shadow-lg text-xs flex items-center">
            <i class="ph ph-plus mr-1.5 text-sm"></i> Tambah Item Baru
        </a>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('inventory.items.index') }}" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Item, SKU, atau Barcode..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
    </div>
    <div class="w-48">
        <select name="category_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-48">
        <select name="brand_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            <option value="">Semua Brand</option>
            @foreach($brands as $brd)
                <option value="{{ $brd->id }}" {{ request('brand_id') == $brd->id ? 'selected' : '' }}>{{ $brd->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-erp-border hover:bg-gray-700 text-gray-900 dark:text-white font-medium py-1.5 px-4 rounded-lg text-xs">Filter</button>
    <a href="{{ route('inventory.items.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white">Reset</a>
</form>

<!-- Table -->
<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">SKU & Barcode</th>
                <th class="py-3 px-4">Nama Item</th>
                <th class="py-3 px-4">Kategori & Brand</th>
                <th class="py-3 px-4">UoM</th>
                <th class="py-3 px-4">Harga Beli / Jual</th>
                <th class="py-3 px-4">Stok Fisik</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @forelse($items as $item)
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="py-3 px-4 font-mono">
                        <span class="text-erp-green font-semibold">{{ $item->sku }}</span>
                        <div class="text-[10px] text-gray-500 dark:text-gray-500">{{ $item->barcode ?? '-' }}</div>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ $item->name }}</td>
                    <td class="py-3 px-4">
                        <div>{{ optional($item->category)->name ?? '-' }}</div>
                        <div class="text-[10px] text-gray-600 dark:text-gray-400">{{ optional($item->brand)->name ?? '-' }}</div>
                    </td>
                    <td class="py-3 px-4 font-medium text-gray-700 dark:text-gray-300">{{ optional($item->uom)->symbol ?? optional($item->uom)->name }}</td>
                    <td class="py-3 px-4 font-mono">
                        <div class="text-gray-700 dark:text-gray-300">Rp {{ number_format($item->cost_price) }}</div>
                        <div class="text-[10px] text-emerald-400">Rp {{ number_format($item->selling_price) }}</div>
                    </td>
                    <td class="py-3 px-4 font-bold text-emerald-400">
                        {{ number_format($item->total_stock) }}
                    </td>
                    <td class="py-3 px-4 text-center space-x-2">
                        <a href="{{ route('inventory.items.show', $item->id) }}" class="text-blue-400 hover:text-blue-300" title="View"><i class="ph ph-eye text-base"></i></a>
                        <a href="{{ route('inventory.items.edit', $item->id) }}" class="text-amber-400 hover:text-amber-300" title="Edit"><i class="ph ph-pencil-simple text-base"></i></a>
                        <form method="POST" action="{{ route('inventory.items.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300" title="Delete"><i class="ph ph-trash text-base"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-500">Tidak ada item ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection
