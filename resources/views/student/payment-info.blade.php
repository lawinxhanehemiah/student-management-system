@extends('layouts.students')

@section('title', 'Payment Info')

@section('content')
<style>
    /* Simple Payment Info - Compact Table */
    .payment-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Page Header */
    .page-header {
        margin-bottom: 20px;
    }
    
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #1a1a2e;
    }
    
    /* Note Box */
    .note-box {
        background: #fff8e7;
        border-left: 3px solid #ffc107;
        padding: 10px 15px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #856404;
    }
    
    /* Filter Section */
    .filter-section {
        margin-bottom: 25px;
    }
    
    .filter-label {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 10px;
    }
    
    .year-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .year-btn {
        padding: 5px 16px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 13px;
        background: white;
        color: #555;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .year-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }
    
    .year-btn:hover:not(.active) {
        background: #f8f9fa;
        border-color: #aaa;
    }
    
    /* Action Links */
    .action-links {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .action-links a {
        color: #667eea;
        text-decoration: none;
        font-size: 13px;
    }
    
    .action-links a:hover {
        text-decoration: underline;
    }
    
    /* Table Title */
    .table-title {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
        display: inline-block;
    }
    
    /* Table - Horizontal Scroll */
    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    
    .payment-table {
        width: 100%;
        min-width: 1000px;
        border-collapse: collapse;
        font-size: 12px;
        background: white;
    }
    
    .payment-table th,
    .payment-table td {
        border: 1px solid #e9ecef;
        padding: 6px 10px;
        vertical-align: middle;
    }
    
    .payment-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #1a1a2e;
        white-space: nowrap;
    }
    
    .payment-table td {
        color: #555;
    }
    
    /* Balance column - NARROW */
    .payment-table th:nth-child(8),
    .payment-table td:nth-child(8) {
        min-width: 90px;
        width: 90px;
        text-align: right;
        white-space: nowrap;
    }
    
    /* Date column */
    .payment-table th:last-child,
    .payment-table td:last-child {
        white-space: nowrap;
        min-width: 160px;
    }
    
    /* Fee Type column - wider */
    .payment-table td:nth-child(5) {
        white-space: normal;
        min-width: 200px;
        max-width: 250px;
    }
    
    .payment-table th:nth-child(5) {
        min-width: 200px;
    }
    
    /* Number columns */
    .payment-table th:nth-child(6),
    .payment-table td:nth-child(6),
    .payment-table th:nth-child(7),
    .payment-table td:nth-child(7) {
        min-width: 100px;
        text-align: right;
    }
    
    .payment-table tbody tr:hover td {
        background: #fafafa;
    }
    
    /* Text alignment */
    .text-end {
        text-align: right;
    }
    
    .text-center {
        text-align: center;
    }
    
    .fw-bold {
        font-weight: 600;
    }
    
    /* Badges */
    .badge-invoice {
        background: #e3f2fd;
        color: #1976d2;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-payment {
        background: #e8f5e9;
        color: #388e3c;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        display: inline-block;
        white-space: nowrap;
    }
    
    /* Totals Row */
    .totals-row {
        background: #f8f9fa;
        font-weight: 600;
    }
    
    .totals-row td {
        padding: 8px 10px;
        border-top: 1px solid #dee2e6;
    }
    
    /* Colors */
    .text-success {
        color: #10b981 !important;
    }
    
    .text-danger {
        color: #ef4444 !important;
    }
    
    /* Footer */
    .page-footer {
        text-align: center;
        padding: 20px;
        margin-top: 30px;
        border-top: 1px solid #e9ecef;
        font-size: 12px;
        color: #999;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .payment-container {
            padding: 12px;
        }
        
        .page-title {
            font-size: 1.2rem;
        }
        
        .note-box {
            font-size: 11px;
            padding: 8px 12px;
        }
        
        .filter-label {
            font-size: 12px;
        }
        
        .year-buttons {
            gap: 8px;
        }
        
        .year-btn {
            padding: 4px 12px;
            font-size: 12px;
        }
        
        .action-links {
            gap: 12px;
        }
        
        .action-links a {
            font-size: 11px;
        }
        
        .table-title {
            font-size: 13px;
        }
        
        .payment-table th,
        .payment-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        
        /* Balance column narrower on mobile */
        .payment-table th:nth-child(8),
        .payment-table td:nth-child(8) {
            min-width: 70px;
            width: 70px;
        }
        
        /* Number columns */
        .payment-table th:nth-child(6),
        .payment-table td:nth-child(6),
        .payment-table th:nth-child(7),
        .payment-table td:nth-child(7) {
            min-width: 80px;
        }
        
        /* Date column */
        .payment-table th:last-child,
        .payment-table td:last-child {
            min-width: 150px;
        }
        
        .payment-table td:nth-child(5) {
            min-width: 160px;
            max-width: 180px;
        }
        
        .page-footer {
            font-size: 10px;
            padding: 15px;
        }
    }
    
    @media print {
        .filter-section, .action-links, .page-footer {
            display: none;
        }
    }
