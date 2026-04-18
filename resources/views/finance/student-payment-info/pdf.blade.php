<!DOCTYPE html>
<html>
<head>
    <title>Payment Info - {{ $student->registration_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; }
        .student-info { margin: 15px 0; padding: 10px; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #333; color: #fff; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-success { color: green; }
        .text-danger { color: red; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #666; }
        .summary { display: flex; justify-content: space-between; margin: 20px 0; }
        .summary-box { border: 1px solid #ddd; padding: 10px; width: 30%; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">ST. MAXIMILIAN KOLBE COLLEGE</div>
        <div>FINANCE DEPARTMENT - STUDENT PAYMENT INFORMATION</div>
    </div>

    <div class="student-info">
        <table style="width: 100%; background: none; border: none;">
            <tr>
                <td><strong>Student Name:</strong> {{ $student->user->first_name }} {{ $student->user->last_name }}</td>
                <td><strong>Reg No:</strong> {{ $student->registration_number }}</td>
            </tr>
            <tr>
                <td><strong>Programme:</strong> {{ $student->programme->name ?? 'N/A' }}</td>
                <td><strong>Current Level:</strong> Year {{ $student->current_level }}</td>
            </tr>
            <tr>
                <td><strong>Printed On:</strong> {{ now()->format('d F Y H:i:s') }}</td>
                <td><strong>Today:</strong> {{ now()->format('d M, Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <div class="summary-box">
            <strong>Total Invoiced</strong><br>
            TZS {{ number_format($totalDebit, 2) }}
        </div>
        <div class="summary-box">
            <strong>Total Paid</strong><br>
            TZS {{ number_format($totalCredit, 2) }}
        </div>
        <div class="summary-box">
            <strong>Balance</strong><br>
            TZS {{ number_format($closingBalance, 2) }}
        </div>
    </div>

    <h4>Transaction History</h4>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Academic Year</th>
                <th>Control #</th>
                <th>Receipt</th>
                <th>Fee Type</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction['date']->format('Y-m-d H:i') }}</td>
                <td>{{ $transaction['academic_year'] }}</td>
                <td>{{ $transaction['control_number'] }}</td>
                <td>{{ $transaction['receipt'] }}</td>
                <td>{{ $transaction['fee_type'] }}</td>
                <td class="text-end">{{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}</td>
                <td class="text-end">{{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}</td>
                <td class="text-end {{ $transaction['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($transaction['balance'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer generated statement. No signature required.</p>
        <p>Note: Hidden invoices (eg. transcript) are included in this statement.</p>
    </div>
</body>
</html>