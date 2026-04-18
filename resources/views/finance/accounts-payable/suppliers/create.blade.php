@extends('layouts.financecontroller')

@section('title', 'Create Supplier')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create New Supplier</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="text-muted">Suppliers</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('finance.accounts-payable.suppliers.store') }}" method="POST" id="supplierForm">
                @csrf
                
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Tax Number (TIN)</label>
                                <input type="text" class="form-control form-control-sm @error('tax_number') is-invalid @enderror" 
                                       name="tax_number" value="{{ old('tax_number') }}">
                                @error('tax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Payment Terms</label>
                                <select class="form-select form-select-sm @error('payment_terms') is-invalid @enderror" 
                                        name="payment_terms">
                                    <option value="">Select</option>
                                    <option value="cash" {{ old('payment_terms') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="credit_7" {{ old('payment_terms') == 'credit_7' ? 'selected' : '' }}>Credit 7 Days</option>
                                    <option value="credit_15" {{ old('payment_terms') == 'credit_15' ? 'selected' : '' }}>Credit 15 Days</option>
                                    <option value="credit_30" {{ old('payment_terms') == 'credit_30' ? 'selected' : '' }}>Credit 30 Days</option>
                                    <option value="credit_60" {{ old('payment_terms') == 'credit_60' ? 'selected' : '' }}>Credit 60 Days</option>
                                </select>
                                @error('payment_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Contact Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Contact Person</label>
                                <input type="text" class="form-control form-control-sm @error('contact_person') is-invalid @enderror" 
                                       name="contact_person" value="{{ old('contact_person') }}">
                                @error('contact_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Email</label>
                                <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Phone</label>
                                <input type="text" class="form-control form-control-sm @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Address</label>
                                <input type="text" class="form-control form-control-sm @error('address') is-invalid @enderror" 
                                       name="address" value="{{ old('address') }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">City</label>
                                <input type="text" class="form-control form-control-sm @error('city') is-invalid @enderror" 
                                       name="city" value="{{ old('city') }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Country</label>
                                <input type="text" class="form-control form-control-sm @error('country') is-invalid @enderror" 
                                       name="country" value="{{ old('country', 'Tanzania') }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Financial Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Credit Limit (TZS)</label>
                                <input type="number" class="form-control form-control-sm @error('credit_limit') is-invalid @enderror" 
                                       name="credit_limit" value="{{ old('credit_limit', 0) }}" step="1000">
                                @error('credit_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Opening Balance (TZS)</label>
                                <input type="number" class="form-control form-control-sm @error('opening_balance') is-invalid @enderror" 
                                       name="opening_balance" value="{{ old('opening_balance', 0) }}" step="1000">
                                <small class="text-muted">Initial balance if any</small>
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Bank Information (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Bank Name</label>
                                <input type="text" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" 
                                       name="bank_name" value="{{ old('bank_name') }}">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Account Number</label>
                                <input type="text" class="form-control form-control-sm @error('bank_account') is-invalid @enderror" 
                                       name="bank_account" value="{{ old('bank_account') }}">
                                @error('bank_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Branch</label>
                                <input type="text" class="form-control form-control-sm @error('bank_branch') is-invalid @enderror" 
                                       name="bank_branch" value="{{ old('bank_branch') }}">
                                @error('bank_branch')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control form-control-sm @error('notes') is-invalid @enderror" 
                                  name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Create Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection