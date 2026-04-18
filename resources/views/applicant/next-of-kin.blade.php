@extends('layouts.app')

@section('title', 'Next of Kin Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-users me-2"></i> Next of Kin Information
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Your next of kin details</p>
                </div>
                <div class="card-body">
                    @if($nextOfKin)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Guardian Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $nextOfKin->guardian_name ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Relationship</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $nextOfKin->relationship ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Guardian Phone</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $nextOfKin->guardian_phone ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                       
                    </div>
                    
                    @if($nextOfKin->guardian_address)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Guardian Address</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $nextOfKin->guardian_address }}</div>
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
                        <strong>No next of kin information found!</strong> 
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Please complete your application form
                        </a>
                        to provide your next of kin information.
                        @else
                        Please start a new application to provide your next of kin information.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection