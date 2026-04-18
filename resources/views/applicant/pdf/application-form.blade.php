<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Form - {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .institution-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
            margin-top: 10px;
        }
        
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            font-weight: bold;
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .signature-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .print-date {
            float: right;
            font-size: 10px;
            color: #666;
        }
        
        .watermark {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 10px;
            color: rgba(0,0,0,0.2);
        }
        
        /* For PDF header */
        .pdf-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .pdf-header-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .pdf-header-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php
    // Handle image for PDF
    $headerImagePath = public_path('assets/images/header.jpg');
    $imageData = null;
    
    if (file_exists($headerImagePath)) {
        $imageData = base64_encode(file_get_contents($headerImagePath));
    }
    ?>
    
    <!-- Print Date -->
    <div class="print-date">
        Printed: {{ now()->format('d/m/Y H:i') }}
    </div>
    
    <!-- Header for PDF -->
    <div class="header">
        @if($imageData)
            <div class="institution-name">
                <img src="data:image/jpeg;base64,{{ $imageData }}" 
                     style="width: 100%; max-width: 800px; height: auto; max-height: 150px;"
                     alt="St. Maximillian Kolbe Health Sciences Institute">
            </div>
        @else
            <!-- Fallback header for PDF -->
            <div class="pdf-header">
                <div class="pdf-header-title">ST. MAXIMILLIAN KOLBE</div>
                <div class="pdf-header-subtitle">HEALTH SCIENCES INSTITUTE</div>
            </div>
        @endif
        
        <div class="document-title">APPLICATION FORM</div>
        <div style="font-size: 12px; color: #555; margin-top: 5px;">
            
        </div>
    </div>
    
    <!-- The rest of your HTML content remains EXACTLY the same -->
    <!-- Application Details -->
    <div class="section">
        <div class="section-title">APPLICATION DETAILS</div>
        <table>
            <tr>
                <td width="30%"><strong>Application Number:</strong></td>
                <td>{{ $application->application_number }}</td>
                <td width="30%"><strong>Application Status:</strong></td>
                <td>{{ strtoupper($application->status) }}</td>
            </tr>
            <tr>
                <td><strong>Academic Year:</strong></td>
                <td>{{ $academicYear->name ?? 'N/A' }}</td>
                <td><strong>Intake:</strong></td>
                <td>{{ $application->intake }}</td>
            </tr>
            <tr>
                <td><strong>Entry Level:</strong></td>
                <td>{{ $application->entry_level }}</td>
                <td><strong>Study Mode:</strong></td>
                <td>{{ $application->study_mode ?? 'Full Time' }}</td>
            </tr>
            <tr>
                <td><strong>Submitted Date:</strong></td>
                <td>
                    @if($application->submitted_at)
                        {{ \Carbon\Carbon::parse($application->submitted_at)->format('d M, Y H:i') }}
                    @else
                        Not submitted
                    @endif
                </td>
                <td><strong>Approved Date:</strong></td>
                <td>
                    @if($application->approved_at)
                        {{ \Carbon\Carbon::parse($application->approved_at)->format('d M, Y H:i') }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">PERSONAL INFORMATION</div>
        <table>
            <tr>
                <td width="25%"><strong>Full Name:</strong></td>
                <td width="25%">{{ $personal->first_name ?? '' }}</td>
                <td width="25%"><strong>Middle Name:</strong></td>
                <td width="25%">{{ $personal->middle_name ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Last Name:</strong></td>
                <td>{{ $personal->last_name ?? '' }}</td>
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
                <td><strong>Nationality:</strong></td>
                <td>{{ $personal->nationality ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Marital Status:</strong></td>
                <td>{{ ucfirst($personal->marital_status ?? 'N/A') }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
    
    <!-- Contact Information -->
    <div class="section">
        <div class="section-title">CONTACT INFORMATION</div>
        <table>
            <tr>
                <td width="25%"><strong>Phone Number:</strong></td>
                <td width="25%">{{ $contact->phone ?? 'N/A' }}</td>
                <td width="25%"><strong>Email Address:</strong></td>
                <td width="25%">{{ $contact->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Region:</strong></td>
                <td>{{ $contact->region ?? 'N/A' }}</td>
                <td><strong>District:</strong></td>
                <td>{{ $contact->district ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Ward:</strong></td>
                <td>{{ $contact->ward ?? 'N/A' }}</td>
                <td><strong>Street/Village:</strong></td>
                <td>{{ $contact->street ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Next of Kin -->
    <div class="section">
        <div class="section-title">NEXT OF KIN INFORMATION</div>
        <table>
            <tr>
                <td width="25%"><strong>Full Name:</strong></td>
                <td width="75%">{{ $kin->guardian_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Phone Number:</strong></td>
                <td>{{ $kin->guardian_phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Relationship:</strong></td>
                <td>{{ $kin->relationship ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td>{{ $kin->guardian_address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Academic Information -->
    <div class="section">
        <div class="section-title">ACADEMIC INFORMATION</div>
        
        <!-- CSEE Details -->
        <table>
            <tr>
                <th colspan="4" style="background-color: #e9ecef;">CSEE (FORM IV) RESULTS</th>
            </tr>
            <tr>
                <td width="25%"><strong>Index Number:</strong></td>
                <td width="25%">{{ $academic->csee_index_number ?? 'N/A' }}</td>
                <td width="25%"><strong>School Name:</strong></td>
                <td width="25%">{{ $academic->csee_school ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Year of Completion:</strong></td>
                <td>{{ $academic->csee_year ?? 'N/A' }}</td>
                <td><strong>Division:</strong></td>
                <td>{{ $academic->csee_division ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Total Points:</strong></td>
                <td>{{ $academic->csee_points ?? 'N/A' }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
        
        <!-- ACSEE Details if available -->
        @if($academic->acsee_index_number)
        <table>
            <tr>
                <th colspan="4" style="background-color: #e9ecef;">ACSEE (FORM VI) RESULTS</th>
            </tr>
            <tr>
                <td width="25%"><strong>Index Number:</strong></td>
                <td width="25%">{{ $academic->acsee_index_number ?? 'N/A' }}</td>
                <td width="25%"><strong>School Name:</strong></td>
                <td width="25%">{{ $academic->acsee_school ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Year of Completion:</strong></td>
                <td>{{ $academic->acsee_year ?? 'N/A' }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
        @endif
        
        <!-- Subjects Table -->
        @if($subjects->count() > 0)
        <table>
            <tr>
                <th colspan="3" style="background-color: #e9ecef;">O-LEVEL SUBJECTS AND GRADES</th>
            </tr>
            <tr>
                <th width="5%">#</th>
                <th width="65%">SUBJECT NAME</th>
                <th width="30%">GRADE</th>
            </tr>
            @foreach($subjects as $index => $subject)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $subject->subject ?? $subject->subject_name ?? 'N/A' }}</td>
                <td>{{ $subject->grade ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>
    
    <!-- Program Choices -->
    <div class="section">
        <div class="section-title">PROGRAM CHOICES</div>
        <table>
            <tr>
                <th width="10%">CHOICE</th>
                <th width="20%">PROGRAM CODE</th>
                <th width="50%">PROGRAM NAME</th>
                <th width="20%">STUDY MODE</th>
            </tr>
            @if($firstProgram)
            <tr>
                <td><strong>1st</strong></td>
                <td>{{ $firstProgram->code ?? 'N/A' }}</td>
                <td>{{ $firstProgram->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($firstProgram->study_mode ?? 'Full Time') }}</td>
            </tr>
            @endif
            @if($secondProgram)
            <tr>
                <td><strong>2nd</strong></td>
                <td>{{ $secondProgram->code ?? 'N/A' }}</td>
                <td>{{ $secondProgram->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($secondProgram->study_mode ?? 'Full Time') }}</td>
            </tr>
            @endif
            @if($thirdProgram)
            <tr>
                <td><strong>3rd</strong></td>
                <td>{{ $thirdProgram->code ?? 'N/A' }}</td>
                <td>{{ $thirdProgram->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($thirdProgram->study_mode ?? 'Full Time') }}</td>
            </tr>
            @endif
        </table>
        
        <!-- Selected Program (if approved) -->
        @if($selectedProgramDetails)
        <table style="margin-top: 10px; border: 2px solid #28a745;">
            <tr>
                <th style="background-color: #d4edda; color: #155724;">
                    ✓ APPROVED PROGRAM
                </th>
            </tr>
            <tr>
                <td style="text-align: center; padding: 15px;">
                    <strong style="font-size: 16px;">
                        {{ $selectedProgramDetails->code }} - {{ $selectedProgramDetails->name }}
                    </strong>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <!-- Information Source -->
    @if($programChoice && $programChoice->information_source)
    <div class="section">
        <div class="section-title">ADDITIONAL INFORMATION</div>
        <table>
            <tr>
                <td width="30%"><strong>How did you hear about us?</strong></td>
                <td width="70%">{{ $programChoice->information_source }}</td>
            </tr>
        </table>
    </div>
    @endif
    
    <!-- Signature Section -->
    <div class="signature-box">
        <table>
            <tr>
                <td width="50%" style="border: none; padding: 20px 0;">
                    <strong>Applicant's Signature:</strong><br><br><br>
                    _________________________________<br>
                    Date: ___________________________
                </td>
                <td width="50%" style="border: none; padding: 20px 0;">
                    <strong>Admission Officer's Signature:</strong><br><br><br>
                    _________________________________<br>
                    Date: ___________________________
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
    <p>
        <strong>OFFICIAL DOCUMENT - KEEP FOR YOUR RECORDS</strong><br>
        This document is computer generated. No signature is required.<br>
        Generated by <strong style="color: #dc3545; font-weight: bold;">{{ setting('institution_name', 'College Name') }}</strong> Application System
    </p>
</div>
    
    <!-- Watermark -->
    <div class="watermark">
        {{ $application->application_number }} | {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>