@extends('layouts.financecontroller')

@section('title', 'New Bank Reconciliation')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>New Bank Reconciliation</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.reconciliation.index') }}">Reconciliation</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.reconciliation.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Select Period -->
                    <div class="form-section">
                        <h5 class="section-title">Select Period</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bank_account_id" class="form-label required">Bank Account</label>
                                <select class="form-select @error('bank_account_id') is-invalid @enderror" 
                                        id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Select Account...</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ $selectedAccount == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="month" class="form-label required">Month</label>
                                <select class="form-select" id="month" name="month" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label required">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    @for($y = date('Y'); $y >= date('Y')-3; $y--)
                                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="action" value="preview" class="btn btn-info">
                                    <i class="fas fa-eye me-2"></i>Preview Transactions
                                </button>
                            </div>
                        </div>
                    </div>

                    @if(isset($account) && $account)
                        <!-- Statement Information -->
                        <div class="form-section mt-4">
                            <h5 class="section-title">Statement Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statement_date" class="form-label required">Statement Date</label>
                                    <input type="date" class="form-control" id="statement_date" 
                                           name="statement_date" value="{{ $selectedYear }}-{{ str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) }}-{{ now()->endOfMonth()->day }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="statement_balance" class="form-label required">Statement Balance</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" step="0.01" class="form-control" 
                                               id="statement_balance" name="statement_balance" 
                                               value="{{ $closingBalance }}" required>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- System Balance Summary -->
                        <div class="alert alert-info mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Opening Balance:</strong> 
                                    {{ number_format($openingBalance, 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Debits:</strong> 
                                    {{ number_format($transactions->whereIn('transaction_type', ['deposit', 'opening_balance'])->sum('amount'), 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Credits:</strong> 
                                    {{ number_format($transactions->whereIn('transaction_type', ['withdrawal', 'transfer'])->sum('amount'), 2) }}
                                </div>
                                <div class="col-md-4 mt-2">
                                    <strong>System Balance:</strong> 
                                    {{ number_format($closingBalance, 2) }}
                                </div>
                                <div class="col-md-8 mt-2">
                                    <strong>Instructions:</strong> 
                                    Enter your bank statement balance above and check transactions
                                </div>
                            </div>
                        </div>

                        <!-- Transactions List -->
                        <div class="form-section mt-4">
                            <h5 class="section-title">Transactions for {{ DateTime::createFromFormat('!m', $selectedMonth)->format('F') }} {{ $selectedYear }}</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">Match</th>
                                            <th>Date</th>
                                            <th>Transaction #</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions as $txn)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="transactions[{{ $loop->index }}][selected]" 
                                                           value="1" class="form-check-input" checked>
                                                    <input type="hidden" name="transactions[{{ $loop->index }}][id]" 
                                                           value="{{ $txn->id }}">
                                                </td>
                                                <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                                <td>{{ $txn->transaction_number }}</td>
                                                <td>{{ Str::limit($txn->description, 30) }}</td>
                                                <td class="text-end {{ $txn->transaction_type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $txn->transaction_type == 'deposit' ? '+' : '-' }}
                                                    {{ number_format($txn->amount, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $txn->status == 'completed' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($txn->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3">
                                                    No transactions found for this period
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <button type="submit" name="action" value="save" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Reconciliation
                            </button>
                            <a href="{{ route('finance.bank.reconciliation.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Reconciliation Guide
                                </h6>
                                <ul class="small text-muted">
                                    <li>Select bank account and period</li>
                                    <li>Enter ending balance from bank statement</li>
                                    <li>Check/uncheck transactions that match</li>
                                    <li>Save to start reconciliation</li>
                                    <li>Complete after all differences resolved</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection