@extends('inventory.layout')
@section('title', 'Stock Buku Catatan (Kartu Stok)')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Stock Buku Catatan (Kartu Stok Digital)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Jurnal riwayat mutasi stok lengkap dari seluruh transaksi.</p>
    </div>
</div>

<form method="GET" action="{{ route('inventory.stock-Buku Catatan.index') }}" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Ref Number, SKU, Nama Item..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
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
                <th class="py-3 px-4">Ref Number</th>
                <th class="py-3 px-4">Tipe Transaksi</th>
                <th class="py-3 px-4">Item SKU & Nama</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Kuantitas</th>
                <th class="py-3 px-4">Harga Satuan</th>
                <th class="py-3 px-4">Operator</th>
                <th class="py-3 px-4">Waktu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($movements as $m)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green">{{ $m->reference_number }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                            @if($m->quantity > 0) bg-emerald-500/20 text-emerald-300 @else bg-rose-500/20 text-rose-300 @endif">
                            {{ str_replace('_', ' ', $m->transaction_type) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ optional($m->item)->name }} <span class="text-gray-500 dark:text-gray-500">({{ optional($m->item)->sku }})</span></td>
                    <td class="py-3 px-4">{{ optional($m->warehouse)->name }}</td>
                    <td class="py-3 px-4 font-bold {{ $m->quantity > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ $m->quantity > 0 ? '+'.number_format($m->quantity) : number_format($m->quantity) }}
                    </td>
                    <td class="py-3 px-4 font-mono">Rp {{ number_format($m->unit_cost) }}</td>
                    <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $m->created_by }}</td>
                    <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-[11px]">{{ $m->created_at->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div>{{ $movements->links() }}</div>
@endsection
