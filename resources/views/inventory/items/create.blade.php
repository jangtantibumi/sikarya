@extends('inventory.layout')

@section('title', 'Tambah Master Item')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Master Item Baru</h1>
        <a href="{{ route('inventory.items.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Batal & Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('inventory.items.store') }}" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Item *</label>
                <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SKU Code *</label>
                <input type="text" name="sku" value="FNB-{{ rand(1000, 9999) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                <input type="text" name="barcode" value="899{{ rand(100000000, 999999999) }}" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori *</label>
                <select name="category_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @if($categories->isEmpty())
                <div class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Belum ada Kategori? <a href="{{ route('inventory.categories.index') }}" class="text-erp-green hover:underline">Buat di sini</a></div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Merek (Brand) *</label>
                <select name="brand_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <option value="">Pilih Merek</option>
                    @foreach($brands as $brd)
                        <option value="{{ $brd->id }}">{{ $brd->name }}</option>
                    @endforeach
                </select>
                @if($brands->isEmpty())
                <div class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Belum ada Merek? <a href="{{ route('inventory.brands.index') }}" class="text-erp-green hover:underline">Buat di sini</a></div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan Ukur (Pcs, Dus, Kg) *</label>
                <select name="uom_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <option value="">Pilih Satuan</option>
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->id }}">{{ $uom->name }} ({{ $uom->symbol }})</option>
                    @endforeach
                </select>
                @if($uoms->isEmpty())
                <div class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Belum ada Satuan? <a href="{{ route('inventory.uoms.index') }}" class="text-erp-green hover:underline">Buat di sini</a></div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli (Cost Price) *</label>
                <input type="number" name="cost_price" value="50000" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual (Selling Price) *</label>
                <input type="number" name="selling_price" value="75000" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock *</label>
                <input type="number" name="min_stock" value="10" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Stock *</label>
                <input type="number" name="max_stock" value="500" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Point *</label>
                <input type="number" name="reorder_point" value="20" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Item</label>
            <textarea name="description" rows="3" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green"></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-erp-border">
            <a href="{{ route('inventory.items.index') }}" class="bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg text-xs">Batal</a>
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs shadow-lg">Simpan Item</button>
        </div>
    </form>
</div>
@endsection
