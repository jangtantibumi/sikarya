@extends('inventory.layout')
@section('title', 'Barcode Generator')
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Barcode Management</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Generasi dan registrasi kode barcode item (EAN-13, CODE128).</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border h-fit">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Registrasi Barcode Baru</h3>
        <form method="POST" action="{{ route('inventory.barcodes.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Item *</label>
                <select name="item_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                    @foreach($items as $itm)
                        <option value="{{ $itm->id }}">{{ $itm->name }} ({{ $itm->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nilai Barcode *</label>
                <input type="text" name="barcode" value="899{{ rand(100000000, 999999999) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Barcode *</label>
                <select name="barcode_type" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                    <option value="CODE128">CODE128</option>
                    <option value="EAN13">EAN13</option>
                    <option value="QR">QR Code</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 rounded-lg text-xs">Simpan Barcode</button>
        </form>
    </div>

    <div class="md:col-span-2 glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Nilai Barcode</th>
                    <th class="py-3 px-4">Nama Item SKU</th>
                    <th class="py-3 px-4">Tipe</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @foreach($barcodes as $bc)
                    <tr class="hover:bg-white/5">
                        <td class="py-3 px-4 font-mono text-erp-green font-bold">{{ $bc->barcode }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ optional($bc->item)->name }} <span class="text-gray-500 dark:text-gray-500 font-normal">({{ optional($bc->item)->sku }})</span></td>
                        <td class="py-3 px-4 font-mono text-gray-700 dark:text-gray-300">{{ $bc->barcode_type }}</td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('inventory.barcodes.destroy', $bc->id) }}" class="inline" onsubmit="return confirm('Hapus Barcode ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300"><i class="ph ph-trash text-base"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $barcodes->links() }}</div>
    </div>
</div>
@endsection
