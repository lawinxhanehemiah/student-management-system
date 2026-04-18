@extends('layouts.financecontroller')

@section('title', 'Payments Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Payments Management</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <span class="breadcrumb-item active">Payments Management</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.payments-management.exam-eligibility') }}" class="btn btn-primary-light btn-wave">
                <i class="feather-check-circle"></i> Exam Eligibility
            </a>
            <button class="btn btn-success-light btn-wave" onclick="exportData()">
                <i class="feather-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Current Year Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Current Academic Year: <span class="text-primary">{{ $currentAcademicYear->name ?? 'N/A' }}</span></h5>
                            <p class="mb-0 text-muted">Today: {{ now()->format('d F, Y') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('finance.payments-management.exam-eligibility') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary by Level and Semester -->
    <div class="row">
        @foreach($levels as $level)
            @foreach($semesters as $semester)
                @if(isset($summary[$level][$semester]))
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-fill">
                                    <span class="d-block mb-2 fw-medium">Year {{ $level }} - Semester {{ $semester }}</span>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Students:</span>
                                        <span class="fw-semibold">{{ $summary[$level][$semester]['total_students'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Eligible:</span>
                                        <span class="fw-semibold">{{ $summary[$level][$semester]['eligible_count'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span>Not Eligible:</span>
                                        <span class="fw-semibold">{{ $summary[$level][$semester]['not_eligible_count'] }}</span>
                                    </div>
                                    <div class="progress progress-sm mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $summary[$level][$semester]['eligible_percentage'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $summary[$level][$semester]['eligible_percentage'] }}% eligible</small>
                                </div>
                                <div class="avatar avatar-lg bg-primary-transparent">
                                    <i class="feather-users fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <a href="{{ route('finance.payments-management.students', ['level' => $level, 'semester' => $semester]) }}" 
                                   class="btn btn-sm btn-primary-light">
                                    View Students <i class="feather-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        @endforeach
    </div>

    <!-- Fee Type Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Payments by Fee Type</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($feeTypeSummary as $type => $data)
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase">{{ $type }}</h6>
                                    <h4 class="text-primary">TZS {{ number_format($data['total_paid'], 2) }}</h4>
                                    <p class="text-muted">{{ $data['count'] }} transactions</p>
                                    <a href="{{ route('finance.payments-management.fee-type', $type) }}" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Quick Actions</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('finance.payments-management.exam-eligibility') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="feather-check-circle fs-2 d-block mb-2"></i>
                                <span>Exam Eligibility</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('finance.student-statements.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="feather-file-text fs-2 d-block mb-2"></i>
                                <span>Student Statement</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('finance.all-payments.index') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="feather-credit-card fs-2 d-block mb-2"></i>
                                <span>All Payments</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('finance.payments-management.export-eligible') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="feather-download fs-2 d-block mb-2"></i>
                                <span>Export Eligible</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportData() {
    window.location.href = '{{ route("finance.payments-management.export-eligible") }}';
}
</script>
@endpush
@endsection