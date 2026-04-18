@extends('layouts.financecontroller')

@section('title', 'Add Bank Account')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add Bank Account</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.index') }}">Bank Accounts</a></li>
                <li class="breadcrumb-item active">Add Account</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.accounts.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Bank Information -->
                    <div class="form-section">
                        <h5 class="section-title">Bank Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label required">Bank Name</label>
                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name" value="{{ old('bank_name') }}" 
                                       placeholder="e.g., NMB, CRDB, NBC" required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="branch" class="form-label">Branch</label>
                                <input type="text" class="form-control @error('branch') is-invalid @enderror" 
                                       id="branch" name="branch" value="{{ old('branch') }}" 
                                       placeholder="e.g., Mlimani City">
                                @error('branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Account Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="account_name" class="form-label required">Account Name</label>
                                <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                       id="account_name" name="account_name" value="{{ old('account_name') }}" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label required">Account Number</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="swift_code" class="form-label">SWIFT Code</label>
                                <input type="text" class="form-control @error('swift_code') is-invalid @enderror" 
                                       id="swift_code" name="swift_code" value="{{ old('swift_code') }}">
                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label required">Currency</label>
                                <select class="form-select @error('currency') is-invalid @enderror" 
                                        id="currency" name="currency" required>
                                    <option value="">Select Currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency }}" {{ old('currency') == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Opening Balance -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Opening Balance</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="opening_balance" class="form-label">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="currency-symbol">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" 
                                           id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                </div>
                                <small class="text-muted">Initial balance for this account</small>
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_default" name="is_default" value="1" 
                                           {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Set as Default Account
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Additional Information</h5>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description/Notes</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    Bank Account Rules
                                </h6>
                                <ul class="small text-muted">
                                    <li>Account number must be unique</li>
                                    <li>Opening balance can be zero or more</li>
                                    <li>Only one account can be default</li>
                                    <li>You can have multiple accounts in different currencies</li>
                                </ul>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Default account will be used for automatic transactions
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Bank Account
                </button>
                <a href="{{ route('finance.bank.accounts.index') }}" class="btn btn-secondary">
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
    $('#currency').change(function() {
        $('#currency-symbol').text($(this).val());
    });
});
</script>
@endpush