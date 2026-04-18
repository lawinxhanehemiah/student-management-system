@extends('layouts.admission')

@section('title', 'Preview Offer Letter')

@section('styles')
<style>
    .letter-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    .letter-header {
        background: #dc3545;
        color: white;
        padding: 30px;
        text-align: center;
    }
    .letter-header h1 {
        margin: 0;
        font-size: 28px;
    }
    .letter-header h3 {
        margin: 10px 0 0;
        font-size: 18px;
    }
    .letter-logo {
        max-width: 120px;
        margin-bottom: 15px;
    }
    .letter-content {
        padding: 40px;
        background: white;
    }
    .letter-footer {
        background: #f5f5f5;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        border-top: 1px solid #ddd;
    }
    .document-list {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 5px;
        margin: 20px 0;
        border-left: 4px solid #dc3545;
    }
    .document-list ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    .document-list li {
        margin: 8px 0;
    }
    .fee-structure-box {
        background: #e8f4f8;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
        margin: 20px 0;
    }
    .contact-info-box {
        background: #f0f0f0;
        padding: 15px;
        border-radius: 5px;
        margin: 20px 0;
    }
    .badge-offer {
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }
    .confirmation-code {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        font-family: monospace;
        font-size: 18px;
        text-align: center;
        letter-spacing: 2px;
    }
    hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 20px 0;
    }
    .btn-download {
        background: #dc3545;
        border: none;
    }
    .btn-download:hover {
        background: #c82333;
    }
    @media print {
        .no-print {
            display: none;
        }
        .letter-container {
            box-shadow: none;
            margin: 0;
            padding: 0;
        }
        .letter-content {
            padding: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <!-- Action Buttons - No Print -->
            <div class="mb-3 no-print">
                <div class="btn-group">
                    <a href="{{ route('admission.offers.letter.generate', $application->id) }}" class="btn btn-danger btn-download">
                        <i class="feather-download"></i> Download PDF
                    </a>
                    <a href="{{ route('admission.offers.letters') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left"></i> Back to Letters
                    </a>
                    <button onclick="window.print();" class="btn btn-info">
                        <i class="feather-printer"></i> Print
                    </button>
                </div>
            </div>

            <!-- Letter Container -->
            <div class="letter-container">
                <div class="letter-header">
                    @if(file_exists(public_path('assets/images/header.png')))
                        <img src="{{ asset('assets/images/header.png') }}" alt="Logo" class="letter-logo">
                    @endif
                    <h1>ST. MAXIMILLIAN COLLEGE</h1>
                    <h3>ADMISSION OFFER LETTER</h3>
                </div>
                
                <div class="letter-content">
                    <!-- Date -->
                    <div style="text-align: right; margin-bottom: 20px;">
                        <strong>Date:</strong> {{ now()->format('d F, Y') }}
                    </div>
                    
                    <!-- Salutation -->
                    <p>Dear <strong>{{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}</strong>,</p>
                    
                    <p>Congratulations! <span class="badge-offer">OFFERED</span></p>
                    
                    <p>We are pleased to inform you that you have been selected for <strong>{{ $application->programme_name }}</strong> programme at St. Maximillian College for the <strong>{{ $application->intake ?? 'March' }}</strong> intake.</p>
                    
                    <p><strong>Your Application Number:</strong> {{ $application->application_number }}</p>
                    <p><strong>Confirmation Code:</strong> <span class="confirmation-code">{{ $confirmation_code ?? $application->application_number }}</span></p>
                    
                    <hr>
                    
                    <!-- Documents Required Section -->
                    <h4 style="color: #dc3545;">📋 Documents Required for Registration:</h4>
                    <div class="document-list">
                        <ul>
                            <li><strong>📸 Passport Size Photos:</strong> 3 copies (recent, with white background)</li>
                            <li><strong>🎓 Academic Certificates:</strong> Original certificates and copies</li>
                            <li><strong>📄 Birth Certificate:</strong> Copy of birth certificate or affidavit</li>
                            <li><strong>🏥 Medical Examination:</strong> Recent medical report from recognized hospital</li>
                            <li><strong>🆔 National ID:</strong> Copy of National ID or Passport (if available)</li>
                        </ul>
                    </div>
                    
                    <!-- Fee Structure Information -->
                    <div class="fee-structure-box">
                        <h4 style="margin: 0 0 10px 0;">💰 Fee Structure</h4>
                        <p>The complete fee structure including tuition fees, registration fees, examination fees, and other charges can be obtained from:</p>
                        <ul style="text-align: left;">
                            <li>📧 Email request to: stmaximilliancollege@info.ac.tz</li>
                            <li>📞 Call us: +255 623 053 846</li>
                            <li>🌐 Visit our website: www.stmaxcollege.ac.tz</li>
                            <li>🏢 Admission Office at the college</li>
                        </ul>
                        <p class="text-muted"><small>Fee structure will be provided upon request or during registration.</small></p>
                    </div>
                    
                    <!-- Important Instructions -->
                    <h4 style="color: #dc3545;">📌 Important Instructions:</h4>
                    <ol>
                        <li><strong>Accept the offer:</strong> Log into your admission portal and accept the offer within 14 days</li>
                        <li><strong>Submit Documents:</strong> Bring all required documents to the Admission Office</li>
                        <li><strong>Complete Registration:</strong> Complete registration within 30 days from offer date</li>
                        <li><strong>Make Payment:</strong> Pay fees as per the fee structure</li>
                        <li><strong>Report:</strong> Report to the college on the specified date with all originals</li>
                    </ol>
                    
                    <!-- Contact Information -->
                    <div class="contact-info-box">
                        <h4 style="margin-top: 0;">📞 Contact Information:</h4>
                        <p><strong>Admission Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM</p>
                        <p><strong>Phone:</strong> +255 623 053 846</p>
                        <p><strong>Email:</strong> stmaximilliancollege@info.ac.tz</p>
                        <p><strong>Website:</strong> www.stmaxcollege.ac.tz</p>
                    </div>
                    
                    <p>For any inquiries or assistance, please don't hesitate to contact us. Our team is ready to help you with the registration process.</p>
                    
                    <p>We look forward to welcoming you to St. Maximillian College and wish you success in your academic journey!</p>
                    
                    <div style="margin-top: 40px;">
                        <p>Yours sincerely,</p>
                        <p style="margin-top: 30px;">
                            <strong>Admission Office</strong><br>
                            St. Maximillian College
                        </p>
                    </div>
                </div>
                
                <div class="letter-footer">
                    <p>St. Maximillian College Admission System</p>
                    <p>© {{ date('Y') }} St. Maximillian College. All rights reserved.</p>
                    <p><small>This is an official offer letter. Please keep it for your records.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection