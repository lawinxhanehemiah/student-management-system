<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Offer Letter - {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .content {
            margin: 30px 0;
        }
        .offer-title {
            background: #ecf0f1;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .details-table td:first-child {
            font-weight: bold;
            width: 35%;
        }
        .signature {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ST. MAXIMILLIANCOLBE COLLEGE</h1>
            <p>Education for Better Management</p>
            <h2>OFFICIAL OFFER OF ADMISSION</h2>
        </div>

        <div style="text-align: right; margin-bottom: 20px;">
            <strong>Date:</strong> {{ $date->format('d F, Y') }}
        </div>

        <div class="content">
            <p><strong>Dear {{ $application->first_name ?? '' }} {{ $application->last_name ?? '' }},</strong></p>

            <div class="offer-title">
                RE: OFFER OF ADMISSION
            </div>

            <p>We are pleased to inform you that you have been offered admission to the <strong>{{ $application->programme_name ?? 'N/A' }}</strong> programme at St. Maximillian College.</p>

            <table class="details-table">
                <tr>
                    <td>Programme:</td>
                    <td><strong>{{ $application->programme_name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td>Programme Code:</td>
                    <td>{{ $application->programme_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Study Mode:</td>
                    <td>{{ ucfirst($application->study_mode ?? 'Full Time') }}</td>
                </tr>
                <tr>
                    <td>Application Number:</td>
                    <td><strong>{{ $application->application_number }}</strong></td>
                </tr>
            </table>

            <p><strong>Reporting Date:</strong> {{ $date->format('d') }} {{ $date->addMonth(1)->format('F, Y') }}</p>

            <div class="signature">
                <p>Yours in Education,</p>
                <p>Lawi Nehemiah<br>
                <strong>Admission Officer</strong><br>
                St. Maximillian College</p>
            </div>
        </div>

        <div class="footer">
            <p>St. Maximillian College | Tabora, Tanzania</p>
            <p>This is an official document. Generated on: {{ $date->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>