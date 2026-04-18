<!DOCTYPE html>
<html>
<head>
    <title>Admission Offer Letter - {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', serif;
            margin: 50px;
            line-height: 1.6;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
        }
        .letter-date {
            text-align: right;
            margin-bottom: 30px;
        }
        .recipient {
            margin-bottom: 30px;
        }
        .subject {
            font-weight: bold;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .signature {
            margin-top: 50px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9pt;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .program-details {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0066cc;
        }
        .program-details h4 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ST. MAXIMILLIAN COLLEGE</h1>
        <p>P.O. Box 12345, Dar es Salaam, Tanzania</p>
        <p>Tel: +255 XXX XXX XXX | Email: info@stmaximillian.ac.tz</p>
        <h2>ADMISSION OFFER LETTER</h2>
    </div>

    <div class="letter-date">
        <p>Date: {{ $date->format('d F, Y') }}</p>
    </div>

    <div class="recipient">
        <p>
            <strong>{{ $application->first_name }} {{ $application->middle_name }} {{ $application->last_name }}</strong><br>
            @if($application->street_address){{ $application->street_address }}<br>@endif
            @if($application->region){{ $application->region }}, @endif
            @if($application->district){{ $application->district }}<br>@endif
            @if($application->phone_number)Tel: {{ $application->phone_number }}<br>@endif
            @if($application->personal_email)Email: {{ $application->personal_email }}@endif
        </p>
    </div>

    <div class="subject">
        <p><strong>RE: ADMISSION OFFER FOR ACADEMIC YEAR {{ $application->academic_year_name ?? date('Y') . '/' . (date('Y')+1) }}</strong></p>
    </div>

    <div class="content">
        <p>Dear {{ $application->first_name }} {{ $application->last_name }},</p>

        <p>We are pleased to inform you that your application for admission to <strong>{{ $application->programme_name }}</strong> (Code: {{ $application->programme_code }}) at St. Maximillian College has been <strong>APPROVED</strong>.</p>

        <div class="program-details">
            <h4>Programme Details:</h4>
            <p><strong>Programme:</strong> {{ $application->programme_name }}<br>
            <strong>Duration:</strong> {{ $application->duration ?? '3 years' }}<br>
            <strong>Study Mode:</strong> {{ ucfirst(str_replace('_', ' ', $application->study_mode ?? 'Full Time')) }}<br>
            <strong>Intake:</strong> {{ $application->intake ?? 'March' }} {{ date('Y') }}<br>
            <strong>Academic Year:</strong> {{ $application->academic_year_name ?? date('Y') . '/' . (date('Y')+1) }}</p>
        </div>

        <p><strong>Your Admission Requirements:</strong></p>
        <ul>
            <li>Complete the online registration process within 14 days from the date of this letter.</li>
            <li>Pay the required tuition and other fees as per the fee structure.</li>
            <li>Submit certified copies of your academic certificates and birth certificate.</li>
            <li>Provide two passport-size photographs.</li>
            <li>Complete the medical examination form.</li>
        </ul>

        <p><strong>Reporting Date:</strong> {{ $application->intake == 'March' ? '15th March ' . date('Y') : '15th September ' . date('Y') }}</p>

        <p>Please confirm your acceptance of this offer by clicking the acceptance link in your applicant portal or by contacting the Admission Office within 14 days.</p>

        <p>For any inquiries, please contact the Admission Office at +255 XXX XXX XXX or email admission@stmaximillian.ac.tz.</p>

        <p>Congratulations on your admission! We look forward to welcoming you to St. Maximillian College.</p>
    </div>

    <div class="signature">
        <p>Yours sincerely,</p>
        <br><br>
        <p><strong>Admission Officer</strong><br>
        St. Maximillian College</p>
    </div>

    <div class="footer">
        <p>This is a system-generated letter. No signature is required.</p>
        <p>Application Number: {{ $application->application_number }}</p>
    </div>
</body>
</html>