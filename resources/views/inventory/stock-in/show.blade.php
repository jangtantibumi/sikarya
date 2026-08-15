@extends('inventory.layout')
@section('title', 'Detail Stock In - ' . $stockIn->number)
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stockIn->number }}</h1>
            <p class="text-xs text-erp-green">Tanggal: {{ $stockIn->date }} | Supplier: {{ $stockIn->supplier_name }}</p>
        </div>
        <div class="flex space-x-3">
            @if($stockIn->status === 'draft')
                <form method="POST" action="{{ route('inventory.stock-in.approve', $stockIn->id) }}">
                    @csrf
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-gray-900 font-bold py-2 px-4 rounded-lg text-xs">Approve & Tambah Stok</button>
                </form>
            @endif
            <a href="{{ route('inventory.stock-in.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
                <i class="ph ph-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border mb-6">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Item yang Diterima</h3>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Item SKU & Nama</th>
                    <th class="py-3 px-4">Qty</th>
                    <th class="py-3 px-4">Harga Satuan</th>
                    <th class="py-3 px-4">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @foreach($stockIn->lines as $line)
                    <tr>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ optional($line->item)->name }} ({{ optional($line->item)->sku }})</td>
                        <td class="py-3 px-4 font-bold text-emerald-400">{{ number_format($line->quantity) }}</td>
                        <td class="py-3 px-4 font-mono">Rp {{ number_format($line->unit_price) }}</td>
                        <td class="py-3 px-4 font-mono font-bold">Rp {{ number_format($line->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
