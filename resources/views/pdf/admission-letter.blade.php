<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter - {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
        }
        .college-name {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .address {
            color: #666;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .reference {
            margin-bottom: 30px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
        }
        .recipient {
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 40px;
        }
        .program-box {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .signature {
            margin-top: 60px;
        }
        .signature-line {
            width: 300px;
            border-top: 1px solid #333;
            margin-top: 40px;
        }
        .important-notes {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 30px;
            font-size: 12px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="college-name">{{ config('app.name', 'COLLEGE NAME') }}</div>
        <div class="address">
            P.O. Box 123, City, Country | Phone: +255 XXX XXX XXX | Email: info@college.ac.tz<br>
            Website: www.college.ac.tz
        </div>
        <div style="font-size: 18px; font-weight: bold; color: #28a745;">
            OFFICIAL ADMISSION LETTER
        </div>
    </div>
    
    <!-- Reference -->
    <div class="reference">
        <table width="100%">
            <tr>
                <td width="50%">
                    <strong>Ref. No:</strong> {{ $application->application_number }}<br>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($application->admission_letter_sent_at ?? now())->format('F d, Y') }}
                </td>
                <td width="50%" style="text-align: right;">
                    <strong>Academic Year:</strong> {{ $application->academic_year_name }}<br>
                    <strong>Intake:</strong> {{ $application->intake }}
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Recipient -->
    <div class="recipient">
        <strong>TO:</strong><br>
        {{ $personal->first_name }} {{ $personal->middle_name ?? '' }} {{ $personal->last_name }}<br>
        @if($contact)
        {{ $contact->region ?? '' }}, {{ $contact->district ?? '' }}<br>
        Email: {{ $contact->email ?? '' }}<br>
        Phone: {{ $contact->phone ?? '' }}
        @endif
    </div>
    
    <!-- Content -->
    <div class="content">
        <p><strong>SUBJECT: ADMISSION TO {{ strtoupper(config('app.name', 'COLLEGE')) }}</strong></p>
        
        <p>Dear <strong>{{ $personal->first_name }} {{ $personal->last_name }}</strong>,</p>
        
        <p>
            We are pleased to inform you that your application for admission to 
            <strong>{{ config('app.name', 'College Name') }}</strong> has been successful. 
            After reviewing your application, we are delighted to offer you admission 
            to the following program of study:
        </p>
        
        <!-- Program Details -->
        <div class="program-box">
            <table width="100%">
                <tr>
                    <td width="50%">
                        <strong>Program:</strong> {{ $program->name ?? 'N/A' }}<br>
                        <strong>Program Code:</strong> {{ $program->code ?? 'N/A' }}<br>
                        <strong>NTA Level:</strong> {{ $program->nta_level ?? '4' }}
                    </td>
                    <td width="50%">
                        <strong>Entry Level:</strong> {{ $application->entry_level }}<br>
                        <strong>Study Mode:</strong> {{ $application->study_mode ?? 'Regular' }}<br>
                        <strong>Duration:</strong> {{ $program->duration ?? '2 Years' }}
                    </td>
                </tr>
            </table>
        </div>
        
        <p>
            Your admission is for the <strong>{{ $application->academic_year_name }}</strong> 
            academic year, <strong>{{ $application->intake }}</strong> intake. Classes are 
            scheduled to commence on 
            <strong>{{ \Carbon\Carbon::parse($application->start_date ?? now()->addDays(30))->format('F d, Y') }}</strong>.
        </p>
        
        <h4 style="color: #28a745; margin-top: 25px;">IMPORTANT REQUIREMENTS:</h4>
        
        <ol>
            <li>
                <strong>Confirmation Fee:</strong> 
                A non-refundable commitment fee of <strong>TZS 50,000/=</strong> must be paid 
                within <strong>14 days</strong> from the date of this letter to secure your place.
            </li>
            <li>
                <strong>Reporting Date:</strong> 
                Report to the college on 
                <strong>{{ \Carbon\Carbon::parse($application->start_date ?? now()->addDays(30))->format('F d, Y') }}</strong> 
                for orientation and registration.
            </li>
            <li>
                <strong>Documents Required:</strong>
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
                Annual tuition fee: <strong>TZS {{ number_format($program->fees ?? 1500000) }}/=</strong>. 
                Payment plans are available at the accounts office.
            </li>
        </ol>
        
        <p>
            <strong>Note:</strong> Failure to pay the commitment fee within the stipulated 
            period will result in automatic cancellation of this admission offer.
        </p>
        
        <p>
            Congratulations on your achievement! We welcome you to our academic community 
            and look forward to supporting your educational journey.
        </p>
        
        <p>Sincerely,</p>
    </div>
    
    <!-- Signature -->
    <div class="signature">
        <div class="signature-line"></div>
        <p><strong>John Doe</strong><br>
        Admission Officer<br>
        {{ config('app.name', 'College Name') }}</p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Important:</strong> This is an official document. Please keep it safely.<br>
            <strong>For inquiries:</strong> admission@college.ac.tz | +255 XXX XXX XXX<br>
            <strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }} | 
            <strong>Reference:</strong> {{ $application->application_number }}
        </p>
    </div>
</body>
</html>