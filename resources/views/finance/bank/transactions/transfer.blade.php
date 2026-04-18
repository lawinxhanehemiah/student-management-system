@extends('layouts.financecontroller')

@section('title', 'Transfer Funds')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Transfer Funds</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.transactions.index') }}">Transactions</a></li>
                <li class="breadcrumb-item active">Transfer</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.transactions.transfer.post') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Transfer Information -->
                    <div class="form-section">
                        <h5 class="section-title">Transfer Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="from_account_id" class="form-label required">From Account</label>
                                <select class="form-select @error('from_account_id') is-invalid @enderror" 
                                        id="from_account_id" name="from_account_id" required>
                                    <option value="">Select Source Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ request('from') == $account->id ? 'selected' : '' }}
                                            {{ old('from_account_id') == $account->id ? 'selected' : '' }}
                                            data-balance="{{ $account->current_balance }}">
                                            {{ $account->bank_name }} - {{ $account->account_name }} 
                                            ({{ $account->account_number }}) - Bal: {{ number_format($account->current_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="to_account_id" class="form-label required">To Account</label>
                                <select class="form-select @error('to_account_id') is-invalid @enderror" 
                                        id="to_account_id" name="to_account_id" required>
                                    <option value="">Select Destination Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->bank_name }} - {{ $account->account_name }} 
                                            ({{ $account->account_number }}) - Bal: {{ number_format($account->current_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_account_id')
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
                                <label for="transfer_date" class="form-label required">Transfer Date</label>
                                <input type="date" class="form-control @error('transfer_date') is-invalid @enderror" 
                                       id="transfer_date" name="transfer_date" 
                                       value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                                @error('transfer_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reference" class="form-label">Reference</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                       id="reference" name="reference" value="{{ old('reference') }}" 
                                       placeholder="e.g., Transfer reference, Authorization code">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label required">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="e.g., Funds transfer for operations, Salary transfer" required>{{ old('description') }}</textarea>
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
                                    Transfer Information
                                </h6>
                                <ul class="small text-muted">
                                    <li>Transfer moves funds between your accounts</li>
                                    <li>Both accounts must be active</li>
                                    <li>Source and destination cannot be the same</li>
                                    <li>Check available balance in source account</li>
                                </ul>
                                <hr>
                                <div id="fromBalanceInfo" style="display: none;">
                                    <div class="mb-2">
                                        <strong>From Account Balance:</strong>
                                        <span id="fromBalance" class="text-primary"></span>
                                    </div>
                                    <div id="transferWarning" class="text-danger" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Amount exceeds available balance!
                                    </div>
                                </div>
                                <div id="sameAccountWarning" class="text-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Source and destination accounts cannot be the same!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-info" id="submitBtn">
                    <i class="fas fa-exchange-alt me-2"></i>Complete Transfer
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
    $('#from_account_id').change(function() {
        const selected = $(this).find('option:selected');
        const balance = parseFloat(selected.data('balance') || 0);
        
        if (balance > 0) {
            $('#fromBalance').text(balance.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#fromBalanceInfo').show();
            validateTransfer();
        } else {
            $('#fromBalanceInfo').hide();
        }
        
        checkSameAccount();
    });

    $('#to_account_id').change(function() {
        checkSameAccount();
    });

    $('#amount').on('input', function() {
        validateTransfer();
    });

    function checkSameAccount() {
        const fromId = $('#from_account_id').val();
        const toId = $('#to_account_id').val();
        
        if (fromId && toId && fromId === toId) {
            $('#sameAccountWarning').show();
            $('#submitBtn').prop('disabled', true);
        } else {
            $('#sameAccountWarning').hide();
            validateTransfer();
        }
    }

    function validateTransfer() {
        const amount = parseFloat($('#amount').val()) || 0;
        const selected = $('#from_account_id').find('option:selected');
        const balance = parseFloat(selected.data('balance') || 0);
        const fromId = $('#from_account_id').val();
        const toId = $('#to_account_id').val();

        if (fromId && toId && fromId !== toId && amount > balance) {
            $('#transferWarning').show();
            $('#submitBtn').prop('disabled', true);
        } else if (fromId && toId && fromId !== toId && amount <= balance) {
            $('#transferWarning').hide();
            $('#submitBtn').prop('disabled', false);
        }
    }

    // Trigger on page load if accounts are selected
    if ($('#from_account_id').val()) {
        $('#from_account_id').trigger('change');
    }
});
</script>
@endpush