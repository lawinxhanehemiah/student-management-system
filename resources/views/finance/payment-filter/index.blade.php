@extends('layouts.financecontroller')

@section('title', 'Tuition Payment Filter')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Tuition Payment Filter</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <span>Tuition Filter</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-sm btn-success-light" onclick="exportFiltered()">
                <i class="feather-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Students by Tuition Payment Amount</h6>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ route('finance.payment-filter.index') }}" id="filterForm">
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
                            @foreach($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>Semester {{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Programme -->
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Programme</label>
                        <select class="form-select form-select-sm" name="programme_id">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $prog)
                            <option value="{{ $prog->id }}" {{ request('programme_id') == $prog->id ? 'selected' : '' }}>
                                {{ $prog->short_name ?? $prog->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Minimum Amount -->
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Min Amount (TZS)</label>
                        <input type="number" class="form-control form-control-sm" 
                               name="min_amount" value="{{ request('min_amount', 0) }}" 
                               step="1000" placeholder="e.g., 350000">
                    </div>

                    <!-- Actions -->
                    <div class="col-md-1 d-flex align-items-end">
                        <div class="d-flex gap-1 w-100">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="feather-filter"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2 text-end">
                        <a href="{{ route('finance.payment-filter.index') }}" class="btn btn-sm btn-light">
                            <i class="feather-x"></i> Clear All Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <span class="badge bg-light text-dark p-2 me-2">
                <strong>Students Found:</strong> {{ number_format($totalStudents) }}
            </span>
            <span class="badge bg-light text-dark p-2 me-2">
                <strong>Total Tuition:</strong> TZS {{ number_format($totalAmount, 0) }}
            </span>
            <span class="badge bg-light text-dark p-2">
                <strong>Average:</strong> TZS {{ number_format($averageAmount, 0) }}
            </span>
        </div>
        <div class="text-muted small">
            Minimum: TZS {{ number_format($minAmount, 0) }}
        </div>
    </div>

    <!-- Results Table -->
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
                            <th>Level</th>
                            <th>Semester</th>
                            @if($academicYearId)
                            <th>Academic Year</th>
                            @endif
                            <th class="text-end">Total Tuition Paid (TZS)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedData as $index => $student)
                        <tr>
                            <td>{{ $loop->iteration + (($paginatedData->currentPage() - 1) * $paginatedData->perPage()) }}</td>
                            <td>
                                <a href="{{ route('finance.student-payment-info.show', $student['id']) }}" class="text-dark">
                                    {{ $student['name'] }}
                                </a>
                            </td>
                            <td>{{ $student['reg_no'] }}</td>
                            <td><small>{{ $student['programme'] }}</small></td>
                            <td>Year {{ $student['level'] }}</td>
                            <td>Sem {{ $student['semester'] }}</td>
                            @if($academicYearId)
                            <td>{{ $student['academic_year'] ?? 'N/A' }}</td>
                            @endif
                            <td class="text-end fw-semibold text-primary">TZS {{ number_format($student['total_paid'], 0) }}</td>
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
                            <td colspan="{{ $academicYearId ? 9 : 8 }}" class="text-center py-4">
                                <p class="text-muted small mb-0">
                                    No students found with tuition payments ≥ TZS {{ number_format($minAmount, 0) }}
                                </p>
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
</div>

<style>
.table td, .table th {
    padding: 0.3rem 0.25rem;
}

.btn-icon {
    padding: 0.15rem 0.3rem;
}

.badge {
    font-weight: 400;
}
</style>
@endsection

@push('scripts')
<script>
function exportFiltered() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();
    window.location.href = '{{ route("finance.payment-filter.export") }}?' + params;
}
</script>
@endpush