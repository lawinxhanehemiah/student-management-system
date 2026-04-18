{{-- resources/views/finance/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 20px;
        }
        .header img {
            max-height: 160px;
            width: auto;
            object-fit: contain;
        }
        .header h3 {
            color: #27ae60;
            margin: 10px 0 0 0;
            font-size: 24px;
        }
        .control-number {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 2px dashed #27ae60;
        }
        .control-number .number {
            color: #27ae60;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background: #27ae60;
            color: white;
        }
        .text-end {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <!-- Option 1: Full server path -->
            <img src="{{ public_path('assets/images/header.jpg') }}" alt="College Header">
            
            <!-- OR Option 2: Tumia base64 (kama Option 1 haifanyi kazi) -->
            <!--
            @php
                $path = public_path('assets/images/header.jpg');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            @endphp
            <img src="{{ $base64 }}" alt="College Header">
            -->
            
            <h3>INVOICE</h3>
        </div>

        <div class="control-number">
            <div class="number">{{ $invoice->control_number }}</div>
        </div>

        <table>
            <tr>
                <td><strong>Invoice Number:</strong></td>
                <td>{{ $invoice->invoice_number }}</td>
                <td><strong>Date:</strong></td>
                <td>{{ $invoice->issue_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td><strong>Student:</strong></td>
                <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                <td><strong>Due Date:</strong></td>
                <td>{{ $invoice->due_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td><strong>Reg No:</strong></td>
                <td>{{ $invoice->student->registration_number ?? '' }}</td>
                <td><strong>Type:</strong></td>
                <td>
                    @if($invoice->invoice_type == 'repeat_module')
                        Repeat Module
                    @else
                        Supplementary
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-end">{{ number_format($item->total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end">Total:</th>
                    <th class="text-end">{{ number_format($invoice->total_amount, 0) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>This is a Finance generated invoice</p>
        </div>
    </div>
</body>
</html>