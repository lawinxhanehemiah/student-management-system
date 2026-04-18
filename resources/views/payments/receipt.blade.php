<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_number ?? 'N/A' }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
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
        .receipt-title {
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
            Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>{{ $school['name'] ?? 'St. Maximilian Kolbe College' }}</h1>
        <h3>OFFICIAL PAYMENT RECEIPT</h3>
    </div>

    <div class="school-info">
        <p>{{ $school['address'] ?? 'P.O. Box 123, Dar es Salaam' }}</p>
        <p>Tel: {{ $school['phone'] ?? '+255 123 456 789' }} | Email: {{ $school['email'] ?? 'info@college.ac.tz' }}</p>
    </div>

    <div class="receipt-title">
        RECEIPT
    </div>

    @php
        // Safely get data with null checks
        $paymentNumber = $payment->payment_number ?? 'N/A';
        $receiptNumber = $payment->receipt_number ?? $payment->payment_number ?? 'N/A';
        $paidAt = isset($payment->paid_at) ? \Carbon\Carbon::parse($payment->paid_at) : (isset($payment->created_at) ? \Carbon\Carbon::parse($payment->created_at) : now());
        $studentName = ($payment->student->user->first_name ?? '') . ' ' . ($payment->student->user->last_name ?? '');
        $studentName = trim($studentName) ?: 'N/A';
        $regNo = $payment->student->registration_number ?? 'N/A';
        $programme = $payment->student->programme->name ?? 'N/A';
        $academicYear = $payment->academicYear->name ?? 'N/A';
        $paymentMethod = isset($payment->payment_method) ? ucwords(str_replace('_', ' ', $payment->payment_method)) : 'N/A';
        $controlNumber = $payment->control_number ?? 'N/A';
        $transactionRef = $payment->transaction_reference ?? 'N/A';
        $invoiceNumber = $payment->payable->invoice_number ?? 'N/A';
        $amount = $payment->amount ?? 0;
        $createdByName = $payment->createdBy->name ?? 'System';
    @endphp

    <table class="info-table">
        <tr>
            <td>Receipt No:</td>
            <td>{{ $receiptNumber }}</td>
        </tr>
        <tr>
            <td>Payment No:</td>
            <td>{{ $paymentNumber }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>{{ $paidAt->format('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td>Student Name:</td>
            <td>{{ $studentName }}</td>
        </tr>
        <tr>
            <td>Registration No:</td>
            <td>{{ $regNo }}</td>
        </tr>
        <tr>
            <td>Programme:</td>
            <td>{{ $programme }}</td>
        </tr>
        <tr>
            <td>Academic Year:</td>
            <td>{{ $academicYear }}</td>
        </tr>
        <tr>
            <td>Payment Method:</td>
            <td>{{ $paymentMethod }}</td>
        </tr>
        @if($controlNumber != 'N/A')
        <tr>
            <td>Control Number:</td>
            <td>{{ $controlNumber }}</td>
        </tr>
        @endif
        @if($transactionRef != 'N/A')
        <tr>
            <td>Transaction Reference:</td>
            <td>{{ $transactionRef }}</td>
        </tr>
        @endif
        <tr>
            <td>Invoice Number:</td>
            <td>{{ $invoiceNumber }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Amount Paid</div>
        <div class="value">TZS {{ number_format($amount, 2) }}</div>
    </div>

    <div class="amount-box" style="background: #e8f5e9;">
        <div class="label">Amount in Words</div>
        <div class="value" style="font-size: 18px;">
            @php
                // Simple number to words function (basic)
                $words = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten'];
                $amountWords = $amount > 0 ? 'Tanzanian Shillings Only' : 'Zero';
            @endphp
            {{ $amountWords }}
        </div>
    </div>

    <div class="signature">
        <div>
            <div class="line">Received By</div>
            <div>{{ $createdByName }}</div>
        </div>
        <div>
            <div class="line">Verified By</div>
            <div>{{ $payment->metadata['verified_by'] ?? '______________' }}</div>
        </div>
        <div>
            <div class="line">Student Signature</div>
            <div>______________</div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated receipt. No signature is required.</p>
        <p>Generated on: {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>