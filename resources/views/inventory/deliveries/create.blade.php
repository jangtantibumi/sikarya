@extends('inventory.layout')
@section('title', 'Jadwalkan Pengiriman Baru')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Input Surat Jalan / Delivery</h1>
    <form method="POST" action="{{ route('inventory.deliveries.store') }}" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. Surat Jalan *</label>
                <input type="text" name="number" value="DEL-202608-{{ rand(1000, 9999) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal *</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Armada / Kurir *</label>
                <input type="text" name="courier_name" value="Suba Express Box #01" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Pengiriman *</label>
            <textarea name="delivery_address" rows="2" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">Jl. Boulevard Utama No. 88, Jakarta Selatan</textarea>
        </div>

        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-erp-border">
            <h3 class="text-xs font-bold text-erp-green uppercase tracking-wider">Item Dikirim</h3>
            <div class="grid grid-cols-12 gap-3 items-center">
                <div class="col-span-8">
                    <label class="block text-[11px] text-gray-600 dark:text-gray-400">Pilih Item</label>
                    <select name="items[0][item_id]" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                        @foreach($items as $itm)
                            <option value="{{ $itm->id }}">{{ $itm->name }} ({{ $itm->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-[11px] text-gray-600 dark:text-gray-400">Qty Dikirim</label>
                    <input type="number" name="items[0][delivered_qty]" value="20" min="1" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white"></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-erp-border">
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs">Simpan Surat Jalan</button>
        </div>
    </form>
</div>
@endsection
