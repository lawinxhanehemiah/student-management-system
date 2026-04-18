@extends('layouts.app')

@section('title', 'Download Application Form')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">
                    <i class="fas fa-download me-2 text-primary"></i>
                    Download Application Form
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('applicant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Download Form</li>
                    </ol>
                </nav>
            </div>
            <div>
                <span class="badge bg-info fs-6 px-3 py-2">
                    <i class="fas fa-file-alt me-1"></i>
                    Application #{{ $application->application_number }}
                </span>
            </div>
        </div>
    </div>

    <!-- Download Options Card -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0">
                <i class="fas fa-file-pdf me-2"></i>
                Application Form Download Options
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Preview Option -->
                <div class="col-md-4">
                    <div class="card border-primary h-100 text-center">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="fas fa-eye fa-4x text-primary"></i>
                            </div>
                            <h4 class="card-title text-primary">Preview PDF</h4>
                            <p class="card-text text-muted">
                                View your application form in PDF format before downloading.
                            </p>
                            <a href="{{ route('applicant.download.preview', $application->id) }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-eye me-2"></i> Preview Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Download PDF Option -->
                <div class="col-md-4">
                    <div class="card border-success h-100 text-center">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="fas fa-file-pdf fa-4x text-success"></i>
                            </div>
                            <h4 class="card-title text-success">Download PDF</h4>
                            <p class="card-text text-muted">
                                Download your complete application form as a PDF document.
                            </p>
                            <a href="{{ route('applicant.download.generate-pdf', $application->id) }}" 
                               class="btn btn-success btn-lg w-100">
                                <i class="fas fa-download me-2"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Print Option -->
                <div class="col-md-4">
                    <div class="card border-info h-100 text-center">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="fas fa-print fa-4x text-info"></i>
                            </div>
                            <h4 class="card-title text-info">Print Directly</h4>
                            <p class="card-text text-muted">
                                Print your application form directly from your browser.
                            </p>
                            <button onclick="window.print()" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-print me-2"></i> Print Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Summary -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0">
                <i class="fas fa-file-alt me-2 text-primary"></i>
                Application Summary
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Basic Info -->
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td width="40%"><strong>Application Number:</strong></td>
                                    <td>{{ $application->application_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'submitted' => 'info',
                                                'approved' => 'success',
                                                'under_review' => 'warning',
                                                'rejected' => 'danger',
                                                'draft' => 'secondary'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$application->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Academic Year:</strong></td>
                                    <td>{{ $academicYear->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Intake:</strong></td>
                                    <td>{{ $application->intake }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Entry Level:</strong></td>
                                    <td>{{ $application->entry_level }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Submitted Date:</strong></td>
                                    <td>
                                        @if($application->submitted_at)
                                            {{ \Carbon\Carbon::parse($application->submitted_at)->format('d M, Y') }}
                                        @else
                                            Not submitted
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="col-md-6">
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td width="40%"><strong>Full Name:</strong></td>
                                    <td>
                                        {{ $personal->first_name ?? '' }} 
                                        {{ $personal->middle_name ?? '' }} 
                                        {{ $personal->last_name ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>{{ ucfirst($personal->gender ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Birth:</strong></td>
                                    <td>
                                        @if($personal->date_of_birth)
                                            {{ \Carbon\Carbon::parse($personal->date_of_birth)->format('d M, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nationality:</strong></td>
                                    <td>{{ $personal->nationality ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Program Choices -->
                <div class="col-12">
                    <div class="card border mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Program Choices</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @if($firstProgram)
                                <div class="col-md-4">
                                    <div class="card border-primary h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">
                                                <i class="fas fa-1 me-1"></i> First Choice
                                            </h6>
                                            <h5 class="fw-bold">{{ $firstProgram->code }}</h5>
                                            <p class="mb-0">{{ $firstProgram->name }}</p>
                                            @if($firstProgram->study_mode)
                                            <small class="text-muted">
                                                {{ ucfirst($firstProgram->study_mode) }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($secondProgram)
                                <div class="col-md-4">
                                    <div class="card border-info h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">
                                                <i class="fas fa-2 me-1"></i> Second Choice
                                            </h6>
                                            <h5 class="fw-bold">{{ $secondProgram->code }}</h5>
                                            <p class="mb-0">{{ $secondProgram->name }}</p>
                                            @if($secondProgram->study_mode)
                                            <small class="text-muted">
                                                {{ ucfirst($secondProgram->study_mode) }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($thirdProgram)
                                <div class="col-md-4">
                                    <div class="card border-secondary h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">
                                                <i class="fas fa-3 me-1"></i> Third Choice
                                            </h6>
                                            <h5 class="fw-bold">{{ $thirdProgram->code }}</h5>
                                            <p class="mb-0">{{ $thirdProgram->name }}</p>
                                            @if($thirdProgram->study_mode)
                                            <small class="text-muted">
                                                {{ ucfirst($thirdProgram->study_mode) }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            @if($selectedProgramDetails)
                            <div class="alert alert-success mt-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                                    <div>
                                        <h6 class="mb-1">Approved Program</h6>
                                        <p class="mb-0 fw-bold">
                                            {{ $selectedProgramDetails->code }} - {{ $selectedProgramDetails->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Notes -->
    <div class="alert alert-warning border-0 shadow-sm">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div>
                <h5 class="alert-heading">Important Notes</h5>
                <ul class="mb-0">
                    <li>Please download and print a copy of your application form for your records.</li>
                    <li>Bring the printed form when coming for registration.</li>
                    <li>Ensure all information is correct before downloading.</li>
                    <li>The downloaded form contains all your application details.</li>
                    <li>Keep this document safe as it may be required for future reference.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
.table-sm td {
    padding: 0.5rem;
}
</style>

<script>
// Auto-refresh page when printing is done
window.onafterprint = function() {
    // Optionally show a message
    console.log("Print completed");
};
</script>
@endsection