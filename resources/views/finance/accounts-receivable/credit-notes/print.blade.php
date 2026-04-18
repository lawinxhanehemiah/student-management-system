<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Note - {{ $creditNote->credit_note_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #27ae60;
            margin: 10px 0 5px;
        }
        .header h3 {
            color: #333;
            margin: 0;
        }
        .school-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .school-info p {
            margin: 5px 0;
            color: #666;
        }
        .credit-note-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background: #f8f9fa;
        }
        .amount-box {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
        }
        .amount-box .label {
            font-size: 14px;
            color: #666;
        }
        .amount-box .value {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            text-align: center;
            width: 30%;
        }
        .signature .line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #27ae60; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            <i class="feather-printer"></i> Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>ST. MAXIMILIANCOLBE COLLEGE</h1>
        <h3>FINANCE CONTROLLER</h3>
    </div>

    <div class="school-info">
        <p>P.O. Box 123, Dar es Salaam, Tanzania</p>
        <p>Tel: +255 123 456 789 | Email: finance@stmaximiliankolbe.ac.tz</p>
    </div>

    <div class="credit-note-title">
        CREDIT NOTE
    </div>

    <table class="info-table">
        <tr>
            <td>Credit Note No:</td>
            <td>{{ $creditNote->credit_note_number }}</td>
        </tr>
        <tr>
            <td>Date Issued:</td>
            <td>{{ $creditNote->issue_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Reference Invoice:</td>
            <td>{{ $creditNote->invoice->invoice_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Student Name:</td>
            <td>{{ $creditNote->student->user->first_name ?? '' }} {{ $creditNote->student->user->last_name ?? '' }}</td>
        </tr>
        <tr>
            <td>Registration No:</td>
            <td>{{ $creditNote->student->registration_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Programme:</td>
            <td>{{ $creditNote->student->programme->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Academic Year:</td>
            <td>{{ $creditNote->academicYear->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Reason:</td>
            <td>{{ $creditNote->reason }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Credit Amount</div>
        <div class="value">TZS {{ number_format($creditNote->amount, 2) }}</div>
    </div>

    @if($creditNote->description)
    <div style="margin: 20px 0;">
        <h4>Description:</h4>
        <p>{{ $creditNote->description }}</p>
    </div>
    @endif

    <div class="signature">
        <div>
            <div class="line">Prepared By</div>
            <div>{{ $creditNote->createdBy->name ?? '______________' }}</div>
            <div style="font-size: 12px; color: #666;">Date: {{ $creditNote->created_at->format('d/m/Y') }}</div>
        </div>
        
        @if($creditNote->approved_by)
        <div>
            <div class="line">Approved By</div>
            <div>{{ $creditNote->approvedBy->name ?? '______________' }}</div>
            <div style="font-size: 12px; color: #666;">Date: {{ $creditNote->approved_at->format('d/m/Y') }}</div>
        </div>
        @else
        <div>
            <div class="line">Approved By</div>
            <div>______________</div>
        </div>
        @endif
        
        <div>
            <div class="line">Received By</div>
            <div>______________</div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated credit note. No signature is required.</p>
        <p>Generated on: {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <script>
        window.onload = function() {
            // Auto print if needed
            // window.print();
        }
    </script>
</body>
</html>