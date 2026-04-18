<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 10px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 16px;
            color: #2c3e50;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
        }
        
        .section {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 12px;
            background: #fafafa;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .label {
            width: 180px;
            color: #555;
            font-size: 12px;
        }
        
        .value {
            flex: 1;
            font-weight: normal;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 8px;
        }
        
        th {
            background: #f0f0f0;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 5px 8px;
            border: 1px solid #ddd;
        }
        
        .date {
            text-align: right;
            font-size: 11px;
            color: #777;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }
        
        .empty {
            color: #999;
            font-style: italic;
        }
        
        @media print {
            .section {
                border: 1px solid #000;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Details</h1>
            <p>Submitted on {{ \Carbon\Carbon::parse($application->created_at)->format('d/m/Y') }}</p>
        </div>
        
        <!-- Summary -->
        <div class="section">
            <div class="section-title">Application Summary</div>
            <div class="row">
                <div class="label">Email Address:</div>
                <div class="value">{{ auth()->user()->email }}</div>
            </div>
            <div class="row">
                <div class="label">Phone Number:</div>
                <div class="value">{{ $contact->phone ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Entry Level:</div>
                <div class="value">{{ $application->entry_level }}</div>
            </div>
        </div>
        
        <!-- Personal Info -->
        <div class="section">
            <div class="section-title">Personal Information</div>
            <div class="row">
                <div class="label">Full Name:</div>
                <div class="value">
                    {{ $personal->first_name ?? 'N/A' }} 
                    {{ $personal->middle_name ?? '' }} 
                    {{ $personal->last_name ?? '' }}
                </div>
            </div>
            <div class="row">
                <div class="label">Gender:</div>
                <div class="value">{{ $personal->gender ?? 'N/A' }}</div>
            </div>
            <div class="row">
                <div class="label">Date of Birth:</div>
                <div class="value">{{ $personal->date_of_birth ?? 'N/A' }}</div>
            </div>
            <div class="row">
                <div class="label">Nationality:</div>
                <div class="value">{{ $personal->nationality ?? 'N/A' }}</div>
            </div>
            <div class="row">
                <div class="label">Location:</div>
                <div class="value">{{ $contact->district ?? 'N/A' }}, {{ $contact->region ?? '' }}</div>
            </div>
        </div>
        
        <!-- Guardian Info -->
        <div class="section">
            <div class="section-title">Parent / Guardian</div>
            <div class="row">
                <div class="label">Name:</div>
                <div class="value">{{ $kin->guardian_name ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Phone:</div>
                <div class="value">{{ $kin->guardian_phone ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Relationship:</div>
                <div class="value">{{ $kin->relationship ?? '<span class="empty">Not specified</span>' }}</div>
            </div>
        </div>
        
        <!-- Academic Info -->
        <div class="section">
            <div class="section-title">Academic Information</div>
            <div class="row">
                <div class="label">Primary School:</div>
                <div class="value">{{ $academic->csee_school ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Index Number:</div>
                <div class="value">{{ $academic->csee_index_number ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Year:</div>
                <div class="value">{{ $academic->csee_year ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            <div class="row">
                <div class="label">Division:</div>
                <div class="value">{{ $academic->csee_division ?? '<span class="empty">Not provided</span>' }}</div>
            </div>
            
            <div style="margin-top: 10px;">
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 12px;">Subject Grades:</div>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                        <tr>
                            <td>{{ $subject->subject ?? $subject->name ?? 'N/A' }}</td>
                            <td>{{ $subject->grade }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="section">
            <div class="section-title">Additional Information</div>
            <div class="row">
                <div class="label">Information Source:</div>
                <div class="value">{{ $program->information_source ?? '<span class="empty">Not specified</span>' }}</div>
            </div>
        </div>
        
        <div class="date">
            Generated on: {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>