</style>

<div class="payment-container">
    
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Payment Info</h1>
    </div>

    <!-- Note Box -->
    <div class="note-box">
        <i class="feather-info"></i> 
        <strong>Note:</strong> Click "View All" Link below to view all hidden invoice & control numbers (eg. transcript or RM invoice). 
        If control number reject the payment click the "activate" button below to re-use it and extend its expire date.
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-label">Filter By Year</div>
        <div class="year-buttons">
            @foreach($yearsWithTransactions as $year)
                <a href="{{ route('student.payments.index', ['academic_year_id' => $year['id']]) }}" 
                   class="year-btn {{ ($selectedYearId ?? '') == $year['id'] ? 'active' : '' }}">
                    {{ $year['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Action Links -->
    <div class="action-links">
        <a href="#" onclick="showAllTransactions()">
            <i class="feather-eye"></i> View All Records
        </a>
        @if(($closingBalance ?? 0) > 0)
        <a href="#" onclick="activateControlNumber()" style="color: #dc3545;">
            <i class="feather-refresh-cw"></i> Activate Expired Control No.
        </a>
        @endif
        <a href="#" onclick="window.print()">
            <i class="feather-printer"></i> Print Statement
        </a>
        <a href="#" onclick="downloadPDF()">
            <i class="feather-download"></i> Download PDF
        </a>
    </div>

    <!-- Table Title -->
    <div class="table-title">
        Academic Year: {{ $selectedYearName ?? 'All Years' }}
    </div>

    <!-- Table with Horizontal Scroll -->
    <div class="table-wrapper">
        <table class="payment-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th>Academic Year</th>
                    <th>Control Number</th>
                    <th>Receipt</th>
                    <th>Fee Type</th>
                    <th class="text-end">Debit (TZS)</th>
                    <th class="text-end">Credit (TZS)</th>
                    <th class="text-end">Balance (TZS)</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $index => $transaction)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>{{ $transaction['academic_year'] }}</td>
                    <td>
                        @if($transaction['receipt'] == 'INVOICE')
                            <span class="badge-invoice">{{ $transaction['control_number'] }}</span>
                        @else
                            {{ $transaction['control_number'] }}
                        @endif
                    </td>
                    <td>
                        @if($transaction['receipt'] == 'INVOICE')
                            <span class="badge-invoice">INVOICE</span>
                        @else
                            <span class="badge-payment">PAYMENT</span>
                        @endif
                    </td>
                    <td style="word-break: break-word; white-space: normal;">
                        {{ $transaction['fee_type'] }}
                    </td>
                    <td class="text-end fw-bold">{{ number_format($transaction['debit'], 0) }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($transaction['credit'], 0) }}</td>
                    <td class="text-end fw-bold {{ $transaction['balance'] > 0 ? 'text-danger' : 'text-success' }}" style="white-space: nowrap;">
                        {{ number_format($transaction['balance'], 0) }}
                    </td>
                    <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($transaction['date'])->format('Y-m-d\TH:i:s') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        No transactions found for {{ $selectedYearName ?? 'this period' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($transactions ?? []) > 0)
            <tfoot>
                <tr class="totals-row">
                    <td colspan="5" class="text-end fw-bold">Total</td>
                    <td class="text-end fw-bold">{{ number_format($totalDebit ?? 0, 0) }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalCredit ?? 0, 0) }}</td>
                    <td class="text-end fw-bold {{ ($closingBalance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($closingBalance ?? 0, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Footer -->
    <div class="page-footer">
        College Website | Copyright © {{ date('Y') }} St. Maximiliancolbe College. All rights reserved.
    </div>

</div>

<script>
    function showAllTransactions() {
        var url = new URL(window.location.href);
        url.searchParams.set('show_all', '1');
        window.location.href = url.toString();
    }
    
    function activateControlNumber() {
        alert('Please contact finance department to activate expired control numbers.');
    }
    
    function downloadPDF() {
        window.location.href = '{{ route('student.payments.download') }}';
    }
</script>
@endsection