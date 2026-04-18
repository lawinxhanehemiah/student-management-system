@extends('layouts.app')

@section('title', 'Program Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-list me-2"></i> Program Information
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Your program choices</p>
                </div>
                <div class="card-body">
                    @if($programChoice)
                    <div class="row">
                        <!-- First Choice -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="feather-award me-2"></i> First Choice Program
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($firstProgram)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program Name</label>
                                                <div class="form-control-plaintext border-bottom pb-2">{{ $firstProgram->name }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program Code</label>
                                                <div class="form-control-plaintext border-bottom pb-2">{{ $firstProgram->code }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Study Mode</label>
                                                <div class="form-control-plaintext border-bottom pb-2">{{ $firstProgram->study_mode ?? 'Full Time' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-success">Selected</span>
                                    @else
                                    <div class="alert alert-warning">
                                        <i class="feather-alert-circle me-2"></i> 
                                        First choice program not found in the system.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Choice -->
                        @if($secondProgram)
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="feather-list me-2"></i> Second Choice Program
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Name</label>
                                        <div class="form-control-plaintext border-bottom pb-2">{{ $secondProgram->name }}</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Code</label>
                                        <div class="form-control-plaintext border-bottom pb-2">{{ $secondProgram->code }}</div>
                                    </div>
                                    
                                    <span class="badge bg-info">Alternative</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Third Choice -->
                        @if($thirdProgram)
                        <div class="col-md-6 mb-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">
                                        <i class="feather-list me-2"></i> Third Choice Program
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Name</label>
                                        <div class="form-control-plaintext border-bottom pb-2">{{ $thirdProgram->name }}</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Code</label>
                                        <div class="form-control-plaintext border-bottom pb-2">{{ $thirdProgram->code }}</div>
                                    </div>
                                    
                                    <span class="badge bg-secondary">Alternative</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Additional Information -->
                    @if($programChoice->information_source)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Additional Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">How did you hear about us?</label>
                                        <div class="form-control-plaintext border-bottom pb-2">{{ $programChoice->information_source }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="alert alert-info mt-4">
                        <i class="feather-info me-2"></i> 
                        <strong>Note:</strong> To edit this information, please go to the application form.
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Edit Application
                        </a>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="feather-alert-circle me-2"></i> 
                        <strong>No program information found!</strong> 
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Please complete your application form
                        </a>
                        to select your program choices.
                        @else
                        Please start a new application to select your program choices.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection