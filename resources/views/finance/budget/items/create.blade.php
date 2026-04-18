@extends('layouts.financecontroller')

@section('title', 'Create Budget Item')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Budget Item</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <a href="{{ route('finance.budget.items.index', $budgetYear->id) }}" class="text-muted">Items</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.budget.items.index', $budgetYear->id) }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('finance.budget.items.store', $budgetYear->id) }}" method="POST" id="itemForm">
                @csrf
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Item Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Department <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('department_id') is-invalid @enderror" 
                                        name="department_id" id="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $selectedDepartment) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Budget Category <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('budget_category_id') is-invalid @enderror" 
                                        name="budget_category_id" id="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('budget_category_id', $selectedCategory) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('budget_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label small mb-1">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                       name="description" value="{{ old('description') }}" 
                                       placeholder="e.g., Office Supplies, Equipment, etc." required>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm @error('quantity') is-invalid @enderror" 
                                       name="quantity" id="quantity" value="{{ old('quantity', 1) }}" 
                                       min="1" step="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Unit Price (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm @error('unit_price') is-invalid @enderror" 
                                       name="unit_price" id="unit_price" value="{{ old('unit_price') }}" 
                                       min="0" step="1000" required>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Total Amount</label>
                                <input type="text" class="form-control form-control-sm bg-light" 
                                       id="total_amount" readonly>
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label small mb-1">Justification</label>
                                <textarea class="form-control form-control-sm @error('justification') is-invalid @enderror" 
                                          name="justification" rows="3">{{ old('justification') }}</textarea>
                                <small class="text-muted">Explain why this item is needed</small>
                                @error('justification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Allocation Info -->
                <div class="card border-0 shadow-sm mb-3 bg-light">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2"><i class="feather-info"></i> Department Allocation</h6>
                        <p class="small text-muted mb-1" id="allocationInfo">
                            Select department and category to view current allocation
                        </p>
                        <div id="allocationDetails" style="display: none;">
                            <div class="d-flex justify-content-between mt-2">
                                <span>Current Allocation:</span>
                                <span class="fw-semibold" id="currentAllocation">TZS 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Utilized:</span>
                                <span class="fw-semibold" id="utilizedAmount">TZS 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Remaining:</span>
                                <span class="fw-semibold" id="remainingAmount">TZS 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.budget.items.index', $budgetYear->id) }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Create Budget Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('quantity').addEventListener('input', calculateTotal);
document.getElementById('unit_price').addEventListener('input', calculateTotal);
document.getElementById('department_id').addEventListener('change', loadAllocation);
document.getElementById('category_id').addEventListener('change', loadAllocation);

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const price = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * price;
    
    document.getElementById('total_amount').value = 'TZS ' + total.toLocaleString();
}

function loadAllocation() {
    const deptId = document.getElementById('department_id').value;
    const catId = document.getElementById('category_id').value;
    
    if (deptId && catId) {
        // Fetch allocation data
        fetch(`/api/budget/{{ $budgetYear->id }}/allocation?department_id=${deptId}&category_id=${catId}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    document.getElementById('allocationDetails').style.display = 'block';
                    document.getElementById('currentAllocation').innerText = 'TZS ' + data.allocated.toLocaleString();
                    document.getElementById('utilizedAmount').innerText = 'TZS ' + data.utilized.toLocaleString();
                    document.getElementById('remainingAmount').innerText = 'TZS ' + data.remaining.toLocaleString();
                    document.getElementById('allocationInfo').innerText = 'Allocation found for this department and category.';
                } else {
                    document.getElementById('allocationDetails').style.display = 'none';
                    document.getElementById('allocationInfo').innerText = 'No allocation found for this department and category. Please create allocation first.';
                }
            });
    }
}

// Calculate initial total if values exist
calculateTotal();
</script>
@endpush
@endsection