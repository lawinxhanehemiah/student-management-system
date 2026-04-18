@extends('layouts.financecontroller')

@section('title', 'Bank Transactions')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Bank Transactions</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Bank & Cash</a></li>
                <li class="breadcrumb-item active">Transactions</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus me-2"></i>New Transaction
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('finance.bank.transactions.deposit.create') }}">
                    <i class="fas fa-arrow-down text-success me-2"></i>Deposit
                </a></li>
                <li><a class="dropdown-item" href="{{ route('finance.bank.transactions.withdrawal.create') }}">
                    <i class="fas fa-arrow-up text-danger me-2"></i>Withdrawal
                </a></li>
                <li><a class="dropdown-item" href="{{ route('finance.bank.transactions.transfer') }}">
                    <i class="fas fa-exchange-alt text-info me-2"></i>Transfer
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Deposits</h6>
                        <h3 class="text-white">{{ number_format($totals['deposits'], 2) }}</h3>
                    </div>
                    <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Withdrawals</h6>
                        <h3 class="text-white">{{ number_format($totals['withdrawals'], 2) }}</h3>
                    </div>
                    <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Pending Transactions</h6>
                        <h3 class="text-white">{{ $totals['pending'] }}</h3>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.bank.transactions.index') }}" method="GET" class="row g-3">
                <div class="col-lg-2 col-md-6">
                    <label for="bank_account_id" class="form-label">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->bank_name }} - {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="transaction_type" class="form-label">Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="deposit" {{ request('transaction_type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="withdrawal" {{ request('transaction_type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                        <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="opening_balance" {{ request('transaction_type') == 'opening_balance' ? 'selected' : '' }}>Opening Balance</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('finance.bank.transactions.index') }}" class="btn btn-reset">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction #</th>
                        <th>Bank Account</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $txn->transaction_number }}</strong>
                            </td>
                            <td>
                                <small>{{ $txn->bankAccount->bank_name }}</small><br>
                                <small class="text-muted">{{ $txn->bankAccount->account_number }}</small>
                            </td>
                            <td>{{ Str::limit($txn->description, 40) }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $txn->transaction_type == 'deposit' ? 'success' : 
                                    ($txn->transaction_type == 'withdrawal' ? 'danger' : 
                                    ($txn->transaction_type == 'transfer' ? 'info' : 'secondary')) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $txn->transaction_type)) }}
                                </span>
                            </td>
                            <td class="text-end {{ $txn->transaction_type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                {{ $txn->transaction_type == 'deposit' ? '+' : '-' }}
                                {{ number_format($txn->amount, 2) }}
                            </td>
                            <td>{!! $txn->status_badge !!}</td>
                            <td>
                                <a href="{{ route('finance.bank.transactions.show', $txn->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No transactions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection