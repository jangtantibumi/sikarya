@extends('inventory.layout')
@section('title', 'Brand Gudang & Stok')
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Brand & Produsen</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Daftar merk dagang supplier dan pabrikan bahan F&B.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border h-fit">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Tambah Brand Baru</h3>
        <form method="POST" action="{{ route('inventory.brands.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Brand *</label>
                <input type="text" name="code" value="BRD-{{ rand(100, 999) }}" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Brand *</label>
                <input type="text" name="name" placeholder="mis. Anchor" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white"></textarea>
            </div>
            <button type="submit" class="w-full bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 rounded-lg text-xs">Simpan Brand</button>
        </form>
    </div>

    <div class="md:col-span-2 glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Kode</th>
                    <th class="py-3 px-4">Nama Brand</th>
                    <th class="py-3 px-4">Jumlah Item</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @foreach($brands as $brd)
                    <tr class="hover:bg-white/5">
                        <td class="py-3 px-4 font-mono text-erp-green font-semibold">{{ $brd->code }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ $brd->name }}</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-mono">{{ $brd->items_count }} items</td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('inventory.brands.destroy', $brd->id) }}" class="inline" onsubmit="return confirm('Hapus brand ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300"><i class="ph ph-trash text-base"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $brands->links() }}</div>
    </div>
</div>
@endsection
