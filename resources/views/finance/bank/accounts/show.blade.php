@extends('layouts.financecontroller')

@section('title', 'Bank Account Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Bank Account Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.index') }}">Bank Accounts</a></li>
                <li class="breadcrumb-item active">{{ $account->account_name }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('finance.bank.accounts.edit', $account->id) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>Edit
    </a>

    <a href="{{ route('finance.bank.accounts.statement', $account->id) }}" class="btn btn-info">
        <i class="fas fa-file-alt me-2"></i>Statement
    </a>

    <a href="{{ route('finance.bank.transactions.deposit.create') }}?account_id={{ $account->id }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Deposit
    </a>

    <a href="{{ route('finance.bank.transactions.withdrawal.create') }}?account_id={{ $account->id }}" class="btn btn-danger">
        <i class="fas fa-minus me-2"></i>Withdraw
    </a>

    <a href="{{ route('finance.bank.accounts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>
</div>

<div class="row">
    <!-- Account Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <!-- Bank Logo based on bank name -->
                    @php
                        $bankLogos = [
                            'NMB' => 'nmb-bank.png',
                            'CRDB' => 'crdb-bank.png',
                            'NBC' => 'nbc-bank.png',
                            'Exim' => 'exim-bank.png',
                            'Amana' => 'amana-bank.png',
                            'Standard Chartered' => 'standard-chartered.png',
                            'Barclays' => 'barclays.png',
                            'DTB' => 'dtb.png',
                            'KCB' => 'kcb.png',
                            'Equity' => 'equity-bank.png',
                            'Bank of Africa' => 'boa.png',
                            'TIB' => 'tib.png',
                            'TWB' => 'twb.png',
                            'Azania' => 'azania-bank.png',
                            'Mkombozi' => 'mkombozi-bank.png',
                            'Yetu' => 'yetu-bank.png',
                            'Uchumi' => 'uchumi-bank.png',
                            'Postal' => 'postal-bank.png',
                        ];
                        
                        $bankKey = explode(' ', trim($account->bank_name))[0];
                        $logoFile = $bankLogos[$bankKey] ?? 'default-bank.png';
                        $logoPath = asset('assets/img/banks/' . $logoFile);
                    @endphp
                    
                    <img src="{{ $logoPath }}" alt="{{ $account->bank_name }}" 
                         class="img-fluid mb-2" style="max-height: 60px; width: auto;"
                         onerror="this.onerror=null; this.src='{{ asset('assets/img/banks/default-bank.png') }}'; this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <i class="fas fa-university fa-4x text-primary" style="display: none;"></i>
                    
                    <h4 class="mt-2">{{ $account->bank_name }}</h4>
                    @if($account->is_default)
                        <span class="badge bg-info">Default Account</span>
                    @endif
                    {!! $account->status_badge !!}
                </div>

                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Name:</span>
                        <span class="value fw-bold">{{ $account->account_name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Number:</span>
                        <span class="value">{{ $account->account_number }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Branch:</span>
                        <span class="value">{{ $account->branch ?? '-' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">SWIFT Code:</span>
                        <span class="value">{{ $account->swift_code ?? '-' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Currency:</span>
                        <span class="value">
                            <span class="badge bg-secondary">{{ $account->currency }}</span>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Opening Balance:</span>
                        <span class="value">{{ number_format($account->opening_balance, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Current Balance:</span>
                        <span class="value fw-bold text-{{ $account->current_balance > 0 ? 'success' : 'danger' }}">
                            {{ number_format($account->current_balance, 2) }}
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $account->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $account->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                @if($account->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $account->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics and Transactions -->
    <div class="col-lg-8">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Total Deposits</h6>
                        <h4>{{ number_format($stats['total_deposits'], 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h6>Total Withdrawals</h6>
                        <h4>{{ number_format($stats['total_withdrawals'], 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h6>Pending</h6>
                        <h4>{{ $stats['pending_count'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>This Month</h6>
                        <h4>{{ number_format($stats['this_month'], 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recent Transactions</h5>
                <a href="{{ route('finance.bank.transactions.index', ['bank_account_id' => $account->id]) }}" class="btn btn-sm btn-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction #</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($account->transactions as $txn)
                                <tr>
                                    <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('finance.bank.transactions.show', $txn->id) }}">
                                            {{ $txn->transaction_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($txn->description, 30) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $txn->transaction_type == 'deposit' ? 'success' : 
                                            ($txn->transaction_type == 'withdrawal' ? 'danger' : 
                                            ($txn->transaction_type == 'transfer' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($txn->transaction_type) }}
                                        </span>
                                    </td>
                                    <td class="text-end {{ $txn->transaction_type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $txn->transaction_type == 'deposit' ? '+' : '-' }}
                                        {{ number_format($txn->amount, 2) }}
                                    </td>
                                    <td class="text-end">{{ number_format($txn->balance_after, 2) }}</td>
                                    <td>{!! $txn->status_badge !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">
                                        <p class="text-muted mb-0">No transactions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('finance.bank.transactions.deposit.create') }}?account_id={{ $account->id }}" 
                           class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-arrow-down mb-2 d-block"></i>
                            <span>Make Deposit</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('finance.bank.transactions.withdrawal.create') }}?account_id={{ $account->id }}" 
                           class="btn btn-outline-danger w-100 py-3">
                            <i class="fas fa-arrow-up mb-2 d-block"></i>
                            <span>Make Withdrawal</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('finance.bank.transactions.transfer') }}?from={{ $account->id }}" 
                           class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-exchange-alt mb-2 d-block"></i>
                            <span>Transfer Funds</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection