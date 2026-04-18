@extends('layouts.app')

@section('title', 'Application Status')

@section('content')
<div class="container-fluid py-4">

    {{-- PROFILE HEADER --}}
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">

            <div class="d-flex align-items-center">
                <div class="me-3">
                    <img src="{{ asset('assets/images/default.png') }}"
                         class="rounded-circle border"
                         width="90" height="90"
                         alt="Profile">
                </div>

                <div>
                    <h5 class="mb-0">
                        {{ $personalInfo->first_name ?? 'Applicant' }} 
                        {{ $personalInfo->middle_name ?? '' }} 
                        {{ $personalInfo->last_name ?? '' }}
                    </h5>
                    <small class="text-muted">
                        {{ ucfirst($personalInfo->gender ?? 'Not specified') }}
                    </small>
                </div>
            </div>

            <div class="text-center">
                <strong class="fw-bold">Controller Number</strong><br>
                <small class="fw-bold" style="color: red; font-size: 1.3rem; letter-spacing: 1px;">
                    {{ $application->registration_fee_control_number ?? 'N/A' }}
                </small>
            </div>

            <div>
                @php
                    $status = strtolower($application->status ?? 'draft');
                    $badgeClass = 'secondary';
                    $badgeText = 'In Progress';
                    
                    if ($status == 'selected' || $status == 'approved') {
                        $badgeClass = 'success';
                        $badgeText = 'Selected';
                    } elseif ($status == 'submitted') {
                        $badgeClass = 'info';
                        $badgeText = 'Submitted';
                    } elseif ($status == 'under_review') {
                        $badgeClass = 'warning';
                        $badgeText = 'Under Review';
                    } elseif ($status == 'rejected' || $status == 'cancelled') {
                        $badgeClass = 'danger';
                        $badgeText = ucfirst($status);
                    }
                @endphp
                <span class="badge bg-{{ $badgeClass }} px-3 py-2">
                    {{ $badgeText }}
                </span>
            </div>

        </div>
    </div>

   

    {{-- APPLICATION DETAILS --}}
    <div class="card mb-4 bg-light">
        <div class="card-body">
            <p>
                <strong>Application Status:</strong>
                <span class="badge bg-{{ $admissionStatus['color'] ?? 'secondary' }}">
                    {{ $admissionStatus['status'] ?? 'In Progress' }}
                </span>
            </p>

            <p><strong>Application Deadline:</strong> {{ $importantDates['application_deadline'] ?? '30 Mar, 2026' }}</p>
            <p><strong>Study Level:</strong> {{ ucfirst($application->entry_level ?? 'Not specified') }}</p>
            <p><strong>Academic Year:</strong> {{ $application->academic_year_name ?? 'N/A' }}</p>
            <p><strong>Intake:</strong> {{ $application->intake ?? 'Not specified' }}</p>
            <p><strong>Study Mode:</strong> {{ $application->study_mode ?? 'Full Time' }}</p>
            
            {{-- Hii ya zamani tuifute kwa kuwa tayari tumetoa message maalum hapo juu --}}
            @if(!empty($admissionStatus['message']) && !(strtolower($application->status) == 'selected' || strtolower($application->status) == 'approved'))
                <div class="alert alert-{{ $admissionStatus['color'] ?? 'info' }} mt-3 mb-0">
                    <i class="fas fa-{{ $admissionStatus['icon'] ?? 'info-circle' }}"></i>
                    {{ $admissionStatus['message'] }}
                </div>
            @endif
        </div>
    </div>

     {{-- HII NI SEHEMU MPYA YA CONGRATULATIONS --}}
    @if(strtolower($application->status) == 'selected' || strtolower($application->status) == 'approved')
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-trophy fa-2x"></i>
            </div>
            <div>
    <h5 class="mb-1">🎉 Congratulations! You have been SELECTED to join our institution.</h5>
    <p class="mb-1">
        <strong>Selected Programme:</strong>
        @php
            // Determine program name and code
            $programName = 'Not specified';
            $programCode = '';
            
            if(isset($admissionStatus['selected_program'])) {
                $programName = $admissionStatus['selected_program']['name'] ?? 'Not specified';
                $programCode = $admissionStatus['selected_program']['code'] ?? '';
            } elseif(isset($programs[0])) {
                $programName = $programs[0]['name'] ?? 'Not specified';
                $programCode = $programs[0]['code'] ?? '';
            }
        @endphp
        
        <span class="fw-bold text-warning" style="
            text-transform: uppercase;
            font-size: 1.1em;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        ">
            {{ strtoupper($programName) }}
        </span>
        
        @if($programCode)
            <span class="text-light bg-dark px-2 py-1 rounded ms-2">
                {{ $programCode }}
            </span>
        @endif
    </p>
    <p class="mb-0">We congratulate you and wish you success in your academic endeavors.</p>
    <p class="mb-0"><strong>Click the button below to download admission letter and join instruction.</strong></p>
