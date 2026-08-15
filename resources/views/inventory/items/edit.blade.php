@extends('inventory.layout')

@section('title', 'Edit Master Item')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Master Item: {{ $item->name }}</h1>
        <a href="{{ route('inventory.items.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('inventory.items.update', $item->id) }}" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Item *</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SKU Code *</label>
                <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                <input type="text" name="barcode" value="{{ old('barcode', $item->barcode) }}" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori *</label>
                <select name="category_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Brand *</label>
                <select name="brand_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    @foreach($brands as $brd)
                        <option value="{{ $brd->id }}" {{ $item->brand_id == $brd->id ? 'selected' : '' }}>{{ $brd->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measure (UoM) *</label>
                <select name="uom_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>{{ $uom->name }} ({{ $uom->symbol }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli *</label>
                <input type="number" name="cost_price" value="{{ old('cost_price', $item->cost_price) }}" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual *</label>
                <input type="number" name="selling_price" value="{{ old('selling_price', $item->selling_price) }}" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock *</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', $item->min_stock) }}" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Stock *</label>
                <input type="number" name="max_stock" value="{{ old('max_stock', $item->max_stock) }}" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Point *</label>
                <input type="number" name="reorder_point" value="{{ old('reorder_point', $item->reorder_point) }}" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Item</label>
            <textarea name="description" rows="3" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">{{ old('description', $item->description) }}</textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-erp-border">
            <a href="{{ route('inventory.items.index') }}" class="bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg text-xs">Batal</a>
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs shadow-lg">Update Item</button>
        </div>
    </form>
</div>
@endsection
