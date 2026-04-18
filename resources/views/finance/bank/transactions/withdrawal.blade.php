@extends('layouts.financecontroller')

@section('title', 'Make Withdrawal')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Make Withdrawal</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.transactions.index') }}">Transactions</a></li>
                <li class="breadcrumb-item active">Withdrawal</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.transactions.withdrawal.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Withdrawal Information -->
                    <div class="form-section">
                        <h5 class="section-title">Withdrawal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_account_id" class="form-label required">Select Bank Account</label>
                                <select class="form-select @error('bank_account_id') is-invalid @enderror" 
                                        id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Choose Account...</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ request('account_id') == $account->id ? 'selected' : '' }}
                                            {{ old('bank_account_id') == $account->id ? 'selected' : '' }}
                                            data-balance="{{ $account->current_balance }}">
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
                                <label for="payee" class="form-label required">Payee</label>
                                <input type="text" class="form-control @error('payee') is-invalid @enderror" 
                                       id="payee" name="payee" value="{{ old('payee') }}" 
                                       placeholder="e.g., Supplier name, Employee name" required>
                                @error('payee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label required">Payment Method</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="withdrawal_slip" {{ old('payment_method') == 'withdrawal_slip' ? 'selected' : '' }}>Withdrawal Slip</option>
                                </select>
                                @error('payment_method')
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
                                       placeholder="e.g., Cheque number, Transfer reference">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label required">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="e.g., Supplier payment, Salary payment, Operating expenses" required>{{ old('description') }}</textarea>
                                @error('description')
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
                                    Withdrawal Information
                                </h6>
                                <ul class="small text-muted">
                                    <li>Withdrawal decreases account balance</li>
                                    <li>Amount must be greater than zero</li>
                                    <li>Check available balance before withdrawal</li>
                                    <li>Large withdrawals may require approval</li>
                                </ul>
                                <hr>
                                <div id="balanceInfo" class="small" style="display: none;">
                                    <div class="mb-2">
                                        <strong>Available Balance:</strong>
                                        <span id="availableBalance" class="text-primary"></span>
                                    </div>
                                    <div id="balanceWarning" class="text-danger" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Amount exceeds available balance!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-danger" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Record Withdrawal
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
        const balance = parseFloat(selected.data('balance') || 0);
        
        if (balance > 0) {
            $('#availableBalance').text(balance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#balanceInfo').show();
            checkBalance();
        } else {
            $('#balanceInfo').hide();
        }
    });

    $('#amount').on('input', function() {
        checkBalance();
    });

    function checkBalance() {
        const amount = parseFloat($('#amount').val()) || 0;
        const selected = $('#bank_account_id').find('option:selected');
        const balance = parseFloat(selected.data('balance') || 0);

        if (amount > balance) {
            $('#balanceWarning').show();
            $('#submitBtn').prop('disabled', true);
        } else {
            $('#balanceWarning').hide();
            $('#submitBtn').prop('disabled', false);
        }
    }

    // Trigger on page load if account is selected
    if ($('#bank_account_id').val()) {
        $('#bank_account_id').trigger('change');
    }
});
</script>
@endpush