@extends('layouts.financecontroller')

@section('title', 'Edit Budget Year')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Edit Budget Year</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <span>Edit</span>
            </div>
        </div>
        <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('finance.budget.years.update', $budgetYear->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Budget Year Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Budget Year Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $budgetYear->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm @error('start_date') is-invalid @enderror" 
                                       name="start_date" value="{{ old('start_date', $budgetYear->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm @error('end_date') is-invalid @enderror" 
                                       name="end_date" value="{{ old('end_date', $budgetYear->end_date->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Total Budget (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm @error('total_budget') is-invalid @enderror" 
                                       name="total_budget" value="{{ old('total_budget', $budgetYear->total_budget) }}" 
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
                                          name="notes" rows="3">{{ old('notes', $budgetYear->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card border-0 shadow-sm mb-3 bg-warning bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <i class="feather-alert-triangle text-warning"></i>
                            <div>
                                <h6 class="fw-semibold mb-1">Important Note</h6>
                                <p class="small text-muted mb-0">
                                    Editing budget year will not affect existing allocations and items. 
                                    However, changing total budget may affect allocation calculations.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Update Budget Year
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection