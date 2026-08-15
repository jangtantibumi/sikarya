@extends('crm.layouts.app')
@section('title', 'Reservations - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Reservations</h1>
        <p>Manajemen pemesanan meja restoran</p>
    </div>
    <div>
        <a href="{{ route('crm.reservations.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Buat Reservasi</a>
    </div>
</div>

<div class="table-wrapper">
    <div style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 12px; background: rgba(255,255,255,0.3);">
        <form action="{{ route('crm.reservations.index') }}" method="GET" style="display: flex; gap: 12px; width: 100%;">
            <input type="date" name="date" class="form-control" style="max-width: 200px;" value="{{ request('date') }}">
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="">Semua Status</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="ph ph-faders"></i> Filter</button>
        </form>
    </div>
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Tgl / Waktu</th>
                    <th>Customer</th>
                    <th>Pax / Meja</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $res->reservation_date->format('d/m/Y') }}</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-clock"></i> {{ date('H:i', strtotime($res->reservation_time)) }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--crm-primary);">{{ optional($res->customer)->name ?? 'Unknown' }}</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-phone"></i> {{ optional($res->customer)->phone }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $res->pax }} Orang</div>
                        @if($res->table_preference)
                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-chair"></i> Meja: {{ $res->table_preference }}</div>
                        @endif
                    </td>
                    <td>
                        @if($res->status == 'Pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($res->status == 'Confirmed')
                            <span class="badge badge-info">Confirmed</span>
                        @elseif($res->status == 'Completed')
                            <span class="badge badge-success">Completed</span>
                        @else
                            <span class="badge badge-secondary" style="background: #e2e8f0; color: #475569;">Cancelled</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('crm.reservations.edit', $res->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data reservasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($reservations->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5);">
        <div style="color: #64748b; font-size: 13px; font-weight: 500;">
            Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} entries
        </div>
        <div style="display: flex; gap: 6px;">
            @if($reservations->onFirstPage())
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</span>
            @else
                <a href="{{ $reservations->previousPageUrl() }}" class="btn btn-outline" style="padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</a>
            @endif

            @if($reservations->hasMorePages())
                <a href="{{ $reservations->nextPageUrl() }}" class="btn btn-outline" style="padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></a>
            @else
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
