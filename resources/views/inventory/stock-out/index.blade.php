@extends('inventory.layout')
@section('title', 'Stock Out (Pengeluaran)')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Stock Out (Pengeluaran Barang)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Pengeluaran bahan baku dari gudang untuk operasional resto/kitchen.</p>
    </div>
    <a href="{{ route('inventory.stock-out.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Transaksi Stock Out Baru
    </a>
</div>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">No. Transaksi</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Penerima/Tujuan</th>
                <th class="py-3 px-4">Gudang Asal</th>
                <th class="py-3 px-4">Total Amount</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($stockOuts as $st)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green">{{ $st->number }}</td>
                    <td class="py-3 px-4">{{ $st->date }}</td>
                    <td class="py-3 px-4 font-medium">{{ $st->recipient_name }}</td>
                    <td class="py-3 px-4">{{ optional($st->warehouse)->name }}</td>
                    <td class="py-3 px-4 font-mono">Rp {{ number_format($st->total_amount) }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                            @if($st->status === 'approved') bg-emerald-500/20 text-emerald-300
                            @elseif($st->status === 'rejected') bg-rose-500/20 text-rose-300
                            @else bg-amber-500/20 text-amber-300 @endif">
                            {{ $st->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center space-x-2">
                        <a href="{{ route('inventory.stock-out.show', $st->id) }}" class="text-blue-400 hover:underline"><i class="ph ph-eye text-base"></i></a>
                        @if($st->status === 'draft')
                            <form method="POST" action="{{ route('inventory.stock-out.approve', $st->id) }}" class="inline">
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
<div>{{ $stockOuts->links() }}</div>
@endsection
