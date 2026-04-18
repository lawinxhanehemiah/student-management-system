@extends('layouts.financecontroller')

@section('title', 'Edit Bank Account')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Bank Account</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.index') }}">Bank Accounts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.accounts.show', $account->id) }}">{{ $account->account_name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.bank.accounts.update', $account->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <!-- Bank Information -->
                    <div class="form-section">
                        <h5 class="section-title">Bank Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label required">Bank Name</label>
                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="branch" class="form-label">Branch</label>
                                <input type="text" class="form-control @error('branch') is-invalid @enderror" 
                                       id="branch" name="branch" value="{{ old('branch', $account->branch) }}">
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
                                       id="account_name" name="account_name" value="{{ old('account_name', $account->account_name) }}" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_number" class="form-label required">Account Number</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" name="account_number" value="{{ old('account_number', $account->account_number) }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="swift_code" class="form-label">SWIFT Code</label>
                                <input type="text" class="form-control @error('swift_code') is-invalid @enderror" 
                                       id="swift_code" name="swift_code" value="{{ old('swift_code', $account->swift_code) }}">
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
                                        <option value="{{ $currency }}" {{ old('currency', $account->currency) == $currency ? 'selected' : '' }}>
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

                    <!-- Status Settings -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Account Status</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Account is Active
                                    </label>
                                </div>
                                <small class="text-muted">Inactive accounts cannot be used for transactions</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_default" name="is_default" value="1" 
                                           {{ old('is_default', $account->is_default) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Set as Default Account
                                    </label>
                                </div>
                                <small class="text-muted">Default account will be used for automatic transactions</small>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Additional Information</h5>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description/Notes</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $account->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Current Status Card -->
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Current Status
                                </h6>
                                <div class="mb-2">
                                    <strong>Balance:</strong> 
                                    <span class="text-{{ $account->current_balance > 0 ? 'success' : 'danger' }}">
                                        {{ number_format($account->current_balance, 2) }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <strong>Status:</strong> {!! $account->status_badge !!}
                                </div>
                                <div class="mb-2">
                                    <strong>Default:</strong> 
                                    @if($account->is_default)
                                        <span class="badge bg-info">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </div>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Created: {{ $account->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Card -->
                    <div class="info-card mt-3">
                        <div class="card bg-warning bg-opacity-10">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Important Note
                                </h6>
                                <p class="small text-muted mb-0">
                                    Changing account number or bank name may affect existing transactions and reconciliations.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Bank Account
                </button>
                <a href="{{ route('finance.bank.accounts.show', $account->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection