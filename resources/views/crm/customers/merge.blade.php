@extends('crm.layouts.app')
@section('title', 'Merge Customer Duplikat - CRM Portal')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Merge Customer Duplikat</h1>
        <p>Gabungkan data dari dua entitas customer yang terduplikasi ke dalam satu profil utama.</p>
    </div>
    <a href="{{ route('crm.customers.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div style="max-width: 800px; margin: 0 auto; background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 32px; box-shadow: var(--crm-shadow);">
    <form action="{{ route('crm.customers.merge') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menggabungkan customer ini? Tindakan ini akan memindahkan poin, spending, serta riwayat dari Customer Sumber ke Customer Target.');">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div style="background: rgba(239, 68, 68, 0.05); border: 1px dashed #ef4444; border-radius: 16px; padding: 20px;">
                <h3 style="font-size: 14px; font-weight: 700; color: #dc2626; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-trash"></i> Customer Sumber (Akan Dihapus)
                </h3>
                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Pilih Customer Sumber</label>
                <select name="source_id" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->name }} ({{ $c->customer_code }}) - {{ $c->phone }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 11px; color: #78716c; margin-top: 8px;">* Seluruh poin, spending, reservasi, & timeline akan ditransfer ke target, lalu customer ini di-soft delete.</p>
            </div>

            <div style="background: rgba(22, 163, 74, 0.05); border: 1px dashed #0C3527; border-radius: 16px; padding: 20px;">
                <h3 style="font-size: 14px; font-weight: 700; color: var(--text-accent); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-check-circle"></i> Customer Target (Profil Utama)
                </h3>
                <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Pilih Customer Target</label>
                <select name="target_id" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->name }} ({{ $c->customer_code }}) - {{ $c->phone }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 11px; color: #78716c; margin-top: 8px;">* Profil utama yang tetap aktif dan menyimpan akumulasi seluruh data.</p>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 14px;">
                <i class="ph ph-git-merge"></i> Proses Penggabungan Data
            </button>
        </div>
    </form>
</div>
@endsection
