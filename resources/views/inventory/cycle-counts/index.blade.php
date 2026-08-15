@extends('inventory.layout')
@section('title', 'Cycle Count / Stock Opname')
@section('content')
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Cycle Count & Stock Opname</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Verifikasi perhitungan fisik barang vs data sistem secara berkala.</p>
    </div>
    <a href="{{ route('inventory.cycle-counts.create') }}" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Input Hasil Stock Opname
    </a>
</div>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">No. Document</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Auditor / Tim</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            @foreach($cycleCounts as $cc)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green">{{ $cc->number }}</td>
                    <td class="py-3 px-4">{{ $cc->date }}</td>
                    <td class="py-3 px-4 font-medium">{{ optional($cc->warehouse)->name }}</td>
                    <td class="py-3 px-4">{{ $cc->conducted_by }}</td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 uppercase">{{ $cc->status }}</span></td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('inventory.cycle-counts.show', $cc->id) }}" class="text-blue-400 hover:underline"><i class="ph ph-eye text-base"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div>{{ $cycleCounts->links() }}</div>
@endsection
