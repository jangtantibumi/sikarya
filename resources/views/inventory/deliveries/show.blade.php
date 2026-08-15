@extends('inventory.layout')
@section('title', 'Detail Delivery - ' . $delivery->number)
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $delivery->number }}</h1>
            <p class="text-xs text-erp-green">Armada: {{ $delivery->courier_name }} | Tracking: {{ $delivery->tracking_number }}</p>
        </div>
        <a href="{{ route('inventory.deliveries.index') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border mb-6">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Item Dikirim</h3>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Item SKU & Nama</th>
                    <th class="py-3 px-4">Qty Dikirim</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                @foreach($delivery->lines as $line)
                    <tr>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ optional($line->item)->name }} ({{ optional($line->item)->sku }})</td>
                        <td class="py-3 px-4 font-bold text-emerald-400">{{ number_format($line->delivered_qty) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
