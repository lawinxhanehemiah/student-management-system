@extends('layouts.financecontroller')

@section('title', "Year $level - Semester $semester Students")

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Year {{ $level }} - Semester {{ $semester }} Students</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.payments-management.dashboard') }}" class="breadcrumb-item">Payments Management</a>
                <span class="breadcrumb-item active">Students List</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.payments-management.export-eligible') }}?level={{ $level }}&semester={{ $semester }}" 
               class="btn btn-success-light btn-wave">
                <i class="feather-download"></i> Export List
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Academic Year</h6>
                            <p class="fw-semibold">{{ $currentAcademicYear->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6>Total Students</h6>
                            <p class="fw-semibold">{{ count($studentsData) }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6>Eligible for Exams</h6>
                            <p class="fw-semibold text-success">{{ collect($studentsData)->where('eligible', true)->count() }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6>Not Eligible</h6>
                            <p class="fw-semibold text-danger">{{ collect($studentsData)->where('eligible', false)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card custom-card">
        <div class="card-header">
            <div class="card-title">Students Payment Status</div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Reg No</th>
                            <th>Programme</th>
                            <th>Required (TZS)</th>
                            <th>Paid (TZS)</th>
                            <th>Balance (TZS)</th>
                            <th>Payment %</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $index => $student)
                        <tr class="{{ $student['eligible'] ? 'table-success' : 'table-danger' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('finance.payments-management.student-statement', $student['id']) }}">
                                    {{ $student['name'] }}
                                </a>
                            </td>
                            <td>{{ $student['reg_no'] }}</td>
                            <td>{{ $student['programme'] }}</td>
                            <td class="fw-semibold">{{ number_format($student['required'], 2) }}</td>
                            <td class="text-success">{{ number_format($student['paid'], 2) }}</td>
                            <td class="{{ $student['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($student['balance'], 2) }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $student['payment_percentage'] }}%</span>
                                    <div class="progress progress-sm flex-fill" style="height: 5px;">
                                        <div class="progress-bar {{ $student['eligible'] ? 'bg-success' : 'bg-warning' }}" 
                                             style="width: {{ $student['payment_percentage'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student['eligible'])
                                    <span class="badge bg-success">Eligible</span>
                                @else
                                    <span class="badge bg-danger">Not Eligible</span>
                                @endif
                            </td>
                            <td>
                                <div class="hstack gap-2">
                                    <a href="{{ route('finance.payments-management.student-statement', $student['id']) }}" 
                                       class="btn btn-sm btn-icon btn-info-light"
                                       data-bs-toggle="tooltip" title="Full Statement">
                                        <i class="feather-file-text"></i>
                                    </a>
                                    <a href="{{ route('finance.payments-management.print-statement', $student['id']) }}" 
                                       class="btn btn-sm btn-icon btn-primary-light" target="_blank"
                                       data-bs-toggle="tooltip" title="Print Statement">
                                        <i class="feather-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#studentsTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>
@endpush
@endsection