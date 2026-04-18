@extends('layouts.financecontroller')

@section('title', 'Cashbook')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Cashbook</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Bank & Cash</a></li>
                <li class="breadcrumb-item active">Cashbook</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($selectedAccount)
            <a href="{{ route('finance.bank.cashbook.export', request()->query()) }}" class="btn btn-secondary">
                <i class="fas fa-download me-2"></i>Export
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.bank.cashbook.index') }}" method="GET" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="bank_account_id" class="form-label required">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                        <option value="">Select Account...</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccount == $acc->id ? 'selected' : '' }}>
                                {{ $acc->bank_name }} - {{ $acc->account_name }} ({{ $acc->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-search me-2"></i>View Cashbook
                    </button>
                    <a href="{{ route('finance.bank.cashbook.index') }}" class="btn btn-reset">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        @if($selectedAccount && $account)
            <!-- Cashbook Header -->
            <div class="text-center mb-4">
                <h3>CASHBOOK</h3>
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
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Opening Balance</h6>
                            <h4 class="text-primary">{{ number_format($totals['opening'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Debits (In)</h6>
                            <h4 class="text-success">{{ number_format($totals['debit'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Credits (Out)</h6>
                            <h4 class="text-danger">{{ number_format($totals['credit'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Closing Balance</h6>
                            <h4 class="text-info">{{ number_format($totals['closing'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cashbook Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="cashbookTable">
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
                            <td class="text-end fw-bold">{{ number_format($totals['opening'], 2) }}</td>
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
                                <td>{{ $txn->metadata['reference'] ?? '-' }}</td>
                                <td class="text-end text-success">{{ $txn->debit > 0 ? number_format($txn->debit, 2) : '-' }}</td>
                                <td class="text-end text-danger">{{ $txn->credit > 0 ? number_format($txn->credit, 2) : '-' }}</td>
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
                            <td class="text-end">{{ number_format($totals['closing'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Notes -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Cashbook Notes:</h6>
                            <ul class="small text-muted mb-0">
                                <li>Debit = Money coming IN to the account (Deposits)</li>
                                <li>Credit = Money going OUT of the account (Withdrawals)</li>
                                <li>Opening balance is as at {{ date('d M Y', strtotime($startDate)) }}</li>
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
                                    <td class="text-end">{{ number_format($totals['opening'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Inflows:</td>
                                    <td class="text-end text-success">{{ number_format($totals['debit'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Outflows:</td>
                                    <td class="text-end text-danger">{{ number_format($totals['credit'], 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Net Movement:</td>
                                    <td class="text-end">{{ number_format($totals['debit'] - $totals['credit'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <img src="{{ asset('assets/img/select-account.svg') }}" alt="Select Account" height="150">
                <h5 class="mt-3">Select a bank account to view cashbook</h5>
                <p class="text-muted">Choose an account from the dropdown above to see the cashbook</p>
            </div>
        @endif
    </div>
</div>
@endsection