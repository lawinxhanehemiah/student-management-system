@extends('layouts.financecontroller')

@section('title', 'Cash Flow Statement')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Cash Flow Statement</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Financial Reporting</a></li>
                <li class="breadcrumb-item active">Cash Flow</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="#" onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print me-2"></i>Print
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.reporting.cash-flow') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" id="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label for="method" class="form-label">Method</label>
                <select name="method" id="method" class="form-select">
                    <option value="indirect" {{ $method == 'indirect' ? 'selected' : '' }}>Indirect Method</option>
                    <option value="direct" {{ $method == 'direct' ? 'selected' : '' }}>Direct Method</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Generate
                </button>
                <a href="{{ route('finance.reporting.cash-flow') }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="text-center mb-4">
    <h3>CASH FLOW STATEMENT ({{ $method == 'indirect' ? 'Indirect Method' : 'Direct Method' }})</h3>
    <p class="text-muted">
        For the period <strong>{{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}</strong> 
        to <strong>{{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</strong>
    </p>
</div>

<!-- Cash Flow Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Opening Cash Balance</h6>
                <h3 class="text-white">{{ number_format($cashFlow['opening_cash'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Net Cash Flow</h6>
                <h3 class="text-white">{{ number_format($cashFlow['net_cash_flow'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Closing Cash Balance</h6>
                <h3 class="text-white">{{ number_format($cashFlow['closing_cash'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Cash Flow Statement Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <tbody>
                    @if($method == 'indirect')
                        <!-- INDIRECT METHOD -->
                        <tr class="table-primary">
                            <th colspan="2">OPERATING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Net Income</td>
                            <td class="text-end">{{ number_format($cashFlow['net_income'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Adjustments to reconcile net income:</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="ps-5">(Increase) / Decrease in Accounts Receivable</td>
                            <td class="text-end {{ $cashFlow['operating']['receivables']['change'] <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $cashFlow['operating']['receivables']['change'] <= 0 ? '' : '-' }}
                                {{ number_format(abs($cashFlow['operating']['receivables']['change']), 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5">Increase / (Decrease) in Accounts Payable</td>
                            <td class="text-end {{ $cashFlow['operating']['payables']['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $cashFlow['operating']['payables']['change'] >= 0 ? '' : '-' }}
                                {{ number_format(abs($cashFlow['operating']['payables']['change']), 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5">(Increase) / Decrease in Inventory</td>
                            <td class="text-end {{ $cashFlow['operating']['inventory']['change'] <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $cashFlow['operating']['inventory']['change'] <= 0 ? '' : '-' }}
                                {{ number_format(abs($cashFlow['operating']['inventory']['change']), 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5">(Increase) / Decrease in Prepaid Expenses</td>
                            <td class="text-end {{ $cashFlow['operating']['prepaids']['change'] <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $cashFlow['operating']['prepaids']['change'] <= 0 ? '' : '-' }}
                                {{ number_format(abs($cashFlow['operating']['prepaids']['change']), 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5">Increase / (Decrease) in Accrued Liabilities</td>
                            <td class="text-end {{ $cashFlow['operating']['accruals']['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $cashFlow['operating']['accruals']['change'] >= 0 ? '' : '-' }}
                                {{ number_format(abs($cashFlow['operating']['accruals']['change']), 2) }}
                            </td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Operating Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['operating']['net'], 2) }}</td>
                        </tr>

                        <tr class="table-warning">
                            <th colspan="2">INVESTING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Purchase of Fixed Assets</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['investing']['purchases'], 2) }})</td>
                        </tr>
                        <tr>
                            <td>Sale of Fixed Assets</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['investing']['sales'], 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Investing Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['investing']['net'], 2) }}</td>
                        </tr>

                        <tr class="table-info">
                            <th colspan="2">FINANCING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Loans Received</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['financing']['loans_received'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Loans Repaid</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['financing']['loans_repaid'], 2) }})</td>
                        </tr>
                        <tr>
                            <td>Capital Contributions</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['financing']['capital_in'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Dividends Paid</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['financing']['dividends_out'], 2) }})</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Financing Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['financing']['net'], 2) }}</td>
                        </tr>
                    @else
                        <!-- DIRECT METHOD -->
                        <tr class="table-primary">
                            <th colspan="2">OPERATING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Cash Receipts from Students/Customers</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['operating']['receipts'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Cash Payments to Suppliers</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['operating']['payments'], 2) }})</td>
                        </tr>
                        <tr>
                            <td>Cash Payments for Operating Expenses</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['operating']['operating_expenses'], 2) }})</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Operating Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['operating']['net'], 2) }}</td>
                        </tr>

                        <tr class="table-warning">
                            <th colspan="2">INVESTING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Purchase of Fixed Assets</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['investing']['purchases'], 2) }})</td>
                        </tr>
                        <tr>
                            <td>Sale of Fixed Assets</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['investing']['sales'], 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Investing Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['investing']['net'], 2) }}</td>
                        </tr>

                        <tr class="table-info">
                            <th colspan="2">FINANCING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td>Loans Received</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['financing']['loans_received'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Loans Repaid</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['financing']['loans_repaid'], 2) }})</td>
                        </tr>
                        <tr>
                            <td>Capital Contributions</td>
                            <td class="text-end text-success">{{ number_format($cashFlow['financing']['capital_in'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Dividends Paid</td>
                            <td class="text-end text-danger">({{ number_format($cashFlow['financing']['dividends_out'], 2) }})</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Net Cash from Financing Activities</td>
                            <td class="text-end">{{ number_format($cashFlow['financing']['net'], 2) }}</td>
                        </tr>
                    @endif

                    <tr class="table-success fw-bold" style="font-size: 1.1rem;">
                        <td>NET INCREASE/(DECREASE) IN CASH</td>
                        <td class="text-end">{{ number_format($cashFlow['net_cash_flow'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Cash at Beginning of Period</td>
                        <td class="text-end">{{ number_format($cashFlow['opening_cash'], 2) }}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Cash at End of Period</td>
                        <td class="text-end">{{ number_format($cashFlow['closing_cash'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection