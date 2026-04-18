@extends('layouts.financecontroller')

@section('title', 'Trial Balance')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Trial Balance</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item active">Trial Balance</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.general-ledger.trial-balance.export', request()->query()) }}" class="btn btn-secondary">
            <i class="fas fa-download me-2"></i>Export
        </a>
        <a href="{{ route('finance.general-ledger.trial-balance.print', request()->query()) }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print me-2"></i>Print
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.trial-balance.index') }}" method="GET" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="as_of_date" class="form-label">As of Date</label>
                    <input type="date" name="as_of_date" id="as_of_date" class="form-control" 
                           value="{{ $asOfDate }}">
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
                <div class="col-lg-2 col-md-6">
                    <label for="level" class="form-label">Level</label>
                    <select name="level" id="level" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Level 1</option>
                        <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Level 2</option>
                        <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Level 3</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="show_zero_balances" class="form-label">Zero Balances</label>
                    <select name="show_zero_balances" id="show_zero_balances" class="form-select">
                        <option value="1" {{ request('show_zero_balances', '1') == '1' ? 'selected' : '' }}>Show</option>
                        <option value="0" {{ request('show_zero_balances') == '0' ? 'selected' : '' }}>Hide</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-filter me-2"></i>Generate
                    </button>
                </div>
            </form>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-4">
            <h4>TRIAL BALANCE</h4>
            <p class="text-muted">As of {{ date('F d, Y', strtotime($asOfDate)) }}</p>
            @if($accountType)
                <p class="text-muted">Account Type: {{ ucfirst($accountType) }}</p>
            @endif
        </div>

        <!-- Balance Status -->
        <div class="alert {{ $trialBalance['is_balanced'] ? 'alert-success' : 'alert-danger' }} mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas {{ $trialBalance['is_balanced'] ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-2"></i>
                    Trial Balance is {{ $trialBalance['is_balanced'] ? 'BALANCED' : 'NOT BALANCED' }}
                </span>
                <span>
                    Total Debit: <strong>{{ number_format($trialBalance['totals']['debit'], 2) }}</strong> |
                    Total Credit: <strong>{{ number_format($trialBalance['totals']['credit'], 2) }}</strong> |
                    Difference: <strong>{{ number_format($trialBalance['totals']['debit'] - $trialBalance['totals']['credit'], 2) }}</strong>
                </span>
            </div>
        </div>

        <!-- Summary by Type -->
        <div class="row mb-4">
            @foreach($trialBalance['by_type'] as $type => $data)
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="summary-card bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $data['name'] }}</h6>
                        <div class="small">
                            <span class="text-primary">D: {{ number_format($data['debit'], 0) }}</span> |
                            <span class="text-success">C: {{ number_format($data['credit'], 0) }}</span>
                        </div>
                        <small class="text-muted">{{ $data['count'] }} accounts</small>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Trial Balance Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="trialBalanceTable">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    @foreach($trialBalance['accounts'] as $account)
                        @php
                            $debit = $account->trial_balance_debit ?? 0;
                            $credit = $account->trial_balance_credit ?? 0;
                            $totalDebit += $debit;
                            $totalCredit += $credit;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $account->account_code }}</strong>
                            </td>
                            <td>
                                @if($account->level > 1)
                                    <span style="margin-left: {{ ($account->level - 1) * 20 }}px">
                                        <i class="fas fa-level-down-alt text-muted me-2"></i>
                                @endif
                                {{ $account->account_name }}
                                @if($account->is_header)
                                    <span class="badge bg-info ms-2">Header</span>
                                @endif
                                @if($account->level > 1)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $account->account_type == 'asset' ? 'primary' : 
                                    ($account->account_type == 'liability' ? 'warning' : 
                                    ($account->account_type == 'equity' ? 'success' : 
                                    ($account->account_type == 'revenue' ? 'info' : 'danger'))) 
                                }}">
                                    {{ $account->type_name }}
                                </span>
                            </td>
                            <td class="text-end {{ $debit > 0 ? 'fw-bold' : '' }}">
                                {{ $debit > 0 ? number_format($debit, 2) : '-' }}
                            </td>
                            <td class="text-end {{ $credit > 0 ? 'fw-bold' : '' }}">
                                {{ $credit > 0 ? number_format($credit, 2) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTALS:</th>
                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.summary-card {
    transition: all 0.3s;
}
.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
#trialBalanceTable tfoot {
    border-top: 2px solid #dee2e6;
}
</style>
@endpush