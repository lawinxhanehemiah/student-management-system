@extends('layouts.financecontroller')

@section('title', ucfirst($type) . ' - Student Summary')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">{{ ucfirst($type) }} - Student Payment Summary</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.payments-management.dashboard') }}" class="text-muted">Payments Management</a> > 
                <a href="{{ route('finance.payments-management.fee-type', $type) }}" class="text-muted">{{ ucfirst($type) }}</a> > 
                <span>Student Summary</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-sm btn-success-light" onclick="exportSummary()">
                <i class="feather-download"></i> Export
            </button>
            <a href="{{ route('finance.payments-management.fee-type', $type) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">
                <i class="feather-filter me-1"></i> Filter Students by {{ ucfirst($type) }} Payment
            </h6>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ route('finance.payments-management.fee-type.student-summary', $type) }}" id="filterForm">
                <div class="row g-2">
                    <!-- Academic Year -->
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

                    <!-- Level -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Level</label>
                        <select class="form-select form-select-sm" name="level">
                            <option value="">All Levels</option>
                            @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>Year {{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Semester</label>
                        <select class="form-select form-select-sm" name="semester">
                            <option value="">All Semesters</option>
                            <option value="1" {{ request('semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                            <option value="2" {{ request('semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                        </select>
                    </div>

                    <!-- Programme -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Programme</label>
                        <select class="form-select form-select-sm" name="programme_id">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $prog)
                            <option value="{{ $prog->id }}" {{ request('programme_id') == $prog->id ? 'selected' : '' }}>
                                {{ $prog->short_name ?? substr($prog->name, 0, 25) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Name or Registration No">
                    </div>

                    <!-- Min Amount -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Min Amount (TZS)</label>
                        <input type="number" class="form-control form-control-sm" name="min_amount" value="{{ request('min_amount', 0) }}" step="1000" placeholder="0">
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="feather-filter"></i> Apply
                        </button>
                    </div>
                    <div class="col-md-12 mt-2 text-end">
                        <a href="{{ route('finance.payments-management.fee-type.student-summary', $type) }}" class="btn btn-sm btn-light">
                            <i class="feather-x"></i> Clear All Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Students</span>
                            <h4 class="mb-0 fw-semibold">{{ number_format($totalStudents) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light rounded">
                            <i class="feather-users text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total {{ ucfirst($type) }}</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($totalAmount, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light rounded">
                            <i class="feather-dollar-sign text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Fully Paid</span>
                            <h4 class="mb-0 fw-semibold text-success">{{ number_format($fullyPaidCount) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light rounded">
                            <i class="feather-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Partial Payment</span>
                            <h4 class="mb-0 fw-semibold text-warning">{{ number_format($partialCount) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light rounded">
                            <i class="feather-alert-circle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Summary -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-light text-dark p-2">
                <strong>Min Amount:</strong> TZS {{ number_format($minAmount, 0) }}
            </span>
            @if(request('academic_year_id'))
                @php $selectedYear = $academicYears->firstWhere('id', request('academic_year_id')); @endphp
                <span class="badge bg-light text-dark p-2">
                    <strong>Year:</strong> {{ $selectedYear->name ?? 'N/A' }}
                </span>
            @endif
            @if(request('level'))
                <span class="badge bg-light text-dark p-2">
                    <strong>Level:</strong> Year {{ request('level') }}
                </span>
            @endif
            @if(request('semester'))
                <span class="badge bg-light text-dark p-2">
                    <strong>Semester:</strong> {{ request('semester') }}
                </span>
            @endif
        </div>
        <div class="text-muted small">
            Showing {{ $paginatedData->firstItem() ?? 0 }} - {{ $paginatedData->lastItem() ?? 0 }} of {{ $paginatedData->total() }}
        </div>
    </div>

    <!-- Students Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Reg No</th>
                            <th>Programme</th>
                            <th>Lvl</th>
                            <th>Sem</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end"># Pymts</th>
                            @if(in_array($type, ['hostel', 'supplementary', 'repeat', 'tuition']))
                            <th class="text-end">Required</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-center">%</th>
                            @endif
                            <th>Last Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedData as $index => $student)
                        <tr class="{{ isset($student['status']) && $student['status'] == 'FULLY PAID' ? 'table-success' : '' }}">
                            <td>{{ $loop->iteration + (($paginatedData->currentPage() - 1) * $paginatedData->perPage()) }}</td>
                            <td>
                                <a href="{{ route('finance.student-payment-info.show', $student['id']) }}" class="text-dark fw-semibold">
                                    {{ $student['name'] }}
                                </a>
                            </td>
                            <td>{{ $student['reg_no'] }}</td>
                            <td><small>{{ $student['programme'] }}</small></td>
                            <td>Y{{ $student['level'] }}</td>
                            <td>S{{ $student['semester'] }}</td>
                            <td class="text-end fw-semibold">TZS {{ number_format($student['total_paid'], 0) }}</td>
                            <td class="text-end">{{ $student['payment_count'] }}</td>
                            
                            @if(in_array($type, ['hostel', 'supplementary', 'repeat', 'tuition']))
                            <td class="text-end">TZS {{ number_format($student['required_fee'], 0) }}</td>
                            <td class="text-end {{ isset($student['balance']) && $student['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($student['balance'] ?? 0, 0) }}
                            </td>
                            <td>
                                @if(isset($student['status']))
                                    @if($student['status'] == 'FULLY PAID')
                                        <span class="badge bg-success">✓ Paid</span>
                                    @else
                                        <span class="badge bg-warning">Partial</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($student['percentage']))
                                    <div class="d-flex align-items-center gap-1">
                                        <span>{{ $student['percentage'] }}%</span>
                                        <div class="progress progress-sm" style="width: 50px; height: 4px;">
                                            <div class="progress-bar {{ $student['percentage'] >= 100 ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ min($student['percentage'], 100) }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            @endif
                            
                            <td><small>{{ $student['last_payment'] }}</small></td>
                            <td>
                                <a href="{{ route('finance.student-payment-info.show', $student['id']) }}" 
                                   class="btn btn-sm btn-icon btn-light"
                                   data-bs-toggle="tooltip" title="View Details">
                                    <i class="feather-eye" style="width: 12px;"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2 mb-0">No students found with total {{ $type }} payments ≥ TZS {{ number_format($minAmount, 0) }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $paginatedData->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center text-muted small mt-3">
        <span>© {{ date('Y') }} St. Maximilian Kolbe College</span>
    </div>
</div>

<style>
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
    background-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-success {
    background-color: rgba(40, 167, 69, 0.05);
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.badge {
    font-weight: 400;
    padding: 0.2rem 0.4rem;
}

.btn-sm {
    padding: 0.15rem 0.4rem;
    font-size: 0.7rem;
}

.btn-icon {
    padding: 0.15rem 0.3rem;
}

.avatar-sm {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-sm i {
    font-size: 16px;
}

.form-label {
    font-size: 0.7rem;
    margin-bottom: 0.1rem;
    color: #6c757d;
    font-weight: 500;
}

.form-select-sm, .form-control-sm {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    height: auto;
}

.card {
    border-radius: 8px;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}
</style>
@endsection

@push('scripts')
<script>
function exportSummary() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();
    window.location.href = '{{ route("finance.payments-management.fee-type.export", $type) }}?' + params + '&summary=true';
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endpush