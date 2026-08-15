@extends('finance::layouts.master')

@section('title', 'Exchange Rates - ERP Finance')
@section('page_heading', 'Exchange Rate Master (Kurs Mata Uang)')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Exchange Rate</h2>
            <form action="{{ route('finance.exchange-rates.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Dari Currency (From)</label>
                    <select name="from_currency_id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($currencies as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->code }} - {{ $curr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Ke Currency (To)</label>
                    <select name="to_currency_id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($currencies as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->code }} - {{ $curr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tgl Rate</label>
                        <input type="date" name="rate_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tipe Rate</label>
                        <select name="rate_type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="spot">Spot Rate</option>
                            <option value="monthly">Monthly Average</option>
                            <option value="corporate">Corporate Rate</option>
                            <option value="tax">Tax Rate (KMK)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nilai Kurs (Rate)</label>
                    <input type="number" step="0.000001" name="rate" required placeholder="15800.00" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Exchange Rate
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Kurs Mata Uang</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pasangan Kurs</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Nilai Kurs</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($rates as $r)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 text-xs">{{ $r->rate_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400">
                                {{ $r->fromCurrency->code ?? '-' }} &rarr; {{ $r->toCurrency->code ?? '-' }}
                            </td>
                            <td class="px-4 py-3 uppercase text-xs">{{ $r->rate_type }}</td>
                            <td class="px-4 py-3 font-mono text-white font-semibold">{{ number_format($r->rate, 4) }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.exchange-rates.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus kurs ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada Exchange Rate terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
