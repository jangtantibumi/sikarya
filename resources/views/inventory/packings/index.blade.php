@extends('inventory.layout')
@section('title', 'Packing Operations')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Packing (Pengemasan Kardus/Box)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Pengemasan barang hasil picking ke dalam kardus pengiriman.</p>
    </div>
    <a href="{{ route('inventory.packings.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Packing Baru
    </a>
</div>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">No. Packing</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Packer Staff</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($packings as $pac)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green">{{ $pac->number }}</td>
                    <td class="py-3 px-4">{{ $pac->date }}</td>
                    <td class="py-3 px-4 font-medium">{{ $pac->packer_name }}</td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 uppercase">{{ $pac->status }}</span></td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('inventory.packings.show', $pac->id) }}" class="text-blue-400 hover:underline"><i class="ph ph-eye text-base"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div>{{ $packings->links() }}</div>
@endsection
