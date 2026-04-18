@extends('layouts.financecontroller')

@section('title', 'Create Budget Year')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Budget Year</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.budget.years.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('finance.budget.years.store') }}" method="POST" id="budgetYearForm">
                @csrf
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Budget Year Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Budget Year Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $nextName) }}" 
                                       placeholder="e.g., 2025/2026" required>
                                <small class="text-muted">Format: YYYY/YYYY</small>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm @error('start_date') is-invalid @enderror" 
                                       name="start_date" value="{{ old('start_date', date('Y-01-01')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm @error('end_date') is-invalid @enderror" 
                                       name="end_date" value="{{ old('end_date', date('Y-12-31')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Total Budget (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm @error('total_budget') is-invalid @enderror" 
                                       name="total_budget" value="{{ old('total_budget') }}" 
                                       min="0" step="1000" required>
                                @error('total_budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label small mb-1">Notes</label>
                                <textarea class="form-control form-control-sm @error('notes') is-invalid @enderror" 
                                          name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card border-0 shadow-sm mb-3 bg-light">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2"><i class="feather-info"></i> Budget Year Information</h6>
                        <p class="small text-muted mb-1">
                            • Budget years are created in <span class="fw-semibold">DRAFT</span> status.
                        </p>
                        <p class="small text-muted mb-1">
                            • After creating, you can add department allocations and budget items.
                        </p>
                        <p class="small text-muted mb-0">
                            • Once all allocations are done, submit for approval workflow.
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.budget.years.index') }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Create Budget Year
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection