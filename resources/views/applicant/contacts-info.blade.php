@extends('layouts.app')

@section('title', 'Contact Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-phone me-2"></i> Contact Information
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Your contact details</p>
                </div>
                <div class="card-body">
                    @if($contactInfo)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $contactInfo->phone ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $contactInfo->email ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Region</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $contactInfo->region ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">District</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $contactInfo->district ?? 'Not provided' }}</div>
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
                        <strong>No contact information found!</strong> 
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Please complete your application form
                        </a>
                        to provide your contact information.
                        @else
                        Please start a new application to provide your contact information.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection