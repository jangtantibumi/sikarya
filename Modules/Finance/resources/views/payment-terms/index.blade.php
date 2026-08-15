@extends('finance::layouts.master')

@section('title', 'Payment Terms - ERP Finance')
@section('page_heading', 'Payment Terms (Syarat Pembayaran)')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Payment Term</h2>
            <form action="{{ route('finance.payment-terms.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode Term</label>
                    <input type="text" name="code" required placeholder="NET30, COD, 2/10 NET30" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Term</label>
                    <input type="text" name="name" required placeholder="Net 30 Days" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Net Days</label>
                        <input type="number" name="net_days" required value="30" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Diskon Days</label>
                        <input type="number" name="discount_days" value="0" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Diskon %</label>
                        <input type="number" step="0.01" name="discount_percentage" value="0.00" min="0" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Keterangan</label>
                    <textarea name="description" rows="2" placeholder="Ketentuan pembayaran..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Payment Term
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Payment Terms</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Term</th>
                        <th class="px-4 py-3">Net Days</th>
                        <th class="px-4 py-3">Diskon Pelunasan</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($terms as $term)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ $term->code }}</td>
                            <td class="px-4 py-3">{{ $term->name }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $term->net_days }} hari</td>
                            <td class="px-4 py-3 text-xs">
                                @if($term->discount_percentage > 0)
                                    <span class="text-emerald-300 font-semibold">{{ $term->discount_percentage }}%</span> dalam {{ $term->discount_days }} hari
                                @else
                                    <span class="text-slate-500">Tidak Ada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $term->description ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('finance.payment-terms.destroy', $term->id) }}" method="POST" onsubmit="return confirm('Hapus term ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Payment Terms.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
