@extends('layouts.app')

@section('title', 'Personal Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-user me-2"></i> Personal Information
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Your personal details</p>
                </div>
                <div class="card-body">
                    @if($personalInfo)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">First Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $personalInfo->first_name ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Middle Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $personalInfo->middle_name ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $personalInfo->last_name ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Gender</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ ucfirst($personalInfo->gender ?? 'Not provided') }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date of Birth</label>
                                <div class="form-control-plaintext border-bottom pb-2">
                                    {{ $personalInfo->date_of_birth ? \Carbon\Carbon::parse($personalInfo->date_of_birth)->format('d/m/Y') : 'Not provided' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nationality</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $personalInfo->nationality ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Marital Status</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ ucfirst($personalInfo->marital_status ?? 'Not provided') }}</div>
                            </div>
                        </div>
                    </div>
                    
                   
                    
                    
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
                        <strong>No personal information found!</strong> 
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Please complete your application form
                        </a>
                        to provide your personal information.
                        @else
                        Please start a new application to provide your personal information.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection