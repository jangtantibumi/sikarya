@extends('finance::layouts.master')

@section('title', 'Chart of Accounts - ERP Finance')
@section('page_heading', 'Chart of Accounts (CoA)')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-slate-400 text-sm">Kelola struktur bagan akun keuangan (SAP S/4HANA &amp; Odoo Standards)</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Modal / Panel -->
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Akun Baru</h2>
            <form action="{{ route('finance.chart-of-accounts.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode Akun</label>
                    <input type="text" name="code" required placeholder="Contoh: 1110.01" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Akun</label>
                    <input type="text" name="name" required placeholder="Contoh: Kas Utama IDR" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Account Group</label>
                    <select name="account_group_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Group --</option>
                        @foreach($accountGroups as $grp)
                            <option value="{{ $grp->id }}">{{ $grp->code }} - {{ $grp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tipe Akun</label>
                        <select name="type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Posisi Saldo</label>
                        <select name="balance_type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Header Account Parent</label>
                    <select name="parent_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Tanpa Parent (Top Level) --</option>
                        @foreach($accounts->where('is_header', true) as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-4 pt-2">
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_header" value="1" class="rounded bg-slate-900 border-slate-700 text-emerald-600 focus:ring-0">
                        Header Account
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_reconciliation" value="1" class="rounded bg-slate-900 border-slate-700 text-emerald-600 focus:ring-0">
                        Akun Rekonsiliasi
                    </label>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Akun
                </button>
            </form>
        </div>

        <!-- Table Panel -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Chart of Accounts</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Akun</th>
                        <th class="px-4 py-3">Group</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Saldo</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-800/40 transition {{ $acc->is_header ? 'font-bold text-emerald-400 bg-slate-800/20' : '' }}">
                            <td class="px-4 py-3 font-mono">{{ $acc->code }}</td>
                            <td class="px-4 py-3">
                                {{ $acc->name }}
                                @if($acc->is_reconciliation)
                                    <span class="ml-2 px-1.5 py-0.5 text-[10px] bg-blue-500/20 text-blue-300 rounded border border-blue-500/30">Recon</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $acc->accountGroup->name ?? '-' }}</td>
                            <td class="px-4 py-3 uppercase text-xs tracking-wider">{{ $acc->type }}</td>
                            <td class="px-4 py-3 uppercase text-xs">{{ $acc->balance_type }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.chart-of-accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Chart of Accounts terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
