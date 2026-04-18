@extends('layouts.financecontroller')

@section('title', 'All Payments')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header - Compact -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h1 class="page-title fs-5 fw-semibold mb-0">All Payments</h1>
            <div class="breadcrumb small">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a>
                <span class="mx-1">/</span>
                <span class="text-dark">All Payments</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-sm btn-success-light" onclick="exportPayments()">
                <i class="feather-download"></i> Export
            </button>
            <button class="btn btn-sm btn-info-light" data-bs-toggle="modal" data-bs-target="#statisticsModal">
                <i class="feather-bar-chart-2"></i> Stats
            </button>
        </div>
    </div>

    <!-- Filters Card - Compact -->
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Payments</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.all-payments.index') }}">
                <div class="row g-2">
                    <!-- 🔴 NEW: Academic Year Filter -->
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="level">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                Y{{ $level }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="semester">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                            <option value="{{ $semester }}" {{ request('semester') == $semester ? 'selected' : '' }}>
                                S{{ $semester }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="programme_id">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                {{ $programme->short_name ?? substr($programme->name, 0, 15) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="payment_method">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $method)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex gap-1">
                            <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}" placeholder="From">
                            <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}" placeholder="To">
                        </div>
                    </div>
                    
                    <div class="col-12 text-end">
                        <a href="{{ route('finance.all-payments.index') }}" class="btn btn-sm btn-light">Clear</a>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table - Compact, Single Color -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Payments List</h6>
            <span class="badge bg-secondary">{{ $payments->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Academic Year</th>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Programme</th>
                            <th>Lvl</th>
                            <th>Sem</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>
                                <a href="{{ route('finance.payments.show', $payment->id) }}" class="text-primary">
                                    {{ substr($payment->payment_number, -8) }}
                                </a>
                            </td>
                            <td>{{ $payment->created_at->format('d/m/y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $payment->academicYear->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($payment->student_id)
                                <a href="{{ route('finance.student-payment-info.show', $payment->student_id) }}" 
                                   class="text-dark text-decoration-none fw-semibold">
                                    {{ $payment->student?->user?->first_name ?? '' }}
                                </a>
                                @else
                                <span>N/A</span>
                                @endif
                            </td>
                            <td>{{ $payment->student?->registration_number ?? 'N/A' }}</td>
                            <td>{{ substr($payment->student?->programme?->name ?? 'N/A', 0, 10) }}</td>
                            <td>{{ $payment->student?->current_level ?? 'N/A' }}</td>
                            <td>{{ $payment->student?->current_semester ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ substr($payment->payment_method, 0, 3) }}</span>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($payment->amount, 0) }}</td>
                            <td>
                                <span class="badge bg-light text-success">✓</span>
                            </td>
                            <td>
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.payments.show', $payment->id) }}" 
                                       class="btn btn-sm btn-icon btn-light"
                                       data-bs-toggle="tooltip" title="View">
                                        <i class="feather-eye" style="width: 14px;"></i>
                                    </a>
                                    <a href="{{ route('finance.payments.receipt', $payment->id) }}" 
                                       class="btn btn-sm btn-icon btn-light"
                                       data-bs-toggle="tooltip" title="Receipt">
                                        <i class="feather-file-text" style="width: 14px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-4">
                                <p class="text-muted mb-0 small">No payments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <!-- Footer - Compact -->
    <div class="text-center text-muted small mt-3">
        <span>© {{ date('Y') }} St. Maximilian Kolbe College</span>
    </div>
</div>

<!-- Statistics Modal - Simplified -->
<div class="modal fade" id="statisticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Payment Statistics</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="statisticsLoader" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <p class="mt-2 small">Loading...</p>
                </div>
                <div id="statisticsContent" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Simple, clean styles */
.table td, .table th {
    padding: 0.4rem 0.3rem;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-sm {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
}

.badge {
    font-weight: 400;
    padding: 0.2rem 0.4rem;
}

.card {
    border-radius: 6px;
}

.form-select-sm, .form-control-sm {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
}

/* Link styles */
a.text-dark:hover {
    text-decoration: underline !important;
    color: #0d6efd !important;
}

/* Pagination */
.pagination {
    margin-bottom: 0;
    gap: 2px;
}

.page-link {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection

@push('scripts')
<script>
function exportPayments() {
    const params = new URLSearchParams(window.location.search).toString();
    window.location.href = '{{ route("finance.all-payments.export") }}?' + params;
}

// Statistics modal
document.getElementById('statisticsModal').addEventListener('show.bs.modal', function() {
    document.getElementById('statisticsLoader').style.display = 'block';
    document.getElementById('statisticsContent').style.display = 'none';
    
    fetch('{{ route("finance.all-payments.statistics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySimpleStatistics(data);
            }
        })
        .finally(() => {
            document.getElementById('statisticsLoader').style.display = 'none';
            document.getElementById('statisticsContent').style.display = 'block';
        });
});

function displaySimpleStatistics(data) {
    let html = `
        <div class="row g-2">
            <div class="col-12">
                <div class="bg-light p-2 rounded text-center mb-2">
                    <small class="text-muted">Total Payments</small>
                    <h5 class="mb-0">${data.total_payments.toLocaleString()} | TZS ${data.total_amount.toLocaleString()}</h5>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2">
                    <small class="text-muted">By Level</small>
                    <table class="table table-sm small mb-0">
    `;
    
    for (let level = 1; level <= 4; level++) {
        html += `<tr><td>Year ${level}</td><td class="text-end">${data.by_level[level].count}</td><td class="text-end">${(data.by_level[level].amount/1000000).toFixed(1)}M</td></tr>`;
    }
    
    html += `
                    </table>
                </div>
            </div>
            <div class="col-6">
                <div class="border rounded p-2">
                    <small class="text-muted">By Semester</small>
                    <table class="table table-sm small mb-0">
    `;
    
    for (let sem = 1; sem <= 2; sem++) {
        html += `<tr><td>Semester ${sem}</td><td class="text-end">${data.by_semester[sem].count}</td><td class="text-end">${(data.by_semester[sem].amount/1000000).toFixed(1)}M</td></tr>`;
    }
    
    html += `
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('statisticsContent').innerHTML = html;
}

// Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush