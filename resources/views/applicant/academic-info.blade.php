@extends('layouts.app')

@section('title', 'Academic Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-book me-2"></i> Academic Information
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Your academic details</p>
                </div>
                <div class="card-body">
                    @if($academicInfo)
                    <!-- CSEE Information -->
                    <h5 class="mb-3 border-bottom pb-2">CSEE (Form IV) Results</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Index Number</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->csee_index_number ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">School Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->csee_school ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Year</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->csee_year ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Division</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->csee_division ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Points</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->csee_points ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ACSEE Information (if available) -->
                    @if($academicInfo->acsee_index_number)
                    <h5 class="mb-3 mt-4 border-bottom pb-2">ACSEE (Form VI) Results</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Index Number</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->acsee_index_number }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">School Name</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->acsee_school }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Year</label>
                                <div class="form-control-plaintext border-bottom pb-2">{{ $academicInfo->acsee_year }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Subjects Table -->
                    @if($subjects && $subjects->count() > 0)
                    <h5 class="mb-3 mt-4 border-bottom pb-2">CSEE Subjects</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $index => $subject)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $subject->subject }}</td>
                                    <td>{{ $subject->grade }}</td>
                                    <td>{{ $subject->points }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                        <strong>No academic information found!</strong> 
                        @if($application)
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link ms-1">
                            Please complete your application form
                        </a>
                        to provide your academic information.
                        @else
                        Please start a new application to provide your academic information.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection