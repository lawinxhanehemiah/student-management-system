@extends('layouts.financecontroller')

@section('title', 'Fee Structures')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Fee Structures</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="#" class="breadcrumb-item">Revenue Management</a>
                <span class="breadcrumb-item active">Fee Structures</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportFeeStructures()">
                <i class="feather-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border mb-3">
        <div class="card-header bg-transparent border-bottom py-2">
            <div class="card-title fw-semibold mb-0">Filter Fee Structures</div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('finance.fee-structures.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Programme</label>
                        <select class="form-select form-select-sm" name="programme_id">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                {{ $programme->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select form-select-sm" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Level</label>
                        <select class="form-select form-select-sm" name="level">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                Year {{ $level }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="feather-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards (Clean, no background colors inside) -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 text-muted">Total Structures</span>
                            <h3 class="fw-semibold mb-2">{{ $feeStructures->total() }}</h3>
                            <small class="text-muted">Across all programmes</small>
                        </div>
                        <div class="avatar avatar-lg bg-light">
                            <i class="feather-layers fs-3 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 text-muted">Active Structures</span>
                            <h3 class="fw-semibold mb-2 text-success">{{ $activeCount ?? 0 }}</h3>
                            <small class="text-muted">Currently in use</small>
                        </div>
                        <div class="avatar avatar-lg bg-light">
                            <i class="feather-check-circle fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 text-muted">Programmes</span>
                            <h3 class="fw-semibold mb-2">{{ $programmes->count() }}</h3>
                            <small class="text-muted">With fee structures</small>
                        </div>
                        <div class="avatar avatar-lg bg-light">
                            <i class="feather-book fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 text-muted">Academic Years</span>
                            <h3 class="fw-semibold mb-2">{{ $academicYears->count() }}</h3>
                            <small class="text-muted">With fee data</small>
                        </div>
                        <div class="avatar avatar-lg bg-light">
                            <i class="feather-calendar fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Structures Table -->
    <div class="card border">
        <div class="card-header bg-transparent border-bottom py-2">
            <div class="card-title fw-semibold mb-0">Fee Structures List</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="min-width: 800px;">
                    <thead style="background: transparent;">
                        <tr>
                            <th class="border-bottom-2">Programme</th>
                            <th class="border-bottom-2">Academic Year</th>
                            <th class="border-bottom-2">Level</th>
                            <th class="text-end border-bottom-2">Registration</th>
                            <th class="text-end border-bottom-2">Semester 1</th>
                            <th class="text-end border-bottom-2">Semester 2</th>
                            <th class="text-end border-bottom-2">Total</th>
                            <th class="text-center border-bottom-2">Status</th>
                            <th class="text-center border-bottom-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeStructures as $fee)
                        <tr>
                            <td>
                                <div>
                                    <span class="fw-semibold">{{ $fee->programme->name ?? 'N/A' }}</span>
                                    <small class="d-block text-muted">{{ $fee->programme->code ?? '' }}</small>
                                </div>
                            </td>
                            <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                            <td>Year {{ $fee->level }}</td>
                            <td class="text-end">TZS {{ number_format($fee->registration_fee, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($fee->semester_1_fee, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($fee->semester_2_fee, 0) }}</td>
                            <td class="text-end fw-semibold">TZS {{ number_format($fee->registration_fee + $fee->semester_1_fee + $fee->semester_2_fee, 0) }}</td>
                            <td class="text-center">
                                @if($fee->is_active)
                                    <span class="badge bg-success bg-opacity-25 text-dark">Active</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-25 text-dark">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('finance.fee-structures.show', $fee->id) }}" 
                                   class="btn btn-sm btn-icon btn-outline-secondary" 
                                   data-bs-toggle="tooltip" 
                                   title="View Details">
                                    <i class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 120px;">
                                <h5 class="mt-3">No Fee Structures Found</h5>
                                <p class="text-muted">There are no fee structures to display.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-3 px-3 pb-3">
                {{ $feeStructures->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportFeeStructures() {
    window.location.href = '{{ route("finance.fee-structures.export") }}';
}

document.addEventListener('DOMContentLoaded', function() {
    var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
@endpush

<style>
/* Clean table styles */
.table {
    margin-bottom: 0;
}
.table th, .table td {
    border-top: none;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
    padding: 0.6rem 0.4rem;
}
.table thead th {
    background: transparent !important;
    color: #212529;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 2px solid #dee2e6 !important;
}
.table tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}
/* Badges subtle */
.badge {
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    font-size: 0.65rem;
}
.badge.bg-success, .badge.bg-danger {
    background-color: rgba(0,0,0,0.05) !important;
    color: #212529 !important;
}
/* Cards clean */
.card {
    border-radius: 8px;
    box-shadow: none;
    border: 1px solid #e9ecef;
}
.card-header {
    background: transparent !important;
    border-bottom: 1px solid #e9ecef;
    padding: 0.5rem 1rem;
}
.card-body {
    padding: 1rem;
}
/* Avatar icons */
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background-color: #f8f9fa;
}
.avatar i {
    font-size: 1.5rem;
}
/* Buttons */
.btn-outline-secondary {
    border-color: #dee2e6;
    color: #495057;
}
.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
/* Pagination */
.pagination {
    margin-bottom: 0;
}
/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
}
</style>
@endsection