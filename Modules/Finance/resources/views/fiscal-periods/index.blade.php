@extends('finance::layouts.master')

@section('title', 'Finance — Fiscal Periods')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-white mb-0">
                <i class="bi bi-calendar3-range me-2 text-finance-accent"></i>
                Fiscal Periods
                @if(isset($fiscalYear))
                    <small class="text-muted fs-6 ms-2">— {{ $fiscalYear->name }}</small>
                @endif
            </h1>
            <p class="text-muted small mb-0 mt-1">Manage and control accounting periods (open / close / lock)</p>
        </div>
        <a href="{{ route('finance.fiscal-years.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Fiscal Years
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Periods Table --}}
    <div class="card finance-card shadow-lg border-0">
        <div class="card-header finance-card-header d-flex align-items-center justify-content-between py-3">
            <span class="fw-semibold text-white">
                <i class="bi bi-table me-2"></i>Period List
            </span>
            <span class="badge bg-info fs-6">{{ $periods->count() }} Periods</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0 finance-table">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Period</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                            <tr>
                                <td class="ps-4 text-muted">{{ $period->period_number }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        P{{ str_pad($period->period_number, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="fw-medium text-white">{{ $period->name }}</td>
                                <td class="text-muted small">{{ $period->start_date->format('d M Y') }}</td>
                                <td class="text-muted small">{{ $period->end_date->format('d M Y') }}</td>
                                <td>
                                    @if($period->status === 'open')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                                            <i class="bi bi-unlock me-1"></i>Open
                                        </span>
                                    @elseif($period->status === 'closed')
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning">
                                            <i class="bi bi-lock me-1"></i>Closed
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">
                                            <i class="bi bi-shield-lock me-1"></i>Locked
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($period->status === 'open')
                                        <form method="POST" action="{{ route('finance.fiscal-periods.update', $period->id) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="closed">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                onclick="return confirm('Close period {{ $period->name }}?')">
                                                <i class="bi bi-lock me-1"></i>Close
                                            </button>
                                        </form>
                                    @elseif($period->status === 'closed')
                                        <form method="POST" action="{{ route('finance.fiscal-periods.update', $period->id) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Re-open period {{ $period->name }}?')">
                                                <i class="bi bi-unlock me-1"></i>Re-open
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('finance.fiscal-periods.update', $period->id) }}" class="d-inline ms-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="locked">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Lock period {{ $period->name }}? This cannot be undone.')">
                                                <i class="bi bi-shield-lock me-1"></i>Lock
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small"><i class="bi bi-shield-fill-lock"></i> Locked</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    No periods found for this fiscal year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
