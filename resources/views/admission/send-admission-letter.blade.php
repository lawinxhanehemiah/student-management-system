{{-- resources/views/admission/send-admission-letter.blade.php --}}
@extends('layouts.admission')

@section('title', 'Send Admission Letter')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">
                    <i class="fas fa-envelope me-2"></i> Send Admission Letter
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.approved') }}">Approved Applications</a></li>
                        <li class="breadcrumb-item active">Send Admission Letter</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.approved') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Approved
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Applicant Info -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> Applicant Information
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Applicant Avatar -->
                    <div class="text-center mb-4">
                        <div class="avatar-xl bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <span class="display-6">
                                {{ strtoupper(substr($applicant->first_name ?? 'A', 0, 1)) }}
                            </span>
                        </div>
                        <h4 class="mb-1">{{ $applicant->first_name ?? '' }} {{ $applicant->last_name ?? '' }}</h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-hashtag me-1"></i> {{ $application->first_name ?? 'N/A' }} {{ $application->last_name ?? '' }}
                        </p>
                    </div>

                    <!-- Details -->
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">
                                <i class="fas fa-id-card me-2"></i> National ID
                            </span>
                            <span class="fw-medium">{{ $applicant->national_id ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">
                                <i class="fas fa-phone me-2"></i> Phone
                            </span>
                            <span class="fw-medium">{{ $contact->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">
                                <i class="fas fa-envelope me-2"></i> Email
                            </span>
                            <span class="fw-medium">{{ $contact->email ?? 'N/A' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i> Location
                            </span>
                            <span class="fw-medium">{{ $contact->region ?? '' }}, {{ $contact->district ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Details -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i> Program Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h6 class="alert-heading mb-2">{{ $program->name ?? 'Program Name' }}</h6>
                        <p class="mb-1">
                            <strong>Code:</strong> {{ $program->code ?? 'N/A' }}
                        </p>
                        <p class="mb-1">
                            <strong>Entry Level:</strong> {{ $application->entry_level }}
                        </p>
                        <p class="mb-1">
                            <strong>Study Mode:</strong> {{ $application->study_mode ?? 'Regular' }}
                        </p>
                        <p class="mb-0">
                            <strong>Duration:</strong> {{ $program->duration ?? '2 Years' }}
                        </p>
                    </div>
                    
                    <div class="mt-3">
                        <p class="mb-1">
                            <strong>Academic Year:</strong> {{ $academicYear->name ?? date('Y') }}
                        </p>
                        <p class="mb-1">
                            <strong>Intake:</strong> {{ $application->intake }}
                        </p>
                        <p class="mb-0">
                            <strong>Approved On:</strong> 
                            {{ \Carbon\Carbon::parse($application->approved_at)->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Send Letter Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i> Send Admission Letter
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Letter Preview -->
                    <div class="mb-4">
                        <h6 class="mb-3">Letter Preview</h6>
                        <div class="border rounded p-4 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center mb-4">
    <div class="institution-header">
        <!-- Try multiple image locations -->
        @php
            $imagePaths = [
                'header' => 'assets/images/header.jpg',
                'logo' => 'assets/images/logo.webp',
                'default' => 'assets/images/default.png',
            ];
            
            $imageFound = false;
            $imageSrc = null;
            
            foreach ($imagePaths as $type => $path) {
                if (file_exists(public_path($path))) {
                    $imageFound = true;
                    $imageSrc = asset($path);
                    break;
                }
            }
        @endphp
        
        @if($imageFound)
            <img src="{{ $imageSrc }}" 
                 class="img-fluid institution-logo"
                 alt="{{ setting('institution_name', 'Saint Maximillian Kolbe Health Sciences Institute') }}"
                 style="max-height: 120px; width: auto; margin-bottom: 15px;">
        @else
            <!-- Fallback with nice styling -->
            <div class="text-center py-3 mb-3" style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%); border-radius: 8px;">
                <h4 class="text-white mb-1" style="font-weight: bold;">{{ setting('institution_name', 'SAINT MAXIMILLIAN KOLBE') }}</h4>
                <p class="text-white mb-0" style="opacity: 0.9;">HEALTH SCIENCES INSTITUTE</p>
            </div>
        @endif
        
        <!-- Letter title -->
        <div class="mt-2">
            <h3 class="text-primary" style="font-weight: bold; border-bottom: 2px solid #3498db; padding-bottom: 5px; display: inline-block;">
                {{ setting('admission_letter_title', 'OFFICIAL ADMISSION LETTER') }}
            </h3>
        </div>
    </div>
</div>
                            
                            <p><strong>Date:</strong> {{ date('F d, Y') }}</p>
                            <p><strong>To:</strong> {{ $applicant->first_name }} {{ $applicant->last_name }}</p>
                            
                            <p class="mt-3">Dear <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>,</p>
                            
                            <p>We are pleased to inform you that your application for admission to 
                               <strong>{{ setting('institution_name', 'College Name') }}</strong> has been successful. 
                               You have been admitted to the following program:</p>
                            
                            <div class="alert alert-success p-3">
                                <strong>Program:</strong> {{ $program->name ?? 'N/A' }}<br>
                                <strong>Program Code:</strong> {{ $program->code ?? 'N/A' }}<br>
                                <strong>Academic Year:</strong> {{ $academicYear->name ?? date('Y') }}<br>
                                <strong>Intake:</strong> {{ $application->intake }}
                            </div>
                            
                            <p>Your admission letter will include detailed instructions on 
                               fees payment, reporting date, and required documents.</p>
                        </div>
                        
                        <div class="mt-2 text-end">
                            <a href="{{ route('admission.applicants.preview-letter', $application->id) }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i> Preview Full Letter
                            </a>
                        </div>
                    </div>

                    <!-- Send Options Form -->
                    <form id="sendLetterForm" action="{{ route('admission.applicants.send-letter', $application->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <h6 class="mb-3">Send Options</h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="send_email" id="sendEmail" value="1" checked>
                                        <label class="form-check-label" for="sendEmail">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            Send email notification
                                        </label>
                                        <div class="form-text">
                                            Email will be sent to: <strong>{{ $contact->email ?? 'No email found' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="send_sms" id="sendSms" value="1">
                                        <label class="form-check-label" for="sendSms">
                                            <i class="fas fa-sms text-info me-2"></i>
                                            Send SMS notification
                                        </label>
                                        <div class="form-text">
                                            SMS will be sent to: <strong>{{ $contact->phone ?? 'No phone found' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deliveryDate" class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Delivery Date
                                </label>
                                <input type="date" class="form-control" id="deliveryDate" name="delivery_date" 
                                       value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                                <div class="form-text">Select when the letter should be delivered</div>
                            </div>

                            <div class="mb-3">
                                <label for="additionalNotes" class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes (Optional)
                                </label>
                                <textarea class="form-control" id="additionalNotes" name="additional_notes" 
                                          rows="3" placeholder="Add any special instructions or notes..."></textarea>
                            </div>
                        </div>

                        <!-- Letter Customization -->
                        <div class="mb-4">
                            <h6 class="mb-3">Letter Customization</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Letter Template</label>
                                    @php
    $templates = explode(',', setting('allowed_letter_templates', 'standard'));
@endphp

<select class="form-select" name="template">
    @foreach($templates as $tpl)
        <option value="{{ $tpl }}"
            {{ setting('default_letter_template') === $tpl ? 'selected' : '' }}>
            {{ ucfirst($tpl) }} Admission Letter
        </option>
    @endforeach
</select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Letter Format</label>
                                    <select class="form-select" name="format">
                                        <option value="pdf">PDF Document</option>
                                        <option value="html">HTML Email</option>
                                        <option value="both">Both PDF and HTML</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox"
       name="include_fee_structure"
       value="1"
       {{ setting('include_fee_structure_default') ? 'checked' : '' }}>

                                <label class="form-check-label" for="includeFeeStructure">
                                    Include fee structure
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="include_registration_guide" id="includeRegistrationGuide" value="1" checked>
                                <label class="form-check-label" for="includeRegistrationGuide">
                                    Include registration guide
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center border-top pt-4">
                            <div>
                                <button type="button" onclick="testEmail()" class="btn btn-outline-info">
                                    <i class="fas fa-vial me-2"></i> Test Email
                                </button>
                                <button type="button" onclick="previewFinalLetter()" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-eye me-2"></i> Preview Final
                                </button>
                            </div>
                            <div>
                                <button type="button" onclick="window.location.href='{{ route('admission.applicants.approved') }}'" 
                                        class="btn btn-outline-secondary me-2">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i> Send Admission Letter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Recent Activity -->
                <div class="card-footer bg-light">
                    <h6 class="mb-3">
                        <i class="fas fa-history me-2"></i> Recent Activity
                    </h6>
                    @php
                        $logs = DB::table('application_logs')
                            ->where('application_id', $application->id)
                            ->whereIn('action', ['approved', 'letter_sent', 'letter_resent'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($logs->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($logs as $log)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="fas fa-{{ 
                                        $log->action == 'approved' ? 'check-circle text-success' : 
                                        ($log->action == 'letter_sent' ? 'envelope text-primary' : 'redo text-info')
                                    }} me-2"></i>
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                </div>
                            </div>
                            @if($log->description)
                            <div class="small text-muted mt-1">
                                {{ $log->description }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No recent activity found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission
    $('#sendLetterForm').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Send Admission Letter?',
            html: `<p>You are about to send the admission letter to:</p>
                   <div class="alert alert-info text-start">
                       <strong>${'{{ $applicant->first_name }} {{ $applicant->last_name }}'}</strong><br>
                       Email: ${'{{ $contact->email ?? "N/A" }}'}<br>
                       Phone: ${'{{ $contact->phone ?? "N/A" }}'}
                   </div>
                   <p class="text-muted">This action cannot be undone.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send Now',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Sending Letter...',
                    text: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            title: 'Letter Sent Successfully!',
                            html: `<p>The admission letter has been sent to the applicant.</p>
                                   <div class="alert alert-success text-start mt-3">
                                       <strong>Details:</strong><br>
                                       • Email sent: ${$('#sendEmail').is(':checked') ? 'Yes' : 'No'}<br>
                                       • SMS sent: ${$('#sendSms').is(':checked') ? 'Yes' : 'No'}<br>
                                       • Delivery date: ${$('#deliveryDate').val()}<br>
                                       • Time: ${new Date().toLocaleTimeString()}
                                   </div>`,
                            icon: 'success',
                            confirmButtonText: 'Return to Approved List'
                        }).then(() => {
                            window.location.href = '{{ route("admission.applicants.approved") }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to send letter',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});

function testEmail() {
    Swal.fire({
        title: 'Test Email Delivery',
        text: 'Send a test email to verify delivery?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Send Test',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admission.applicants.send-letter", $application->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    test_email: true,
                    send_email: true
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Test Email Sent!',
                        text: 'A test email has been sent to your address.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function previewFinalLetter() {
    window.open('{{ route("admission.applicants.preview-letter", $application->id) }}', '_blank');
}
</script>

<style>
.avatar-xl {
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.list-group-item {
    border: none;
    padding-left: 0;
    padding-right: 0;
}

.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush
@endsection