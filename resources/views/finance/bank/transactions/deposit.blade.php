@extends('layouts.financecontroller')

@section('title', 'Make Deposit')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Make Deposit</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.transactions.index') }}">Transactions</a></li>
                <li class="breadcrumb-item active">Deposit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.transactions.deposit.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Deposit Information -->
                    <div class="form-section">
                        <h5 class="section-title">Deposit Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_account_id" class="form-label required">Select Bank Account</label>
                                <select class="form-select @error('bank_account_id') is-invalid @enderror" 
                                        id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Choose Account...</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ request('account_id') == $account->id ? 'selected' : '' }}
                                            {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_name }} 
                                            ({{ $account->account_number }}) - Bal: {{ number_format($account->current_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label required">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount') }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transaction_date" class="form-label required">Transaction Date</label>
                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" name="transaction_date" 
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reference" class="form-label">Reference Number</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                       id="reference" name="reference" value="{{ old('reference') }}" 
                                       placeholder="e.g., Deposit slip #, Transaction ID">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label required">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="e.g., Cash deposit, Cheque deposit, Interest earned" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Link to Payment -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Link to Payment (Optional)</h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="payment_id" class="form-label">Payment ID</label>
                                <input type="number" class="form-control @error('payment_id') is-invalid @enderror" 
                                       id="payment_id" name="payment_id" value="{{ old('payment_id') }}" 
                                       placeholder="Enter payment ID if this deposit is from a student payment">
                                <small class="text-muted">Link this deposit to an existing payment record</small>
                                @error('payment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Info Card -->
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Deposit Information
                                </h6>
                                <ul class="small text-muted">
                                    <li>Deposit increases account balance</li>
                                    <li>Amount must be greater than zero</li>
                                    <li>Transaction date cannot be in the future</li>
                                    <li>Reference number helps with reconciliation</li>
                                </ul>
                                <hr>
                                <div id="selectedAccountInfo" class="small" style="display: none;">
                                    <strong>Selected Account Balance:</strong>
                                    <span id="accountBalance"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Record Deposit
                </button>
                <a href="{{ route('finance.bank.transactions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#bank_account_id').change(function() {
        const selected = $(this).find('option:selected');
        const text = selected.text();
        
        // Extract balance from option text
        const match = text.match(/Bal: ([\d,]+\.?\d*)/);
        if (match) {
            const balance = match[1];
            $('#accountBalance').text(balance);
            $('#selectedAccountInfo').show();
        } else {
            $('#selectedAccountInfo').hide();
        }
    });

    // Trigger on page load if account is selected
    if ($('#bank_account_id').val()) {
        $('#bank_account_id').trigger('change');
    }
});
</script>
@endpush