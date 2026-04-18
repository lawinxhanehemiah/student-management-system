{{-- resources/views/finance/invoices/print.blade.php --}}
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
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #27ae60;
            margin: 0;
            font-size: 28px;
        }
        .header h3 {
            color: #666;
            margin: 5px 0;
            font-weight: normal;
        }
        .control-number {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 2px dashed #27ae60;
            border-radius: 5px;
        }
        .control-number .label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .control-number .number {
            color: #27ae60;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            font-family: monospace;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            font-weight: bold;
            background: #f8f9fa;
            width: 40%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #27ae60;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tfoot td {
            font-weight: bold;
            background: #f8f9fa;
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
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        .status-partial {
            background: #fff3cd;
            color: #856404;
        }
        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <h1><img 
                src="{{ asset('assets/images/header.jpg') }}" 
                alt="College Header"
                class="img-fluid w-100"
                style="max-height: 160px; object-fit: contain;"
            ></h1>
            <h3>INVOICE</h3>
        </div>

        <!-- Control Number -->
        <div class="control-number">
            <div class="label">Control Number</div>
            <div class="number">{{ $invoice->control_number }}</div>
        </div>

        <!-- Invoice Info -->
        <table class="info-table">
            <tr>
                <td>Invoice Number:</td>
                <td><strong>{{ $invoice->invoice_number }}</strong></td>
            </tr>
            <tr>
                <td>Invoice Type:</td>
                <td>
                    @if($invoice->invoice_type == 'repeat_module')
                        Repeat Module Fee
                    @elseif($invoice->invoice_type == 'supplementary')
                        Supplementary Fee
                    @else
                        {{ ucfirst($invoice->invoice_type) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>Issue Date:</td>
                <td>{{ $invoice->issue_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Due Date:</td>
                <td>{{ $invoice->due_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>
                    @if($invoice->payment_status == 'paid')
                        <span class="status-badge status-paid">Paid</span>
                    @elseif($invoice->payment_status == 'partial')
                        <span class="status-badge status-partial">Partial</span>
                    @elseif($invoice->isOverdue())
                        <span class="status-badge status-unpaid">Overdue</span>
                    @else
                        <span class="status-badge status-unpaid">Unpaid</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Student Information -->
        <table class="info-table">
            <tr>
                <td>Student Name:</td>
                <td><strong>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</strong></td>
            </tr>
            <tr>
                <td>Registration Number:</td>
                <td><strong>{{ $invoice->student->registration_number ?? '' }}</strong></td>
            </tr>
            <tr>
                <td>Programme:</td>
                <td>{{ $invoice->student->programme->name ?? '' }}</td>
            </tr>
            <tr>
                <td>Academic Year:</td>
                <td>{{ $invoice->academicYear->name ?? '' }}</td>
            </tr>
        </table>

        <!-- Invoice Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount (TZS)</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Total (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-end">{{ number_format($item->amount, 0) }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                    <td class="text-end"><strong>{{ number_format($invoice->total_amount, 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">Paid Amount:</td>
                    <td class="text-end">{{ number_format($invoice->paid_amount, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Balance:</strong></td>
                    <td class="text-end"><strong>{{ number_format($invoice->balance, 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Description if any -->
        @if($invoice->description)
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <strong>Description:</strong><br>
            {{ $invoice->description }}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a Finance generated invoice.</p>
            <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
            <p>For any queries, contact finance department</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>