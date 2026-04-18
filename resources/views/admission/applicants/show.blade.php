@extends('layouts.admission')

@section('title', 'Application Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Application Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">All Applications</a></li>
                        <li class="breadcrumb-item active">{{ $application->application_number }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                
                @if($application->status == 'submitted' || $application->status == 'under_review')
                    <button type="button" class="btn btn-success" id="approveBtn">
                        <i class="fas fa-check me-2"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" id="rejectBtn">
                        <i class="fas fa-times me-2"></i> Reject
                    </button>
                    <button type="button" class="btn btn-info" id="waitlistBtn">
                        <i class="fas fa-hourglass-half me-2"></i> Waitlist
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Banner -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        @php
                            $statusBadgeClass = 'secondary';
                            $statusText = 'Unknown';
                            
                            if ($application->status == 'approved') {
                                $statusBadgeClass = 'success';
                                $statusText = 'Approved';
                            } elseif ($application->status == 'rejected') {
                                $statusBadgeClass = 'danger';
                                $statusText = 'Rejected';
                            } elseif ($application->status == 'waitlisted') {
                                $statusBadgeClass = 'info';
                                $statusText = 'Waitlisted';
                            } elseif ($application->status == 'submitted') {
                                $statusBadgeClass = 'warning';
                                $statusText = 'Pending Review';
                            } elseif ($application->status == 'under_review') {
                                $statusBadgeClass = 'primary';
                                $statusText = 'Under Review';
                            }
                        @endphp
                        <span class="badge bg-{{ $statusBadgeClass }} fs-6 px-3 py-2">
                            {{ $statusText }}
                        </span>
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $personal->first_name ?? '' }} {{ $personal->last_name ?? '' }}</h4>
                        <p class="text-muted mb-0">
                            Application #{{ $application->application_number ?? 'N/A' }} • 
                            Academic Year: {{ $application->academic_year ?? 'N/A' }} • 
                            Intake: {{ $application->intake ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="mb-0">
                    <small class="text-muted">Submitted: {{ isset($application->created_at) ? date('d/m/Y H:i', strtotime($application->created_at)) : 'N/A' }}</small>
                </p>
                @if(!empty($application->approved_at))
                    <p class="mb-0">
                        <small class="text-muted">Approved: {{ date('d/m/Y H:i', strtotime($application->approved_at)) }}</small>
                    </p>
                @endif
                @if(!empty($application->rejected_at))
                    <p class="mb-0">
                        <small class="text-muted">Rejected: {{ date('d/m/Y H:i', strtotime($application->rejected_at)) }}</small>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

    <!-- Application Sections -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Personal Information -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Personal Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Full Name:</strong><br>
                {{ $personal->first_name ?? 'N/A' }} {{ $personal->middle_name ?? '' }} {{ $personal->last_name ?? '' }}</p>
                
                <p><strong>Gender:</strong><br>
                {{ $personal->gender ?? 'N/A' }}</p>
                
                <p><strong>Date of Birth:</strong><br>
                @if(!empty($personal->date_of_birth))
                    {{ date('d/m/Y', strtotime($personal->date_of_birth)) }}
                @else
                    N/A
                @endif
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Nationality:</strong><br>
                {{ $personal->nationality ?? 'N/A' }}</p>
                
                <p><strong>National ID:</strong><br>
                {{ $personal->national_id ?? 'N/A' }}</p>
                
                <p><strong>Marital Status:</strong><br>
                {{ $personal->marital_status ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
            <!-- Academic Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Academic Information</h5>
                </div>
                <div class="card-body">
                    @if($academic)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>CSEE Details</h6>
                                <p><strong>Index Number:</strong> {{ $academic->csee_index_number ?? 'N/A' }}</p>
                                <p><strong>School:</strong> {{ $academic->csee_school ?? 'N/A' }}</p>
                                <p><strong>Year:</strong> {{ $academic->csee_year ?? 'N/A' }}</p>
                                <p><strong>Division:</strong> {{ $academic->csee_division ?? 'N/A' }}</p>
                                <p><strong>Points:</strong> {{ $academic->csee_points ?? 'N/A' }}</p>
                            </div>
                            @if($academic->acsee_index_number)
                                <div class="col-md-6">
                                    <h6>ACSEE Details</h6>
                                    <p><strong>Index Number:</strong> {{ $academic->acsee_index_number ?? 'N/A' }}</p>
                                    <p><strong>School:</strong> {{ $academic->acsee_school ?? 'N/A' }}</p>
                                    <p><strong>Year:</strong> {{ $academic->acsee_year ?? 'N/A' }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Subjects Table -->
@if($subjects->count() > 0)
    <h6>O-Level Subjects</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    <tr>
                        <td>
                            {{ 
                                // Try different possible column names
                                $subject->subject_name ?? 
                                $subject->name ?? 
                                $subject->subject ?? 
                                'Unknown Subject' 
                            }}
                        </td>
                        <td>{{ $subject->grade ?? 'N/A' }}</td>
                        <td>{{ $subject->points ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
                    @else
                        <p class="text-muted">No academic information provided.</p>
                    @endif
                </div>
            </div>

            <!-- Program Choices -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> Program Choices</h5>
    </div>
    <div class="card-body">
        @if($programChoice)
            <div class="row">
                @php
                    // Get all program details
                    $firstProgram = $programChoice->first_choice_program_id ? 
                        DB::table('programmes')->find($programChoice->first_choice_program_id) : null;
                    $secondProgram = $programChoice->second_choice_program_id ? 
                        DB::table('programmes')->find($programChoice->second_choice_program_id) : null;
                    $thirdProgram = $programChoice->third_choice_program_id ? 
                        DB::table('programmes')->find($programChoice->third_choice_program_id) : null;
                    
                    // Get selected program if exists
                    $selectedProgram = $application->selected_program_id ? 
                        DB::table('programmes')->find($application->selected_program_id) : null;
                @endphp
                
                <!-- First Choice -->
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border {{ $selectedProgram && $selectedProgram->id == $firstProgram->id ? 'border-success border-2' : 'border-light' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title {{ $selectedProgram && $selectedProgram->id == $firstProgram->id ? 'text-success' : 'text-primary' }}">
                                        <i class="fas fa-1 me-1"></i> First Choice
                                    </h6>
                                    <p class="mb-1"><strong>{{ $firstProgram->code ?? 'N/A' }} - {{ $firstProgram->name ?? 'N/A' }}</strong></p>
                                    @if($firstProgram->study_mode)
                                        <p class="text-muted mb-0 small">
                                            {{ ucfirst(str_replace('_', ' ', $firstProgram->study_mode)) }}
                                        </p>
                                    @endif
                                </div>
                                @if($selectedProgram && $selectedProgram->id == $firstProgram->id)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Selected
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Choice -->
                @if($secondProgram)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border {{ $selectedProgram && $selectedProgram->id == $secondProgram->id ? 'border-success border-2' : 'border-light' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-info">
                                        <i class="fas fa-2 me-1"></i> Second Choice
                                    </h6>
                                    <p class="mb-1"><strong>{{ $secondProgram->code ?? 'N/A' }} - {{ $secondProgram->name ?? 'N/A' }}</strong></p>
                                    @if($secondProgram->study_mode)
                                        <p class="text-muted mb-0 small">
                                            {{ ucfirst(str_replace('_', ' ', $secondProgram->study_mode)) }}
                                        </p>
                                    @endif
                                </div>
                                @if($selectedProgram && $selectedProgram->id == $secondProgram->id)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Selected
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Third Choice -->
                @if($thirdProgram)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border {{ $selectedProgram && $selectedProgram->id == $thirdProgram->id ? 'border-success border-2' : 'border-light' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-secondary">
                                        <i class="fas fa-3 me-1"></i> Third Choice
                                    </h6>
                                    <p class="mb-1"><strong>{{ $thirdProgram->code ?? 'N/A' }} - {{ $thirdProgram->name ?? 'N/A' }}</strong></p>
                                    @if($thirdProgram->study_mode)
                                        <p class="text-muted mb-0 small">
                                            {{ ucfirst(str_replace('_', ' ', $thirdProgram->study_mode)) }}
                                        </p>
                                    @endif
                                </div>
                                @if($selectedProgram && $selectedProgram->id == $thirdProgram->id)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Selected
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Display Selected Program -->
            @if($selectedProgram)
            <div class="alert alert-success mt-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1">Approved Program:</h6>
                        <p class="mb-0">
                            <strong>{{ $selectedProgram->code ?? 'N/A' }} - {{ $selectedProgram->name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">
                                @if($selectedProgram->id == $firstProgram->id)
                                    Applicant's First Choice
                                @elseif($secondProgram && $selectedProgram->id == $secondProgram->id)
                                    Applicant's Second Choice
                                @elseif($thirdProgram && $selectedProgram->id == $thirdProgram->id)
                                    Applicant's Third Choice
                                @else
                                    Selected Program
                                @endif
                            </small>
                        </p>
                    </div>
                </div>
            </div>
            @endif
            
            @if($programChoice->information_source)
                <div class="mt-3">
                    <p><strong>How did you hear about us?</strong><br>
                    {{ $programChoice->information_source }}</p>
                </div>
            @endif
            
        @else
            <p class="text-muted">No program choices selected.</p>
        @endif
    </div>
</div>
</div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Contact Information -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-phone me-2"></i> Contact Information</h5>
    </div>
    <div class="card-body">
        <p><strong>Phone:</strong><br>
        {{ $contact->phone ?? 'N/A' }}</p>
        
        <p><strong>Region:</strong><br>
        {{ $contact->region ?? 'N/A' }}</p>
        
        <p><strong>District:</strong><br>
        {{ $contact->district ?? 'N/A' }}</p>
        
        <p><strong>Address:</strong><br>
        {{ $contact->address ?? 'N/A' }}</p>
    </div>
</div>
            <!-- Next of Kin -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Next of Kin</h5>
    </div>
    <div class="card-body">
        <p><strong>Name:</strong><br>
        {{ $kin->guardian_name ?? 'N/A' }}</p>
        
        <p><strong>Phone:</strong><br>
        {{ $kin->guardian_phone ?? 'N/A' }}</p>
        
        <p><strong>Relationship:</strong><br>
        {{ $kin->relationship ?? 'N/A' }}</p>
        
        <p><strong>Address:</strong><br>
        {{ $kin->guardian_address ?? 'N/A' }}</p>
    </div>
</div>
           <!-- Application Meta -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Application Details</h5>
    </div>
    <div class="card-body">
        <p><strong>Entry Level:</strong><br>
        {{ $application->entry_level ?? 'N/A' }}</p>
        
        <p><strong>Is Free Application:</strong><br>
        {{ isset($application->is_free_application) ? ($application->is_free_application ? 'Yes' : 'No') : 'N/A' }}</p>
        
        @if(!empty($application->fee_waiver_reason))
            <p><strong>Fee Waiver Reason:</strong><br>
            {{ $application->fee_waiver_reason }}</p>
        @endif
        
        <p><strong>Application Steps Completed:</strong></p>
        <ul class="list-unstyled">
            <li><i class="fas {{ !empty($application->step_personal_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Personal Info</li>
            <li><i class="fas {{ !empty($application->step_contact_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Contact Info</li>
            <li><i class="fas {{ !empty($application->step_next_of_kin_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Next of Kin</li>
            <li><i class="fas {{ !empty($application->step_academic_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Academic Info</li>
            <li><i class="fas {{ !empty($application->step_programs_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Program Choices</li>
            <li><i class="fas {{ !empty($application->step_declaration_completed) ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i> Declaration</li>
        </ul>
    </div>
</div>
            <!-- Audit Trail -->
            @if($auditLogs && $auditLogs->count() > 0)
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Audit Trail</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($auditLogs as $log)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong class="text-{{ $log->action == 'approved' ? 'success' : ($log->action == 'rejected' ? 'danger' : ($log->action == 'waitlisted' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($log->action) }}
                                        </strong>
                                        <small class="text-muted">{{ date('d/m/Y H:i', strtotime($log->created_at)) }}</small>
                                    </div>
                                    @if($log->notes)
                                        <p class="mb-0 mt-1 small">{{ $log->notes }}</p>
                                    @endif
                                    @if($log->performed_by)
                                        <small class="text-muted">By: User #{{ $log->performed_by }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .card-header.bg-light {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .badge.fs-6 {
        font-size: 1rem !important;
        padding: 0.5rem 1rem;
    }
    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(0,0,0,.1);
        border-radius: 50%;
        border-top-color: #007bff;
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Get CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const applicationId = "{{ $application->id }}";
    const applicationNumber = "{{ $application->application_number }}";
    
    // Setup AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
    
    // Function to show error
    function showError(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
    
    // APPROVE BUTTON
    $('#approveBtn').click(function() {
        // Show loading
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching program choices',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Get program choices first
        $.ajax({
            url: `/admission/applicants/${applicationId}/program-choices`,
            method: 'GET',
            success: function(response) {
                Swal.close();
                
                if (!response.success) {
                    showError('Error', response.message || 'Failed to load program choices');
                    return;
                }
                
                let programs = response.choices;
                let hasChoices = false;
                let optionsHtml = '';
                
                // First Choice
                if (programs.first_choice) {
                    hasChoices = true;
                    optionsHtml += `
                        <div class="program-option border rounded p-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input program-radio" 
                                       type="radio" 
                                       name="selected_program" 
                                       id="program_first" 
                                       value="${programs.first_choice.id}"
                                       checked>
                                <label class="form-check-label d-block" for="program_first">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="d-block">${programs.first_choice.name}</strong>
                                            ${programs.first_choice.code ? `<small class="text-muted">Code: ${programs.first_choice.code}</small>` : ''}
                                        </div>
                                        <span class="badge bg-primary">First Choice</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                }
                
                // Second Choice
                if (programs.second_choice) {
                    hasChoices = true;
                    optionsHtml += `
                        <div class="program-option border rounded p-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input program-radio" 
                                       type="radio" 
                                       name="selected_program" 
                                       id="program_second" 
                                       value="${programs.second_choice.id}">
                                <label class="form-check-label d-block" for="program_second">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="d-block">${programs.second_choice.name}</strong>
                                            ${programs.second_choice.code ? `<small class="text-muted">Code: ${programs.second_choice.code}</small>` : ''}
                                        </div>
                                        <span class="badge bg-info">Second Choice</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                }
                
                // Third Choice
                if (programs.third_choice) {
                    hasChoices = true;
                    optionsHtml += `
                        <div class="program-option border rounded p-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input program-radio" 
                                       type="radio" 
                                       name="selected_program" 
                                       id="program_third" 
                                       value="${programs.third_choice.id}">
                                <label class="form-check-label d-block" for="program_third">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="d-block">${programs.third_choice.name}</strong>
                                            ${programs.third_choice.code ? `<small class="text-muted">Code: ${programs.third_choice.code}</small>` : ''}
                                        </div>
                                        <span class="badge bg-secondary">Third Choice</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    `;
                }
                
                if (!hasChoices) {
                    Swal.fire({
                        title: 'No Program Choices',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                <p>This applicant has not selected any programs.</p>
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Show program selection dialog
                Swal.fire({
                    title: 'Approve Application',
                    html: `
                        <div class="text-start">
                            <p>Select program for <strong>${applicationNumber}</strong>:</p>
                            <div id="programsList" class="mb-3">
                                ${optionsHtml}
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Approval Notes (optional)</label>
                                <textarea class="form-control" id="approveNotes" rows="3" 
                                          placeholder="Enter approval notes..."></textarea>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                This will change the application status to "Approved".
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check me-2"></i>Approve',
                    cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    width: '600px',
                    preConfirm: () => {
                        const selectedProgram = document.querySelector('input[name="selected_program"]:checked');
                        const notes = document.getElementById('approveNotes').value;
                        
                        if (!selectedProgram) {
                            Swal.showValidationMessage('Please select a program');
                            return false;
                        }
                        
                        return {
                            programId: selectedProgram.value,
                            notes: notes
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const { programId, notes } = result.value;
                        
                        // Show processing
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Approving application',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send approval request
                        $.ajax({
                            url: `/admission/applicants/${applicationId}/approve`,
                            method: 'POST',
                            data: {
                                selected_program_id: programId,
                                notes: notes
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        html: `
                                            <div class="text-center">
                                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                <p>${response.message || 'Application approved successfully!'}</p>
                                                <p class="text-muted small">Page will refresh...</p>
                                            </div>
                                        `,
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true,
                                        didClose: () => {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message || 'Failed to approve application',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Failed to approve application. ';
                                
                                if (xhr.status === 419) {
                                    errorMsg = 'Session expired. Please refresh the page.';
                                } else if (xhr.status === 422) {
                                    errorMsg = 'Validation error. Please check your input.';
                                } else if (xhr.status === 404) {
                                    errorMsg = 'Application not found.';
                                }
                                
                                Swal.fire({
                                    title: 'Error',
                                    text: errorMsg,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            },
            error: function(xhr) {
                Swal.close();
                showError('Error', 'Failed to load program choices. Please try again.');
            }
        });
    });
    
    // REJECT BUTTON
    $('#rejectBtn').click(function() {
        Swal.fire({
            title: 'Reject Application',
            html: `
                <div class="text-start">
                    <p>Reject <strong>${applicationNumber}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Reason:</label>
                        <textarea class="form-control" id="rejectReason" rows="4" 
                                  placeholder="Enter rejection reason..." required></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            width: '500px',
            preConfirm: () => {
                const reason = document.getElementById('rejectReason').value;
                if (!reason.trim()) {
                    Swal.showValidationMessage('Reason is required');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value;
                
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.ajax({
                    url: `/admission/applicants/${applicationId}/reject`,
                    method: 'POST',
                    data: { reason: reason },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Application rejected successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Failed to reject application', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to reject application', 'error');
                    }
                });
            }
        });
    });
    
    // WAITLIST BUTTON
    $('#waitlistBtn').click(function() {
        Swal.fire({
            title: 'Waitlist Application',
            html: `
                <div class="text-start">
                    <p>Waitlist <strong>${applicationNumber}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional):</label>
                        <textarea class="form-control" id="waitlistNotes" rows="3" 
                                  placeholder="Enter waitlist notes..."></textarea>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Waitlist',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#17a2b8',
            width: '500px'
        }).then((result) => {
            if (result.isConfirmed) {
                const notes = $('#waitlistNotes').val();
                
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.ajax({
                    url: `/admission/applicants/${applicationId}/waitlist`,
                    method: 'POST',
                    data: { notes: notes },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', 'Application waitlisted', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to waitlist', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to waitlist application', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

@endsection