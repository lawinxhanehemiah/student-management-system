@extends('layouts.financecontroller')

@section('title', 'Account Statement')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Account Statement</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.index') }}">Bank Accounts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.show', $account->id) }}">{{ $account->account_name }}</a></li>
                <li class="breadcrumb-item active">Statement</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <a href="{{ route('finance.bank.accounts.show', $account->id) }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Account
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filter Form -->
        <form action="{{ route('finance.bank.accounts.statement', $account->id) }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="start_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-filter me-2">
                    <i class="fas fa-search me-2"></i>Generate
                </button>
                <a href="{{ route('finance.bank.accounts.statement', $account->id) }}" class="btn btn-reset">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>

        <!-- Statement Header -->
        <div class="text-center mb-4">
            <h3>BANK ACCOUNT STATEMENT</h3>
            <h4>{{ $account->bank_name }} - {{ $account->account_name }}</h4>
            <p class="text-muted">
                Account Number: <strong>{{ $account->account_number }}</strong> |
                Currency: <strong>{{ $account->currency }}</strong>
            </p>
            <p class="text-muted">
                Period: {{ date('d M Y', strtotime($startDate)) }} to {{ date('d M Y', strtotime($endDate)) }}
            </p>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Opening Balance</h6>
                        <h4 class="text-primary">{{ number_format($openingBalance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Debits (In)</h6>
                        <h4 class="text-success">{{ number_format($transactions->where('transaction_type', 'deposit')->sum('amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Credits (Out)</h6>
                        <h4 class="text-danger">{{ number_format($transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="statementTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction #</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th class="text-end">Debit (In)</th>
                        <th class="text-end">Credit (Out)</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-info">
                        <td colspan="6" class="text-end fw-bold">Opening Balance:</td>
                        <td class="text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($transactions as $txn)
                        <tr>
                            <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('finance.bank.transactions.show', $txn->id) }}">
                                    {{ $txn->transaction_number }}
                                </a>
                            </td>
                            <td>{{ $txn->description }}</td>
                            <td>
                                @if($txn->metadata['reference'] ?? false)
                                    {{ $txn->metadata['reference'] }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end text-success">
                                {{ in_array($txn->transaction_type, ['deposit', 'opening_balance']) || 
                                   ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'incoming') 
                                   ? number_format($txn->amount, 2) : '-' }}
                            </td>
                            <td class="text-end text-danger">
                                {{ in_array($txn->transaction_type, ['withdrawal']) || 
                                   ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'outgoing')
                                   ? number_format($txn->amount, 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold">{{ number_format($txn->running_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No transactions found for this period</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr class="table-secondary fw-bold">
                        <td colspan="6" class="text-end">Closing Balance:</td>
                        <td class="text-end">{{ number_format($transactions->last()->running_balance ?? $openingBalance, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Notes -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6>Statement Notes:</h6>
                        <ul class="small text-muted mb-0">
                            <li>This statement includes all transactions from {{ date('d M Y', strtotime($startDate)) }} to {{ date('d M Y', strtotime($endDate)) }}</li>
                            <li>Opening balance is as at {{ date('d M Y', strtotime($startDate)) }}</li>
                            <li>All amounts are in {{ $account->currency }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6>Account Summary:</h6>
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Opening Balance:</td>
                                <td class="text-end">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Inflows:</td>
                                <td class="text-end text-success">{{ number_format($transactions->where('transaction_type', 'deposit')->sum('amount'), 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Outflows:</td>
                                <td class="text-end text-danger">{{ number_format($transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Net Movement:</td>
                                <td class="text-end">{{ number_format($transactions->where('transaction_type', 'deposit')->sum('amount') - $transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .page-header, .btn, form, footer, .nxl-navigation, .page-btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    #statementTable {
        font-size: 10pt;
    }
}
</style>
@endpush