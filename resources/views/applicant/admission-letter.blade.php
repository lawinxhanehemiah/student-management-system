@extends('layouts.applicant')

@section('title', 'Admission Letter')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-certificate me-2"></i> Admission Letter
                        </h4>
                        <div>
                            <button class="btn btn-light btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <a href="{{ route('applicant.admission-letter.download') }}" 
                               class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-download me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Letter Header -->
                    <div class="text-center mb-5">
                        <img src="{{ asset('images/college-logo.png') }}" 
                             alt="College Logo" height="80" class="mb-3">
                        <h2 class="text-success">{{ config('app.name', 'College Name') }}</h2>
                        <p class="text-muted mb-0">P.O. Box 123, City, Country</p>
                        <p class="text-muted">Phone: +255 XXX XXX XXX | Email: info@college.ac.tz</p>
                    </div>
                    
                    <!-- Reference Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Reference No:</strong> 
                                {{ $application->application_number }}
                            </p>
                            <p class="mb-1">
                                <strong>Date:</strong> 
                                {{ \Carbon\Carbon::parse($application->admission_letter_sent_at)->format('F d, Y') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1">
                                <strong>Academic Year:</strong> 
                                {{ $application->academic_year }}
                            </p>
                            <p class="mb-1">
                                <strong>Intake:</strong> 
                                {{ $application->intake }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Recipient Address -->
                    <div class="mb-4">
                        <p class="mb-1"><strong>To:</strong></p>
                        <p class="mb-0">{{ $personal->first_name }} {{ $personal->middle_name ?? '' }} {{ $personal->last_name }}</p>
                        @if($contact)
                        <p class="mb-0">{{ $contact->region ?? '' }}, {{ $contact->district ?? '' }}</p>
                        <p class="mb-0">{{ $contact->email ?? '' }}</p>
                        <p class="mb-0">{{ $contact->phone ?? '' }}</p>
                        @endif
                    </div>
                    
                    <!-- Letter Content -->
                    <div class="letter-content">
                        <h5 class="text-success mb-3">ADMISSION LETTER</h5>
                        
                        <p>Dear <strong>{{ $personal->first_name }} {{ $personal->last_name }}</strong>,</p>
                        
                        <p class="mb-3">
                            We are pleased to inform you that your application for admission to 
                            <strong>{{ config('app.name', 'College Name') }}</strong> has been successful. 
                            You have been admitted to the following program:
                        </p>
                        
                        <!-- Program Details -->
                        <div class="alert alert-success">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Program:</strong> 
                                        {{ $application->program_name }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Code:</strong> 
                                        {{ $application->program_code }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Entry Level:</strong> 
                                        {{ $application->entry_level }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Study Mode:</strong> 
                                        {{ $application->study_mode ?? 'Regular' }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Duration:</strong> 
                                        {{ $application->program_duration ?? '2 Years' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="mb-3">
                            Your admission is for the <strong>{{ $application->academic_year }}</strong> 
                            academic year, <strong>{{ $application->intake }}</strong> intake.
                        </p>
                        
                        <!-- Instructions -->
                        <h6 class="mt-4 mb-2 text-dark">NEXT STEPS:</h6>
                        <ol class="mb-3">
                            <li>
                                <strong>Confirmation of Admission:</strong> 
                                To confirm your admission, you must pay a non-refundable 
                                commitment fee of <strong>TZS 50,000/=</strong> within 
                                14 days from the date of this letter.
                            </li>
                            <li>
                                <strong>Reporting Date:</strong> 
                                You are expected to report to the college on 
                                <strong>March 15, {{ date('Y') }}</strong> for orientation.
                            </li>
                            <li>
                                <strong>Required Documents:</strong> 
                                Bring original certificates, birth certificate, 
                                passport photos, and this admission letter.
                            </li>
                            <li>
                                <strong>Tuition Fees:</strong> 
                                Full tuition fee payment details will be provided 
                                during registration.
                            </li>
                        </ol>
                        
                        <p class="mb-3">
                            Failure to confirm your admission by paying the commitment fee 
                            within the stipulated time will result in automatic cancellation 
                            of this offer.
                        </p>
                        
                        <p class="mb-3">
                            Congratulations on your admission! We look forward to welcoming 
                            you to our college community.
                        </p>
                        
                        <p class="mb-0">Yours sincerely,</p>
                        
                        <!-- Signature -->
                        <div class="mt-5">
                            <p class="mb-0"><strong>John Doe</strong></p>
                            <p class="text-muted mb-0">Admission Officer</p>
                            <p class="text-muted">{{ config('app.name', 'College Name') }}</p>
                        </div>
                    </div>
                    
                    <!-- Important Notes -->
                    <div class="alert alert-info mt-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-circle me-2"></i> Important Notes
                        </h6>
                        <ul class="mb-0">
                            <li>This letter is only valid for the specified academic year and intake</li>
                            <li>Keep this letter safely as you will need it during registration</li>
                            <li>For any inquiries, contact admission@college.ac.tz</li>
                            <li>Download and print this letter for your records</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Letter generated on: 
                                {{ \Carbon\Carbon::parse($application->admission_letter_sent_at)->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0 small text-muted">
                                Status: 
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Official
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.letter-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

@media print {
    .card-header, .card-footer, .btn, .alert-info {
        display: none !important;
    }
    
    .card {
        border: none !important;
    }
    
    .letter-content {
        font-size: 12pt;
    }
}
</style>
@endpush
@endsection