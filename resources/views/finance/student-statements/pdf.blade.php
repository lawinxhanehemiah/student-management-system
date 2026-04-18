<!DOCTYPE html>
<html>
<head>
    <title>Student Statement - {{ $student->registration_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .student-info { margin: 15px 0; padding: 10px; background: #f5f5f5; }
        .summary { display: flex; justify-content: space-between; margin: 15px 0; }
        .summary-box { border: 1px solid #ddd; padding: 10px; width: 30%; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #333; color: #fff; padding: 8px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-success { color: green; }
        .text-danger { color: red; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">ST. MAXIMILIAN KOLBE COLLEGE</div>
        <div>FINANCE DEPARTMENT</div>
        <div>STUDENT STATEMENT</div>
    </div>

    <div class="student-info">
        <table style="width: 100%; background: none; border: none;">
            <tr>
                <td><strong>Student Name:</strong> {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}</td>
                <td><strong>Reg No:</strong> {{ $student->registration_number }}</td>
            </tr>
            <tr>
                <td><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</td>
                <td><strong>Level:</strong> Year {{ $student->current_level }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <div class="summary-box">
            <strong>Total Invoiced</strong><br>
            TZS {{ number_format($summary['total_invoiced'], 2) }}
        </div>
        <div class="summary-box">
            <strong>Total Paid</strong><br>
            TZS {{ number_format($summary['total_paid'], 2) }}
        </div>
        <div class="summary-box">
            <strong>Balance</strong><br>
            TZS {{ number_format($summary['total_balance'], 2) }}
        </div>
    </div>

    <h4>Invoices</h4>
    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Due Date</th>
                <th>Type</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $invoice->invoice_type)) }}</td>
                <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                <td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td>
                <td class="text-end">{{ number_format($invoice->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Payments</h4>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                <td>{{ ucwords($payment->payment_method) }}</td>
                <td>{{ $payment->transaction_reference ?? $payment->receipt_number ?? 'N/A' }}</td>
                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('d F Y H:i:s') }}</p>
        <p>This is a computer generated statement. No signature required.</p>
    </div>
</body>
</html>