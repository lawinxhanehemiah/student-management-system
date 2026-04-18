@extends('layouts.app')

@section('title', 'Preview & Submit Application')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="feather-eye me-2"></i> Application Preview
                    </h4>
                    <p class="card-subtitle text-white-50 mb-0">Review your application before submission</p>
                </div>
                <div class="card-body">
                    
                    <!-- Application Status -->
                    @if($application)
                    <div class="alert alert-{{ $application->status == 'submitted' ? 'success' : 'warning' }} mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="alert-heading mb-1">
                                    <i class="feather-{{ $application->status == 'submitted' ? 'check-circle' : 'info' }} me-2"></i>
                                    Application Status: 
                                    <span class="text-uppercase">{{ $application->status }}</span>
                                </h5>
                                <p class="mb-0">
                                    Application Number: <strong>{{ $application->application_number }}</strong>
                                    | Submitted on: 
                                    <strong>{{ $application->submitted_at ? \Carbon\Carbon::parse($application->submitted_at)->format('d/m/Y H:i') : 'Not submitted yet' }}</strong>
                                </p>
                            </div>
                            <div>
                                <span class="badge bg-{{ $application->status == 'submitted' ? 'success' : 'warning' }} fs-6">
                                    {{ strtoupper($application->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Personal Information Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="feather-user text-primary me-2"></i> 
                                Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($personalInfo)
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Full Name:</strong><br>
                                        {{ $personalInfo->first_name }} 
                                        {{ $personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '' }}
                                        {{ $personalInfo->last_name }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Gender:</strong><br>
                                        {{ ucfirst($personalInfo->gender) }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Date of Birth:</strong><br>
                                        {{ \Carbon\Carbon::parse($personalInfo->date_of_birth)->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                Personal information not provided
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="feather-phone text-primary me-2"></i> 
                                Contact Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($contactInfo)
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Phone:</strong><br>
                                        {{ $contactInfo->phone }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong><br>
                                        {{ $contactInfo->email }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Region:</strong><br>
                                        {{ $contactInfo->region }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>District:</strong><br>
                                        {{ $contactInfo->district }}
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                Contact information not provided
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Next of Kin Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="feather-users text-primary me-2"></i> 
                                Next of Kin Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($nextOfKin)
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Guardian Name:</strong><br>
                                        {{ $nextOfKin->guardian_name }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Relationship:</strong><br>
                                        {{ $nextOfKin->relationship }}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Guardian Phone:</strong><br>
                                        {{ $nextOfKin->guardian_phone }}
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                Next of kin information not provided
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Academic Information Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="feather-book text-primary me-2"></i> 
                                Academic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($academicInfo)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <p><strong>CSEE Index:</strong><br>
                                        {{ $academicInfo->csee_index_number }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>School:</strong><br>
                                        {{ $academicInfo->csee_school }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Division:</strong><br>
                                        {{ $academicInfo->csee_division }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($subjects && $subjects->count() > 0)
                            <h6>CSEE Subjects:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Grade</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjects as $subject)
                                        <tr>
                                            <td>{{ $subject->subject }}</td>
                                            <td>{{ $subject->grade }}</td>
                                            <td>{{ $subject->points }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                Academic information not provided
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Program Choices Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="feather-list text-primary me-2"></i> 
                                Program Choices
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($programChoice && $firstProgram)
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="alert alert-success">
                                        <h6 class="mb-1">
                                            <i class="feather-award me-2"></i> 
                                            First Choice: {{ $firstProgram->name }}
                                            <span class="badge bg-success ms-2">Selected</span>
                                        </h6>
                                        <p class="mb-0">Code: {{ $firstProgram->code }}</p>
                                    </div>
                                </div>
                                
                                @if($secondProgram)
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-info">
                                        <h6 class="mb-1">
                                            <i class="feather-list me-2"></i> 
                                            Second Choice
                                        </h6>
                                        <p class="mb-0">{{ $secondProgram->name }}<br>
                                           <small>Code: {{ $secondProgram->code }}</small>
                                        </p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($thirdProgram)
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-secondary">
                                        <h6 class="mb-1">
                                            <i class="feather-list me-2"></i> 
                                            Third Choice
                                        </h6>
                                        <p class="mb-0">{{ $thirdProgram->name }}<br>
                                           <small>Code: {{ $thirdProgram->code }}</small>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                Program choices not provided
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            @if($application && $application->status != 'submitted')
                            <a href="{{ route('applicant.application.form', $application->id) }}" 
                               class="btn btn-primary btn-lg me-3">
                                <i class="feather-edit me-2"></i> Edit Application
                            </a>
                            
                            <button type="button" class="btn btn-success btn-lg" 
                                    onclick="confirmSubmission()">
                                <i class="feather-send me-2"></i> Submit Application
                            </button>
                            
                            <p class="text-muted mt-3">
                                <small>
                                    <i class="feather-info me-1"></i>
                                    Once submitted, you cannot edit your application. 
                                    Please review all information carefully before submitting.
                                </small>
                            </p>
                            @elseif($application && $application->status == 'submitted')
                            <div class="alert alert-success">
                                <h5 class="mb-2">
                                    <i class="feather-check-circle me-2"></i>
                                    Application Submitted Successfully!
                                </h5>
                                <p class="mb-0">
                                    Your application has been submitted on 
                                    <strong>{{ \Carbon\Carbon::parse($application->submitted_at)->format('d/m/Y H:i') }}</strong>.
                                    You will be notified about the next steps via email.
                                </p>
                            </div>
                            
                            <a href="{{ route('applicant.dashboard') }}" 
                               class="btn btn-primary">
                                <i class="feather-home me-2"></i> Go to Dashboard
                            </a>
                            @else
                            <div class="alert alert-warning">
                                <i class="feather-alert-circle me-2"></i> 
                                No application found. Please start a new application.
                            </div>
                            
                            <a href="{{ route('applicant.application.start') }}" 
                               class="btn btn-primary">
                                <i class="feather-plus-circle me-2"></i> Start New Application
                            </a>
                            @endif
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmSubmission() {
    if (confirm('Are you sure you want to submit your application?\n\nOnce submitted, you cannot make changes.')) {
        // Redirect to submit endpoint
        window.location.href = "{{ route('applicant.application.form', $application->id ?? '') }}";
    }
}
</script>
@endpush
@endsection