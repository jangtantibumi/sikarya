@extends('inventory.layout')
@section('title', 'Serial Numbers')
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Serial Numbers Tracking</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Pencatatan nomor seri per unit mesin & perlengkapan barista/kitchen.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border h-fit">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Tambah Serial Number</h3>
        <form method="POST" action="{{ route('inventory.serial-numbers.store') }}" class="space-y-4">
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
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Gudang *</label>
                <select name="warehouse_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Number *</label>
                <input type="text" name="serial_number" value="SN-FNB-{{ rand(100000, 999999) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                <select name="status" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                    <option value="available">Available (Tersedia)</option>
                    <option value="reserved">Reserved</option>
                    <option value="sold">Sold</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 rounded-lg text-xs">Simpan Serial Number</button>
        </form>
    </div>

    <div class="md:col-span-2 glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Serial Number</th>
                    <th class="py-3 px-4">Nama Item</th>
                    <th class="py-3 px-4">Gudang</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @foreach($serials as $sn)
                    <tr class="hover:bg-white/5">
                        <td class="py-3 px-4 font-mono text-erp-green font-bold">{{ $sn->serial_number }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ optional($sn->item)->name }}</td>
                        <td class="py-3 px-4">{{ optional($sn->warehouse)->name }}</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-300 uppercase font-semibold">{{ $sn->status }}</span></td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('inventory.serial-numbers.destroy', $sn->id) }}" class="inline" onsubmit="return confirm('Hapus Serial Number ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300"><i class="ph ph-trash text-base"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $serials->links() }}</div>
    </div>
</div>
@endsection
