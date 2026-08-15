@extends('finance::layouts.master')

@section('title', 'Profit Centers - ERP Finance')
@section('page_heading', 'Profit Center Management')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Profit Center</h2>
            <form action="{{ route('finance.profit-centers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode PC</label>
                    <input type="text" name="code" required placeholder="PC-RETAIL-01" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Profit Center</label>
                    <input type="text" name="name" required placeholder="SBU Division Retail" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Parent Profit Center</label>
                    <select name="parent_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Tanpa Parent (Top Level) --</option>
                        @foreach($profitCenters as $pc)
                            <option value="{{ $pc->id }}">{{ $pc->code }} - {{ $pc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Manager</label>
                        <input type="text" name="manager_name" placeholder="Hendrik P." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Segment</label>
                        <input type="text" name="segment" placeholder="B2C Retail" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Profit Center
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Profit Center</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Profit Center</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Manager</th>
                        <th class="px-4 py-3">Segment</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($profitCenters as $pc)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ $pc->code }}</td>
                            <td class="px-4 py-3">{{ $pc->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $pc->parent->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $pc->manager_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $pc->segment ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.profit-centers.destroy', $pc->id) }}" method="POST" onsubmit="return confirm('Hapus Profit Center ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Profit Center.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
