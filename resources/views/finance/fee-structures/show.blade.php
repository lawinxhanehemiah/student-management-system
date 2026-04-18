@extends('layouts.financecontroller')

@section('title', 'Fee Structure Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Fee Structure Details</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.fee-structures.index') }}" class="breadcrumb-item">Fee Structures</a>
                <span class="breadcrumb-item active">View</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.fee-structures.index') }}" class="btn btn-light btn-wave">
                <i class="feather-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Fee Structure Details Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Fee Structure Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Programme</label>
                            <h5>{{ $feeStructure->programme->name ?? 'N/A' }}</h5>
                            <small class="text-muted">Code: {{ $feeStructure->programme->code ?? 'N/A' }}</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Academic Year</label>
                            <h5>{{ $feeStructure->academicYear->name ?? 'N/A' }}</h5>
                            <small class="text-muted">
                                @if($feeStructure->academicYear && $feeStructure->academicYear->start_date)
                                    {{ \Carbon\Carbon::parse($feeStructure->academicYear->start_date)->format('Y') }} -
                                    {{ \Carbon\Carbon::parse($feeStructure->academicYear->end_date)->format('Y') }}
                                @else
                                    N/A
                                @endif
                            </small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Level/Year of Study</label>
                            <h5>Year {{ $feeStructure->level }}</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Status</label><br>
                            @if($feeStructure->is_active)
                                <span class="badge bg-success fs-6">Active</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-semibold mb-3">Fee Breakdown</h6>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <label class="text-muted d-block">Registration Fee</label>
                                    <h4 class="text-primary mt-2">TZS {{ number_format($feeStructure->registration_fee, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <label class="text-muted d-block">Semester 1 Fee</label>
                                    <h4 class="text-primary mt-2">TZS {{ number_format($feeStructure->semester_1_fee, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <label class="text-muted d-block">Semester 2 Fee</label>
                                    <h4 class="text-primary mt-2">TZS {{ number_format($feeStructure->semester_2_fee, 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-success-transparent mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Total Annual Fee:</span>
                                <h3 class="text-success mb-0">TZS {{ number_format($feeStructure->registration_fee + $feeStructure->semester_1_fee + $feeStructure->semester_2_fee, 0) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Audit Info Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Audit Information</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted">Created At</label>
                        <p>{{ $feeStructure->created_at ? $feeStructure->created_at->format('d F Y H:i') : 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted">Last Updated</label>
                        <p>{{ $feeStructure->updated_at ? $feeStructure->updated_at->format('d F Y H:i') : 'N/A' }}</p>
                    </div>
                    
                    @if($feeStructure->created_at != $feeStructure->updated_at)
                    <div class="alert alert-info py-2">
                        <i class="feather-info me-1"></i> This record has been updated since creation.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Info Card -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Additional Information</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted">Programme Study Mode</label>
                        <p>{{ $feeStructure->programme->study_mode ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted">Programme Duration</label>
                        <p>{{ $feeStructure->programme->duration_years ?? 'N/A' }} years</p>
                    </div>
                    
                    <hr>
                    
                    <div class="text-muted small">
                        <i class="feather-info me-1"></i>
                        This fee structure is used for generating invoices for students in this programme, academic year, and level.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection