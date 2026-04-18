@extends('layouts.financecontroller')

@section('title', 'Create Account')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Create New Account</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.chart-of-accounts.index') }}">Chart of Accounts</a></li>
                <li class="breadcrumb-item active">Create Account</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.general-ledger.chart-of-accounts.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Basic Information</h5>
                        
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
                                <label for="account_type" class="form-label required">Account Type</label>
                                <select class="form-select @error('account_type') is-invalid @enderror" 
                                        id="account_type" name="account_type" required>
                                    <option value="">Select Type</option>
                                    @foreach($accountTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('account_type') == $key ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $type => $typeCategories)
                                        <optgroup label="{{ $accountTypes[$type] }}" class="category-group" data-type="{{ $type }}">
                                            @foreach($typeCategories as $key => $category)
                                                <option value="{{ $key }}" 
                                                    data-type="{{ $type }}"
                                                    {{ old('category') == $key ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="parent_code" class="form-label">Parent Account</label>
                                <select class="form-select @error('parent_code') is-invalid @enderror" 
                                        id="parent_code" name="parent_code">
                                    <option value="">None (Top Level)</option>
                                    @foreach($parentAccounts as $parent)
                                        <option value="{{ $parent->account_code }}" 
                                            {{ old('parent_code') == $parent->account_code ? 'selected' : '' }}>
                                            {{ $parent->account_code }} - {{ $parent->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_header" name="is_header" value="1" 
                                           {{ old('is_header') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_header">
                                        This is a Header Account (Cannot post transactions)
                                    </label>
                                </div>
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
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" 
                                           id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                </div>
                                <small class="text-muted">Only for new accounts without transactions</small>
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Additional Information</h5>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
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
                                    Account Code Generation
                                </h6>
                                <p class="small text-muted mb-0">
                                    Account code will be automatically generated based on:
                                </p>
                                <ul class="small text-muted mt-2">
                                    <li>Account type (1-5 prefix)</li>
                                    <li>Parent account hierarchy</li>
                                    <li>Sequential numbering</li>
                                </ul>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Example: 1-01-001 (Asset > Current Asset > Cash)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Account
                </button>
                <a href="{{ route('finance.general-ledger.chart-of-accounts.index') }}" class="btn btn-secondary">
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
    // Filter categories based on selected account type
    $('#account_type').change(function() {
        const selectedType = $(this).val();
        
        // Hide all option groups
        $('.category-group').hide();
        
        // Show only matching group
        if(selectedType) {
            $(`.category-group[data-type="${selectedType}"]`).show();
            $('#category').val('');
        } else {
            $('.category-group').show();
        }
    });

    // Trigger change on load if type is selected
    if($('#account_type').val()) {
        $('#account_type').trigger('change');
    }
});
</script>
@endpush