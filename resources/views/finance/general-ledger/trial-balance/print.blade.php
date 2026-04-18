<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Balance - {{ date('F d, Y', strtotime($asOfDate)) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            margin: 2cm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        .header h3 {
            font-size: 14pt;
            margin-top: 0;
            color: #666;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .tfoot {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin-top: 5px;
        }
        @media print {
            body {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <div class="company-info">
            <p>P.O. Box 12345, Dar es Salaam, Tanzania</p>
            <p>Tel: +255 123 456 789 | Email: info@company.com</p>
        </div>
        <h2>TRIAL BALANCE</h2>
        <h3>As of {{ date('F d, Y', strtotime($asOfDate)) }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="text-end">Debit (TZS)</th>
                <th class="text-end">Credit (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach($accounts as $account)
                @php
                    $debit = 0;
                    $credit = 0;
                    
                    if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                        if ($account->current_balance > 0) {
                            $debit = $account->current_balance;
                        } else {
                            $credit = abs($account->current_balance);
                        }
                    } else {
                        if ($account->current_balance > 0) {
                            $credit = $account->current_balance;
                        } else {
                            $debit = abs($account->current_balance);
                        }
                    }
                    
                    if ($debit > 0 || $credit > 0) {
                        $totalDebit += $debit;
                        $totalCredit += $credit;
                    }
                @endphp
                @if($debit > 0 || $credit > 0)
                    <tr>
                        <td>{{ $account->account_code }}</td>
                        <td>
                            @if($account->level > 1)
                                {!! str_repeat('&nbsp;&nbsp;&nbsp;', $account->level - 1) !!}
                                @if($account->level > 1)└ @endif
                            @endif
                            {{ $account->account_name }}
                        </td>
                        <td class="text-end">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                        <td class="text-end">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot class="tfoot">
            <tr>
                <td colspan="2" class="text-end fw-bold">TOTALS:</td>
                <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-center">
                    <strong>STATUS: {{ abs($totalDebit - $totalCredit) < 0.01 ? 'BALANCED' : 'NOT BALANCED' }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="signature">
        <div>
            <p>Prepared By:</p>
            <div class="signature-line"></div>
            <p>Date: _______________</p>
        </div>
        <div>
            <p>Approved By:</p>
            <div class="signature-line"></div>
            <p>Date: _______________</p>
        </div>
    </div>

    <div class="text-center" style="margin-top: 30px; font-size: 8pt; color: #666;">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>