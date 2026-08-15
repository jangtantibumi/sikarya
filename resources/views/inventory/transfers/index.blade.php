@extends('inventory.layout')
@section('title', 'Stock Transfer')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Stock Transfer (Mutasi Antar Gudang)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Pemindahan stok antar gudang atau cold storage.</p>
    </div>
    <a href="{{ route('inventory.transfers.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Transfer Baru
    </a>
</div>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">No. Transfer</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Gudang Asal</th>
                <th class="py-3 px-4">Gudang Tujuan</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($transfers as $tr)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green">{{ $tr->number }}</td>
                    <td class="py-3 px-4">{{ $tr->date }}</td>
                    <td class="py-3 px-4 font-medium">{{ optional($tr->sourceWarehouse)->name }}</td>
                    <td class="py-3 px-4 font-medium text-emerald-400">{{ optional($tr->destinationWarehouse)->name }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                            @if($tr->status === 'approved') bg-emerald-500/20 text-emerald-300
                            @elseif($tr->status === 'rejected') bg-rose-500/20 text-rose-300
                            @else bg-amber-500/20 text-amber-300 @endif">
                            {{ $tr->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center space-x-2">
                        <a href="{{ route('inventory.transfers.show', $tr->id) }}" class="text-blue-400 hover:underline"><i class="ph ph-eye text-base"></i></a>
                        @if($tr->status === 'draft')
                            <form method="POST" action="{{ route('inventory.transfers.approve', $tr->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-emerald-400 font-bold hover:underline">Approve</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div>{{ $transfers->links() }}</div>
@endsection
