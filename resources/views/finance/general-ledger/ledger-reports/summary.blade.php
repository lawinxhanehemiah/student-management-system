@extends('layouts.financecontroller')

@section('title', 'Ledger Summary')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Ledger Summary</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.ledger-reports.index') }}">Ledger Reports</a></li>
                <li class="breadcrumb-item active">Summary</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.ledger-reports.summary') }}" method="GET" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" 
                           value="{{ $fromDate }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" 
                           value="{{ $toDate }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="account_type" class="form-label">Account Type</label>
                    <select name="account_type" id="account_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="asset" {{ $accountType == 'asset' ? 'selected' : '' }}>Assets</option>
                        <option value="liability" {{ $accountType == 'liability' ? 'selected' : '' }}>Liabilities</option>
                        <option value="equity" {{ $accountType == 'equity' ? 'selected' : '' }}>Equity</option>
                        <option value="revenue" {{ $accountType == 'revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="expense" {{ $accountType == 'expense' ? 'selected' : '' }}>Expenses</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-filter me-2"></i>Generate Summary
                    </button>
                </div>
            </form>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-4">
            <h4>LEDGER SUMMARY REPORT</h4>
            <p class="text-muted">
                Period: {{ date('F d, Y', strtotime($fromDate)) }} to {{ date('F d, Y', strtotime($toDate)) }}
            </p>
            @if($accountType)
                <p class="text-muted">Account Type: {{ ucfirst($accountType) }}</p>
            @endif
        </div>

        <!-- Summary Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th class="text-end">Opening Balance</th>
                        <th class="text-end">Period Debit</th>
                        <th class="text-end">Period Credit</th>
                        <th class="text-end">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalOpening = 0;
                        $totalPeriodDebit = 0;
                        $totalPeriodCredit = 0;
                        $totalClosing = 0;
                    @endphp
                    @foreach($summary as $item)
                        @php
                            $totalOpening += $item['opening_balance'];
                            $totalPeriodDebit += $item['period_debit'];
                            $totalPeriodCredit += $item['period_credit'];
                            $totalClosing += $item['closing_balance'];
                        @endphp
                        <tr>
                            <td><strong>{{ $item['account']->account_code }}</strong></td>
                            <td>{{ $item['account']->account_name }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $item['account']->account_type == 'asset' ? 'primary' : 
                                    ($item['account']->account_type == 'liability' ? 'warning' : 
                                    ($item['account']->account_type == 'equity' ? 'success' : 
                                    ($item['account']->account_type == 'revenue' ? 'info' : 'danger'))) 
                                }}">
                                    {{ $item['account']->type_name }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($item['opening_balance'], 2) }}</td>
                            <td class="text-end">{{ number_format($item['period_debit'], 2) }}</td>
                            <td class="text-end">{{ number_format($item['period_credit'], 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($item['closing_balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTALS:</th>
                        <th class="text-end">{{ number_format($totalOpening, 2) }}</th>
                        <th class="text-end">{{ number_format($totalPeriodDebit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalPeriodCredit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalClosing, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Opening Balance</h6>
                        <h4>{{ number_format($totalOpening, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Total Debits (Period)</h6>
                        <h4>{{ number_format($totalPeriodDebit, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Total Credits (Period)</h6>
                        <h4>{{ number_format($totalPeriodCredit, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Total Closing Balance</h6>
                        <h4>{{ number_format($totalClosing, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection