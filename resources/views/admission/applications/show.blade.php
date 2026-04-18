{{-- resources/views/admission/applications/show.blade.php --}}
@extends('layouts.admission')

@section('title', 'Application Details - ' . ($application->application_number ?? 'N/A'))

@section('content')
<style>
    .application-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        color: white;
    }
    .info-card {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 25px;
        border: none;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .info-card .card-header {
        background: white;
        border-bottom: 3px solid #667eea;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 12px 12px 0 0 !important;
    }
    .info-card .card-header i {
        color: #667eea;
        margin-right: 10px;
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        width: 35%;
        font-weight: 600;
        color: #555;
    }
    .info-value {
        width: 65%;
        color: #333;
    }
    .status-badge {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-draft { background: #fff3cd; color: #856404; }
    .status-submitted { background: #cfe2ff; color: #084298; }
    .status-approved { background: #d1e7dd; color: #0f5132; }
    .status-rejected { background: #f8d7da; color: #842029; }
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
        display: inline-block;
    }
    .subjects-table {
        background: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
    }
    .subjects-table th {
        background: #667eea;
        color: white;
        border: none;
        padding: 10px;
    }
    .subjects-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    .grade-badge {
        display: inline-block;
        width: 40px;
        text-align: center;
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: bold;
    }
    .grade-A { background: #d4edda; color: #155724; }
    .grade-B { background: #d1ecf1; color: #0c5460; }
    .grade-C { background: #fff3cd; color: #856404; }
    .grade-D { background: #f8d7da; color: #721c24; }
    .grade-E, .grade-F { background: #f5c6cb; color: #721c24; }
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .btn-action {
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="application-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2" style="color: white;">
                    <i class="fas fa-file-alt me-3"></i> 
                    {{ $application->application_number ?? 'N/A' }}
                </h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-user me-2"></i> {{ $application->applicant_name ?? 'Applicant' }}
                    <span class="mx-3">|</span>
                    <i class="fas fa-calendar-alt me-2"></i> 
                    {{ $application->created_at ? date('d F Y', strtotime($application->created_at)) : 'N/A' }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="action-buttons">
                    <a href="{{ route('admission.officer.applications.edit', $application->id) }}" class="btn btn-light btn-action">
                        <i class="fas fa-edit me-2"></i> Edit
                    </a>
                    <a href="{{ route('admission.officer.applications.index') }}" class="btn btn-outline-light btn-action">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
                <div class="mt-3">
                    <span class="status-badge status-{{ $application->status ?? 'draft' }}">
                        <i class="fas fa-{{ $application->status == 'draft' ? 'pencil-alt' : ($application->status == 'submitted' ? 'paper-plane' : ($application->status == 'approved' ? 'check-circle' : 'times-circle')) }} me-2"></i>
                        {{ ucfirst($application->status ?? 'Draft') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Personal & Contact Info -->
        <div class="col-lg-6">
            <!-- Basic Information Card -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Basic Information
                </div>
                <div class="card-body p-0">
                    <div class="info-row">
                        <div class="info-label">Application Number</div>
                        <div class="info-value"><strong>{{ $application->application_number ?? 'N/A' }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Academic Year</div>
                        <div class="info-value">{{ $application->academic_year_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Intake</div>
                        <div class="info-value">{{ $application->intake ?? 'N/A' }} Intake</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Entry Level</div>
                        <div class="info-value">{{ $application->entry_level ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Study Mode</div>
                        <div class="info-value">{{ $application->study_mode ?? 'Full Time' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Submission Date</div>
                        <div class="info-value">
                            @if($application->submitted_at)
                                {{ date('d F Y, H:i', strtotime($application->submitted_at)) }}
                            @else
                                <span class="text-muted">Not submitted yet</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-address-card"></i> Contact Information
                </div>
                <div class="card-body p-0">
                    <div class="info-row">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            @if($contact && $contact->phone)
                                <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                            @else
                                {{ $application->applicant_phone ?? 'N/A' }}
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            @if($contact && $contact->email)
                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                            @else
                                {{ $application->applicant_email ?? 'N/A' }}
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Region</div>
                        <div class="info-value">{{ ($contact && $contact->region) ? $contact->region : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">District</div>
                        <div class="info-value">{{ ($contact && $contact->district) ? $contact->district : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Next of Kin Card -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Next of Kin
                </div>
                <div class="card-body p-0">
                    <div class="info-row">
                        <div class="info-label">Guardian Name</div>
                        <div class="info-value">{{ ($kin && $kin->guardian_name) ? $kin->guardian_name : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Relationship</div>
                        <div class="info-value">{{ ($kin && $kin->relationship) ? $kin->relationship : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Guardian Phone</div>
                        <div class="info-value">{{ ($kin && $kin->guardian_phone) ? $kin->guardian_phone : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Guardian Address</div>
                        <div class="info-value">{{ ($kin && $kin->guardian_address) ? $kin->guardian_address : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-6">
            <!-- Personal Information Card -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                <div class="card-body p-0">
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">
                            @if($personal)
                                <strong>{{ ($personal->first_name ?? '') . ' ' . ($personal->middle_name ?? '') . ' ' . ($personal->last_name ?? '') }}</strong>
                            @else
                                {{ $application->applicant_name ?? 'N/A' }}
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gender</div>
                        <div class="info-value">
                            @if($personal && $personal->gender)
                                @if($personal->gender == 'male')
                                    <i class="fas fa-mars text-primary"></i> Male
                                @else
                                    <i class="fas fa-venus text-danger"></i> Female
                                @endif
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            @if($personal && $personal->date_of_birth)
                                {{ date('d F Y', strtotime($personal->date_of_birth)) }}
                                <span class="text-muted ms-2">
                                    ({{ \Carbon\Carbon::parse($personal->date_of_birth)->age }} years)
                                </span>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nationality</div>
                        <div class="info-value">{{ ($personal && $personal->nationality) ? $personal->nationality : 'Tanzanian' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value">
                            @if($personal && $personal->marital_status)
                                <span class="badge bg-secondary">{{ ucfirst($personal->marital_status) }}</span>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Selection Card -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-graduation-cap"></i> Program Selection
                </div>
                <div class="card-body p-0">
                    <div class="info-row">
                        <div class="info-label">First Choice</div>
                        <div class="info-value">
                            <strong>{{ ($firstProgram->name ?? 'N/A') }}</strong>
                            <span class="text-muted">({{ $firstProgram->code ?? '' }})</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Second Choice</div>
                        <div class="info-value">{{ ($secondProgram->name ?? 'N/A') }} <span class="text-muted">({{ $secondProgram->code ?? '' }})</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Third Choice</div>
                        <div class="info-value">{{ ($thirdProgram->name ?? 'N/A') }} <span class="text-muted">({{ $thirdProgram->code ?? '' }})</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Information Source</div>
                        <div class="info-value">{{ ($programChoice && $programChoice->information_source) ? $programChoice->information_source : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Selected Programme</div>
                        <div class="info-value">
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ ($selectedProgram->name ?? 'N/A') }} ({{ $selectedProgram->code ?? '' }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Information Section - Full Width -->
    <div class="card info-card">
        <div class="card-header">
            <i class="fas fa-chart-line"></i> Academic Information
        </div>
        <div class="card-body">
            @if($academic)
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="section-title">
                            <i class="fas fa-school me-2"></i> CSEE Results
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Index Number</small>
                                <div><strong>{{ $academic->csee_index_number ?? 'N/A' }}</strong></div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">School</small>
                                <div>{{ $academic->csee_school ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <small class="text-muted">Year</small>
                                <div>{{ $academic->csee_year ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <small class="text-muted">Division</small>
                                <div>
                                    <span class="badge bg-primary">Division {{ $academic->csee_division ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <small class="text-muted">Points</small>
                                <div><strong>{{ $academic->csee_points ?? 'N/A' }} points</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if($academic->acsee_index_number)
                            <h6 class="section-title">
                                <i class="fas fa-university me-2"></i> ACSEE Results (Optional)
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Index Number</small>
                                    <div>{{ $academic->acsee_index_number ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">School</small>
                                    <div>{{ $academic->acsee_school ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted">Year</small>
                                    <div>{{ $academic->acsee_year ?? 'N/A' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light text-center mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                No ACSEE results provided
                            </div>
                        @endif
                    </div>
                </div>

                @if($subjects && $subjects->count() > 0)
                    <div class="mt-4">
                        <h6 class="section-title">
                            <i class="fas fa-book-open me-2"></i> Subjects & Grades
                        </h6>
                        <div class="table-responsive subjects-table">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Subject Name</th>
                                        <th width="100">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $index => $subject)
                                    <tr>
                                        <td class="text-center">{{ sprintf('%02d', $index + 1) }}</td>
                                        <td><strong>{{ $subject->subject }}</strong></td>
                                        <td class="text-center">
                                            <span class="grade-badge grade-{{ $subject->grade }}">
                                                {{ $subject->grade }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @else
                <div class="alert alert-warning text-center mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No academic information has been filled yet.
                </div>
            @endif
        </div>
    </div>

    <!-- Declaration Section -->
    @if($declaration)
    <div class="card info-card">
        <div class="card-header">
            <i class="fas fa-file-signature"></i> Declaration
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Declared On</div>
                        <div class="info-value">{{ $declaration->declared_at ? date('d F Y, H:i:s', strtotime($declaration->declared_at)) : 'N/A' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">IP Address</div>
                        <div class="info-value">{{ $declaration->ip_address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="alert alert-success mt-3 mb-0">
                <i class="fas fa-check-circle me-2"></i>
                Applicant has agreed to all terms and conditions
            </div>
        </div>
    </div>
    @endif
</div>
@endsection