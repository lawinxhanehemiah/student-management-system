{{-- resources/views/admission/preview-admission-letter.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Admission Letter - {{ $application->application_number ?? 'N/A' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .preview-container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .preview-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px 30px;
        }
        
        .letter-paper {
            padding: 40px;
            min-height: 800px;
            position: relative;
        }
        
        .letter-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
        }
        
        .college-logo {
            height: 80px;
            margin-bottom: 15px;
        }
        
        .letter-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .recipient-box {
            background: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #28a745;
            margin: 25px 0;
            border-radius: 5px;
        }
        
        .program-details {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }
        
        .footer-actions {
            background: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #dee2e6;
        }
        
        .stamp-area {
            position: absolute;
            right: 40px;
            bottom: 40px;
            text-align: center;
        }
        
        .stamp {
            border: 2px dashed #dc3545;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            transform: rotate(5deg);
        }
        
        .watermark {
            position: absolute;
            opacity: 0.05;
            font-size: 120px;
            font-weight: bold;
            color: #28a745;
            transform: rotate(-45deg);
            top: 30%;
            left: 10%;
            z-index: 0;
        }
        
        @media print {
            body {
                background: white !important;
            }
            
            .preview-container {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            
            .footer-actions,
            .preview-header {
                display: none !important;
            }
            
            .letter-paper {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <!-- Header -->
        <div class="preview-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-file-certificate me-2"></i> Preview Admission Letter
                    </h1>
                    <p class="mb-0">Application #: {{ $application->application_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-eye me-1"></i> Preview Mode
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Letter Content -->
        <div class="letter-paper">
            <!-- Watermark -->
            <div class="watermark">
                PREVIEW
            </div>
            
            <!-- Letter Header -->
            <div class="letter-header">
                
               <div class="container-fluid mb-3">
    <div class="row">
        <div class="col-12 text-center">
            <img 
                src="{{ asset('assets/images/header.jpg') }}" 
                alt="College Header"
                class="img-fluid w-100"
                style="max-height: 160px; object-fit: contain;"
            >
        </div>
    </div>
</div>

                <div class="mt-3">
                    <h4 class="text-dark">OFFICIAL ADMISSION LETTER</h4>
                </div>
            </div>
            
            <!-- Reference Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Reference No:</strong> 
                        <span class="text-success">{{ $application->application_number ?? 'N/A' }}</span>
                    </p>
                    <p class="mb-1">
                        <strong>Date:</strong> 
                        {{ date('F d, Y') }}
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1">
                        <strong>Academic Year:</strong> 
                        {{ $academicYear->name ?? date('Y') . '/' . (date('Y') + 1) }}
                    </p>
                    <p class="mb-1">
                        <strong>Intake:</strong> 
                        {{ $application->intake ?? 'September' }}
                    </p>
                </div>
            </div>
            
            <!-- Recipient -->
            <div class="recipient-box">
                <p class="mb-2"><strong>To:</strong></p>
                <h4 class="mb-1">{{ $personal->first_name ?? 'Applicant' }} {{ $personal->last_name ?? '' }}</h4>
                @if($contact)
                <p class="mb-1">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ $contact->region ?? '' }}, {{ $contact->district ?? '' }}
                </p>
                <p class="mb-1">
                    <i class="fas fa-phone me-2"></i>
                    {{ $contact->phone ?? 'N/A' }}
                </p>
                <p class="mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    {{ $contact->email ?? 'N/A' }}
                </p>
                @endif
            </div>
            
            <!-- Letter Body -->
            <div class="letter-content">
                <p>Dear <strong>{{ $personal->first_name ?? 'Applicant' }} {{ $personal->last_name ?? '' }}</strong>,</p>
                
                <p>
                    We are pleased to inform you that your application for admission to 
                    <strong>{{ config('app.name', 'ST. MAXIMILLIAN KOLBE UNIVERSITY') }}</strong> 
                    has been successful. After careful review of your application, we are delighted to 
                    offer you admission to the following program of study:
                </p>
                
                <!-- Program Details -->
                <div class="program-details">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Program Name:</strong><br>
                                {{ $program->name ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                <strong>Program Code:</strong><br>
                                {{ $program->code ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Entry Level:</strong><br>
                                {{ $application->entry_level ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                <strong>Study Mode:</strong><br>
                                {{ $application->study_mode ?? 'Regular' }}
                            </p>
                            <p class="mb-0">
                                <strong>Duration:</strong><br>
                                {{ $program->duration ?? '2 Years' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <p>
                    Your admission is for the <strong>{{ $academicYear->name ?? date('Y') . '/' . (date('Y') + 1) }}</strong> 
                    academic year, <strong>{{ $application->intake ?? 'September' }}</strong> intake.
                </p>
                
                <h5 class="mt-4 mb-3 text-success">Important Information:</h5>
                
                <ol>
                    <li class="mb-2">
                        <strong>Confirmation of Admission:</strong> 
                        To confirm your admission, you must pay a non-refundable commitment fee of 
                        <strong>TZS {{ number_format(setting('commitment_fee_amount'), 0) }}/=</strong> within {{ setting('commitment_fee_days') }}
                    </li>
                    <li class="mb-2">
                        <strong>Reporting Date:</strong> 
                        You are expected to report to the college on 
                        <strong>{{ \Carbon\Carbon::parse(setting('reporting_date'))->format('F d, Y') }}
                    </strong> for orientation and registration.
                    </li>
                    <li class="mb-2">
                        <strong>Required Documents:</strong> 
                        Bring the following documents during registration:
                        <ul>
                            <li>Original academic certificates and transcripts</li>
                            <li>Birth certificate (original and photocopy)</li>
                            <li>Four (4) recent passport-size photographs</li>
                            <li>Medical examination report</li>
                            <li>This admission letter (printed copy)</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Tuition Fees:</strong> 
                        Annual tuition fee for the program is 
                        <strong>TZS {{ number_format($program->fees ?? 1500000, 0) }}/=</strong>. 
                        Payment plans are available at the accounts office.
                    </li>
                </ol>
                
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Failure to pay the commitment fee within the stipulated 
                    period will result in automatic cancellation of this admission offer.
                </div>
                
                <p class="mt-4">
                    Congratulations on your achievement! We welcome you to our academic community 
                    and look forward to supporting your educational journey at 
                    {{ setting('institution_name', 'College Name') }}.
                </p>
                
                <p>Yours sincerely,</p>
                
                <!-- Signature Area -->
                <div class="mt-5">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Dr. John P. Mwakatobe</strong></p>
                            <p class="text-muted mb-0">Admission Officer</p>
                            <p class="text-muted">{{ config('app.name', 'ST. MAXIMILLIAN KOLBE UNIVERSITY') }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="stamp-area">
                                <div class="stamp">
                                    <i class="fas fa-stamp fa-3x text-danger mb-2"></i>
                                    <p class="mb-0 small">OFFICIAL</p>
                                    <p class="mb-0 small">ADMISSION</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Notes -->
            <div class="mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-md-6">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            This is an official document. Please keep it safely.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-clock me-1"></i>
                            Generated: {{ now()->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Actions -->
        <div class="footer-actions">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        This is a preview. Changes are not saved automatically.
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <a href="{{ route('admission.applicants.generate-letter', $application->id) }}" 
                       class="btn btn-success me-2" target="_blank">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('admission.applicants.send-letter-form', $application->id) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Send Letter
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Print shortcut
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
        
        // Auto-refresh after 5 minutes
        setTimeout(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>