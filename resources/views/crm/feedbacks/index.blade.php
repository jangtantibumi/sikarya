@extends('crm.layouts.app')
@section('title', 'Feedback & Keluhan - CRM')

@section('content')
<div class="page-header">
    <div class="page-title-group">
        <h1>Feedback & Keluhan</h1>
        <p>Manajemen kepuasan pelanggan dan ulasan.</p>
    </div>
</div>

<div class="table-wrapper">
    <div style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 12px; background: rgba(255,255,255,0.3);">
        <form action="{{ route('crm.feedbacks.index') }}" method="GET" style="display: flex; gap: 12px; width: 100%;">
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="">Semua Status</option>
                <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <select name="rating" class="form-control" style="max-width: 200px;">
                <option value="">Semua Rating (1-5)</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ 4</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ 3</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐ 2</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ 1</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="ph ph-faders"></i> Filter</button>
        </form>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Kategori</th>
                    <th>Rating</th>
                    <th>Pesan</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $fb)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $fb->created_at->format('d/m/Y') }}</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ $fb->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--crm-primary);">{{ optional($fb->customer)->name ?? 'Unknown' }}</div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ optional($fb->customer)->customer_code }}</div>
                    </td>
                    <td>
                        <span style="background: rgba(12, 53, 39, 0.05); color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">{{ $fb->category }}</span>
                    </td>
                    <td style="color: #f59e0b; font-size: 13px;">
                        @for($i=1; $i<=5; $i++)
                            {{ $i <= $fb->rating ? '⭐' : '☆' }}
                        @endfor
                    </td>
                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #475569; font-size: 13px;" title="{{ $fb->message }}">
                        {{ $fb->message }}
                    </td>
                    <td>
                        @if($fb->status == 'Open')
                            <span class="badge badge-danger">Open</span>
                        @elseif($fb->status == 'In Progress')
                            <span class="badge badge-warning">In Progress</span>
                        @else
                            <span class="badge badge-success">Resolved</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('crm.feedbacks.show', $fb->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada feedback atau komplain.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($feedbacks->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5);">
        <div style="color: #64748b; font-size: 13px; font-weight: 500;">
            Showing {{ $feedbacks->firstItem() ?? 0 }} to {{ $feedbacks->lastItem() ?? 0 }} of {{ $feedbacks->total() }} entries
        </div>
        <div style="display: flex; gap: 6px;">
            @if($feedbacks->onFirstPage())
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</span>
            @else
                <a href="{{ $feedbacks->previousPageUrl() }}" class="btn btn-outline" style="padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</a>
            @endif

            @if($feedbacks->hasMorePages())
                <a href="{{ $feedbacks->nextPageUrl() }}" class="btn btn-outline" style="padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></a>
            @else
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
