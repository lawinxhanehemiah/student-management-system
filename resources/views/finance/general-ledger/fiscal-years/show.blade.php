@extends('layouts.financecontroller')

@section('title', 'Fiscal Year Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Fiscal Year Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.fiscal-years.index') }}">Fiscal Years</a></li>
                <li class="breadcrumb-item active">{{ $fiscalYear->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($fiscalYear->status == 'open')
            <a href="{{ route('finance.general-ledger.fiscal-years.edit', $fiscalYear->id) }}" 
               class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            
            @if(!$fiscalYear->is_active)
                <button type="button" class="btn btn-success" onclick="setActive()">
                    <i class="fas fa-check-circle me-2"></i>Set as Active
                </button>
            @endif
            
            <button type="button" class="btn btn-danger" onclick="closeYear()">
                <i class="fas fa-lock me-2"></i>Close Year
            </button>
        @else
            <button type="button" class="btn btn-secondary" onclick="reopenYear()">
                <i class="fas fa-unlock me-2"></i>Reopen Year
            </button>
        @endif
        
        <a href="{{ route('finance.general-ledger.fiscal-years.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Action Forms -->
<form id="set-active-form" method="POST" action="{{ route('finance.general-ledger.fiscal-years.set-active', $fiscalYear->id) }}" style="display: none;">
    @csrf
</form>
<form id="close-form" method="POST" action="{{ route('finance.general-ledger.fiscal-years.close', $fiscalYear->id) }}" style="display: none;">
    @csrf
</form>
<form id="reopen-form" method="POST" action="{{ route('finance.general-ledger.fiscal-years.reopen', $fiscalYear->id) }}" style="display: none;">
    @csrf
</form>

<div class="row">
    <!-- Fiscal Year Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Fiscal Year Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Name:</span>
                        <span class="value fw-bold">{{ $fiscalYear->name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Period:</span>
                        <span class="value">
                            {{ $fiscalYear->start_date->format('d M Y') }} - 
                            {{ $fiscalYear->end_date->format('d M Y') }}
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Duration:</span>
                        <span class="value">
                            {{ $fiscalYear->start_date->diffInDays($fiscalYear->end_date) }} days
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">{!! $fiscalYear->status_badge !!}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Active:</span>
                        <span class="value">{!! $fiscalYear->active_badge !!}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $fiscalYear->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $fiscalYear->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                @if($fiscalYear->notes)
                    <div class="mt-3">
                        <h6>Notes:</h6>
                        <p class="text-muted">{{ $fiscalYear->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h6>Total Journals</h6>
                        <h2>{{ $stats['total_journals'] }}</h2>
                        <small>Posted: {{ $stats['posted_journals'] }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h6>Total Debit</h6>
                        <h2>{{ number_format($stats['total_debit'], 0) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <h6>Total Credit</h6>
                        <h2>{{ number_format($stats['total_credit'], 0) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning text-white mb-3">
                    <div class="card-body">
                        <h6>Net Income/Loss</h6>
                        <h2>{{ number_format($stats['total_debit'] - $stats['total_credit'], 0) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Summary -->
        <div class="card mt-3">
            <div class="card-header">
                <h5>Monthly Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Journals</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlySummary as $month)
                                <tr>
                                    <td>{{ DateTime::createFromFormat('!m', $month->month)->format('F') }} {{ $month->year }}</td>
                                    <td>{{ $month->count }}</td>
                                    <td class="text-end">{{ number_format($month->total_debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($month->total_credit, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setActive() {
    if(confirm('Set this fiscal year as active?')) {
        document.getElementById('set-active-form').submit();
    }
}

function closeYear() {
    if(confirm('Are you sure you want to close this fiscal year? This will create closing entries and cannot be undone easily.')) {
        document.getElementById('close-form').submit();
    }
}

function reopenYear() {
    if(confirm('Are you sure you want to reopen this fiscal year? This should only be done if no transactions exist in later periods.')) {
        document.getElementById('reopen-form').submit();
    }
}
</script>
@endpush