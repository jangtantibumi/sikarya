@extends('crm.layouts.app')
@section('title', 'Membership Tiers - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Membership Tiers</h1>
        <p>Kelola tingkatan membership customer beserta benefitnya.</p>
    </div>
    <div>
        <a href="{{ route('crm.memberships.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Tier</a>
    </div>
</div>

<div class="table-wrapper">
    <table class="crm-table">
        <thead>
            <tr>
                <th>Nama Tier</th>
                <th>Min Poin</th>
                <th>Diskon</th>
                <th>Benefit</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($memberships as $tier)
            <tr>
                <td>
                    <span class="badge badge-success">{{ $tier->name }}</span>
                </td>
                <td style="color: var(--crm-primary); font-weight: 700;">{{ number_format($tier->min_points) }} pts</td>
                <td style="color: var(--text-accent); font-weight: 700;">{{ number_format($tier->discount_percentage, 2) }}%</td>
                <td style="color: #64748b; font-size: 13px;">{{ $tier->benefits }}</td>
                <td style="text-align: right;">
                    <a href="{{ route('crm.memberships.edit', $tier->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data tier membership.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
