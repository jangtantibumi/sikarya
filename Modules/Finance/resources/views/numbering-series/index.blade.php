@extends('finance::layouts.master')

@section('title', 'Numbering Series - ERP Finance')
@section('page_heading', 'Numbering Series (Penomoran Dokumen)')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Series Penomoran</h2>
            <form action="{{ route('finance.numbering-series.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Module Code</label>
                        <input type="text" name="module_code" required value="FINANCE" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Document Type</label>
                        <input type="text" name="document_type" required placeholder="JOURNAL_ENTRY" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Prefix Format</label>
                    <input type="text" name="prefix" required placeholder="JV-{YYYY}-{MM}-" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Variabel format: {YYYY}, {YY}, {MM}, {DD}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Panjang Digit (Length)</label>
                        <input type="number" name="length" value="5" min="1" max="10" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Siklus Reset</label>
                        <select name="reset_cycle" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="yearly">Tahunan (Yearly)</option>
                            <option value="monthly">Bulanan (Monthly)</option>
                            <option value="daily">Harian (Daily)</option>
                            <option value="never">Tanpa Reset (Never)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Numbering Series
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Series Penomoran</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Modul &amp; Tipe</th>
                        <th class="px-4 py-3">Prefix Format</th>
                        <th class="px-4 py-3">Contoh No. Terakhir</th>
                        <th class="px-4 py-3">No. Urut</th>
                        <th class="px-4 py-3">Siklus Reset</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($series as $s)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-emerald-400">{{ $s->module_code }}</span>
                                <div class="text-xs text-slate-400 font-mono">{{ $s->document_type }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-amber-300">{{ $s->prefix }}</td>
                            <td class="px-4 py-3 font-mono text-xs font-bold text-white bg-slate-900/40 rounded">{{ $s->sample_number }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->current_number }}</td>
                            <td class="px-4 py-3 capitalize text-xs">{{ $s->reset_cycle }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.numbering-series.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus series ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Numbering Series.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
