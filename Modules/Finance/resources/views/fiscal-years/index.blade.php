@extends('finance::layouts.master')

@section('title', 'Fiscal Years & Periods - ERP Finance')
@section('page_heading', 'Fiscal Year & Period Management')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Buka Tahun Buku Baru</h2>
            <form action="{{ route('finance.fiscal-years.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode FY</label>
                    <input type="text" name="code" required placeholder="Contoh: FY2026" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Tahun Buku</label>
                    <input type="text" name="name" required placeholder="Contoh: Tahun Buku 2026" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tgl Mulai</label>
                        <input type="date" name="start_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tgl Selesai</label>
                        <input type="date" name="end_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <p class="text-xs text-slate-400">Sistem akan secara otomatis membuat 12 Periode Bulanan saat Tahun Buku dibuat.</p>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Generate Fiscal Year &amp; Periods
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto space-y-6">
            <h2 class="text-lg font-bold text-white">Daftar Tahun Buku &amp; Status Periode</h2>

            @forelse($fiscalYears as $fy)
                <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                        <div>
                            <span class="font-mono font-bold text-emerald-400 text-base">{{ $fy->code }}</span>
                            <span class="text-slate-300 ml-2 font-semibold">{{ $fy->name }}</span>
                            <span class="text-xs text-slate-500 ml-3">({{ $fy->start_date->format('d M Y') }} - {{ $fy->end_date->format('d M Y') }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 text-xs rounded border {{ $fy->is_closed ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' }}">
                                {{ $fy->is_closed ? 'CLOSED' : 'OPEN' }}
                            </span>
                        </div>
                    </div>

                    <!-- Periode Bulanan -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 pt-1">
                        @foreach($fy->periods as $fp)
                            <div class="bg-slate-800/60 p-2 rounded-lg border border-slate-700/50 flex flex-col justify-between">
                                <div class="text-xs font-bold text-white">P{{ $fp->period_number }}: {{ $fp->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($fp->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($fp->end_date)->format('d M Y') }}</div>
                                <div class="mt-2">
                                    <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded {{ $fp->status === 'open' ? 'bg-emerald-500/20 text-emerald-300' : ($fp->status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300') }}">
                                        {{ $fp->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-500 py-6">Belum ada Fiscal Year terdaftar.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
