@extends('inventory.layout')
@section('title', 'Pengaturan Gudang & Stok')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Pengaturan Modul Gudang & Stok</h1>
    <form method="POST" action="{{ route('inventory.settings.update') }}" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        @csrf
        @foreach($settings as $st)
            <div>
                <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1 uppercase font-mono">{{ $st->setting_key }}</label>
                <p class="text-[11px] text-gray-600 dark:text-gray-400 mb-2">{{ $st->description }}</p>
                <input type="text" name="{{ $st->setting_key }}" value="{{ $st->setting_value }}" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
        @endforeach
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-erp-border">
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs">Simpan Pengaturan</button>
        </div>
    </form>
</div>
@endsection
