@extends('finance::layouts.master')

@section('title', 'Account Groups - ERP Finance')
@section('page_heading', 'Account Groups (Kelompok Akun)')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Account Group</h2>
            <form action="{{ route('finance.account-groups.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode Group</label>
                    <input type="text" name="code" required placeholder="Contoh: 1100" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Group</label>
                    <input type="text" name="name" required placeholder="Contoh: Aset Lancar" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kategori</label>
                    <select name="category" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                        <option value="equity">Equity</option>
                        <option value="revenue">Revenue</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Range Kode Dari</label>
                        <input type="text" name="code_from" placeholder="1100" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Range Kode Ke</label>
                        <input type="text" name="code_to" placeholder="1199" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Jenis Laporan</label>
                    <select name="report_type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="balance_sheet">Balance Sheet (Neraca)</option>
                        <option value="profit_loss">Profit &amp; Loss (Laba Rugi)</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Group
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Account Group</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Group</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Range Kode</th>
                        <th class="px-4 py-3">Laporan</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($groups as $grp)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ $grp->code }}</td>
                            <td class="px-4 py-3">{{ $grp->name }}</td>
                            <td class="px-4 py-3 uppercase text-xs">{{ $grp->category }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $grp->code_from }} - {{ $grp->code_to }}</td>
                            <td class="px-4 py-3 text-xs">{{ str_replace('_', ' ', strtoupper($grp->report_type)) }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.account-groups.destroy', $grp->id) }}" method="POST" onsubmit="return confirm('Hapus group ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Account Group.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