</div>
        </div>
    </div>

    
    @endif

    {{-- INFO ALERT --}}
    <div class="alert alert-info text-center mb-4">
    @if(($application->status ?? 'draft') == 'draft')
        Your application is still in draft. 
        <a href="{{ route('applicant.application.form', $application->id) }}" class="alert-link">
            Click here to complete it
        </a>.
    @else
        If you face any problem in your application please contact support. 
        <span style="color: red; font-weight: bold;">0712699596</span>, 
        <span style="color: red; font-weight: bold;">0782278027</span>
    @endif
</div>
 {{-- PAYMENT INFORMATION --}}
    @if(($application->status ?? 'draft') != 'draft')
    <div class="card mt-4" id="download-section">
        
        <div class="card-body">
            
            
            {{-- ADD DOWNLOAD BUTTON IF APPROVED --}}
            @if(strtolower($application->status) == 'selected' || strtolower($application->status) == 'approved')
            <div class="mt-4 pt-3 border-top">
                <h6 class="mb-2">Admission Documents</h6>
                <p class="text-muted mb-3">Download your admission documents:</p>
                
                {{-- TUMIA ROUTES AMBAZO UNAZO --}}
                <div class="d-flex gap-2 flex-wrap">
                    {{-- OPTION 1: Tumia AdmissionLetterController --}}
                    <a href="{{ route('applicant.download.form') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-download me-2"></i> Download Admission Form
                    </a>
                    
                    {{-- OPTION 2: Au tumia DashboardController --}}
                    <a href="{{ url('/applicant/download-admission-letter/' . $application->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf me-2"></i> Joining Instructions
                    </a>
                    
                    {{-- OPTION 3: Au tumia ApplicationController --}}
                    <a href="{{ route('applicant.dashboard') }}" class="btn btn-info">
                        <i class="fas fa-envelope me-2"></i> View Admission Letter
                    </a>
                </div>
                
                
            </div>
            @endif
            
            {{-- BONUS: Orodha ya vitendo kama umechaguliwa --}}
            @if(strtolower($application->status) == 'selected' || strtolower($application->status) == 'approved')
            <div class="mt-4 pt-3 border-top">
                
                <div class="list-group">
                   
                    
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">1. Prepare Required Documents</h6>
                            <span class="badge bg-info">Required</span>
                        </div>
                        <p class="mb-1">Prepare all original documents for verification during registration.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif



    {{-- PROGRAMMES TABLE --}}
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-1">Programme(s)</h6>
            <small class="text-muted">
                Your selected and alternative programs
            </small>
        </div>

        <div class="card-body p-0">
            @if(count($programs) > 0)
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Study Mode</th>
                            <th style="width:150px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programs as $program)
                        <tr>
                            <td>{{ $program['number'] }}</td>
                            <td>{{ $program['name'] }}</td>
                            <td>{{ $program['code'] }}</td>
                            <td>{{ $program['study_mode'] }}</td>
                            <td>
                                @if($program['status'] == 'Selected')
                                    <span class="badge bg-success">{{ $program['status'] }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $program['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center p-4">
                    <p class="text-muted mb-0">No program selected yet.</p>
                    @if(($application->status ?? 'draft') == 'draft')
                        <a href="{{ route('applicant.application.form', $application->id) }}" class="btn btn-sm btn-primary mt-2">
                            Complete Application
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

   
</div>
@endsection