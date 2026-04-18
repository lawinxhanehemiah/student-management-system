@extends('layouts.financecontroller')

@section('title', ucfirst($type) . ' Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ ucfirst($type) }} Payments</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.payments-management.dashboard') }}" class="breadcrumb-item">Payments Management</a>
                <span class="breadcrumb-item active">{{ ucfirst($type) }}</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-success-light btn-wave" onclick="exportFiltered()">
                <i class="feather-download"></i> Export Filtered
            </button>
            <button class="btn btn-primary-light btn-wave" onclick="printPage()">
                <i class="feather-printer"></i> Print
            </button>
            <a href="{{ route('finance.payments-management.dashboard') }}" class="btn btn-light btn-wave">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Advanced Filters Card -->
    <div class="card custom-card mb-3">
        <div class="card-header bg-light py-2">
            <h6 class="mb-0 fw-semibold">Filter Payments</h6>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ route('finance.payments-management.fee-type', $type) }}" id="filterForm">
                <div class="row g-2">
                    <!-- 🔴 NEW: Academic Year Filter -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Academic Year</label>
                        <select class="form-select form-select-sm" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Search by text -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="feather-search"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, Reg No, Ref...">
                        </div>
                    </div>
                    
                    <!-- Level Filter -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Level</label>
                        <select class="form-select form-select-sm" name="level">
                            <option value="">All Levels</option>
                            @for($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- Semester Filter -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Semester</label>
                        <select class="form-select form-select-sm" name="semester">
                            <option value="">All Semesters</option>
                            <option value="1" {{ request('semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                            <option value="2" {{ request('semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                        </select>
                    </div>
                    
                    <!-- Programme Filter -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Programme</label>
                        <select class="form-select form-select-sm" name="programme_id">
                            <option value="">All Programmes</option>
                            @foreach($programmes ?? [] as $programme)
                            <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                {{ $programme->short_name ?? substr($programme->name, 0, 20) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Payment Method Filter -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Method</label>
                        <select class="form-select form-select-sm" name="payment_method">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods ?? [] as $method)
                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $method)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">From Date</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small mb-1">To Date</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    
                    <!-- Min/Max Amount -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Min Amount</label>
                        <input type="number" class="form-control form-control-sm" name="min_amount" value="{{ request('min_amount') }}" step="1000" placeholder="TZS">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Max Amount</label>
                        <input type="number" class="form-control form-control-sm" name="max_amount" value="{{ request('max_amount') }}" step="1000" placeholder="TZS">
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-1 w-100">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                                <i class="feather-filter"></i> Apply
                            </button>
                            <a href="{{ route('finance.payments-management.fee-type', $type) }}" class="btn btn-sm btn-light flex-fill">
                                <i class="feather-x"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Info (Compact) -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <span class="badge bg-light text-dark p-2 me-2">
                <strong>Total Records:</strong> {{ $payments->total() }}
            </span>
            <span class="badge bg-light text-dark p-2 me-2">
                <strong>Total Amount:</strong> TZS {{ number_format($totalAmount, 0) }}
            </span>
        </div>
        <div class="text-muted small">
            Showing {{ $payments->firstItem() ?? 0 }} - {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Academic Year</th>  <!-- 🔴 NEW COLUMN -->
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Programme</th>
                            <th>Lvl</th>
                            <th>Sem</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Reference</th>
                            <th>Control #</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                        <tr>
                            <td>{{ $payments->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('finance.payments.show', $payment->id) }}" class="fw-semibold small">
                                    {{ substr($payment->payment_number, -8) }}
                                </a>
                            </td>
                            <td>{{ $payment->created_at->format('d/m/y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark small">
                                    {{ $payment->academicYear->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($payment->student_id)
                                <a href="{{ route('finance.student-payment-info.show', $payment->student_id) }}" 
                                   class="text-dark text-decoration-none fw-semibold small">
                                    {{ $payment->student?->user?->first_name ?? '' }}
                                </a>
                                @else
                                <span class="small">N/A</span>
                                @endif
                            </td>
                            <td><small>{{ $payment->student?->registration_number ?? 'N/A' }}</small></td>
                            <td><small>{{ $payment->student?->programme?->short_name ?? substr($payment->student?->programme?->name ?? 'N/A', 0, 10) }}</small></td>
                            <td><small>Y{{ $payment->student?->current_level ?? '?' }}</small></td>
                            <td><small>S{{ $payment->student?->current_semester ?? '?' }}</small></td>
                            <td>
                                <span class="badge bg-light text-dark small">
                                    {{ $payment->payable ? strtoupper(substr(str_replace('_', ' ', $payment->payable->invoice_type), 0, 4)) : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark small">
                                    {{ substr(ucwords($payment->payment_method), 0, 3) }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold small">{{ number_format($payment->amount, 0) }}</td>
                            <td><small>{{ substr($payment->transaction_reference ?? $payment->receipt_number ?? 'N/A', -6) }}</small></td>
                            <td><small>{{ substr($payment->control_number ?? 'N/A', -4) }}</small></td>
                            <td>
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.payments.show', $payment->id) }}" 
                                       class="btn btn-sm btn-icon btn-light"
                                       data-bs-toggle="tooltip" title="View">
                                        <i class="feather-eye" style="width: 12px;"></i>
                                    </a>
                                    <a href="{{ route('finance.payments.receipt', $payment->id) }}" 
                                       class="btn btn-sm btn-icon btn-light"
                                       data-bs-toggle="tooltip" title="Receipt">
                                        <i class="feather-file-text" style="width: 12px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center py-4">
                                <p class="text-muted small mb-0">No payments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $payments->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<style>
/* Compact table styles */
.table td, .table th {
    padding: 0.3rem 0.25rem;
    vertical-align: middle;
}

.table thead th {
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-weight: 400;
    padding: 0.2rem 0.4rem;
}

.btn-sm {
    padding: 0.15rem 0.4rem;
}

.btn-icon {
    padding: 0.15rem 0.3rem;
}

/* Form controls */
.form-label {
    font-size: 0.7rem;
    margin-bottom: 0.1rem;
    color: #6c757d;
}

.form-control-sm, .form-select-sm {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    height: auto;
}

.input-group-sm .form-control,
.input-group-sm .input-group-text {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
}

/* Card */
.card {
    border-radius: 6px;
    border: 1px solid rgba(0,0,0,0.08);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}
</style>
@endsection

@push('scripts')
<script>
function exportFiltered() {
    // Get current form parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();
    
    // Add export parameter
    window.location.href = '{{ route("finance.all-payments.export") }}?' + params + '&type={{ $type }}&export=csv';
}

function printPage() {
    window.print();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush