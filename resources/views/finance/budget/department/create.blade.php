@extends('layouts.financecontroller')

@section('title', 'Add Department Allocation')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Add Department Allocation</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <span>Add Allocation</span>
            </div>
        </div>
        <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('finance.budget.departments.store', $budgetYear->id) }}" method="POST" id="allocationForm">
                @csrf
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Budget Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Budget Year</label>
                                <input type="text" class="form-control form-control-sm bg-light" 
                                       value="{{ $budgetYear->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Total Budget</label>
                                <input type="text" class="form-control form-control-sm bg-light" 
                                       value="TZS {{ number_format($budgetYear->total_budget, 0) }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Remaining Budget</label>
                                <input type="text" class="form-control form-control-sm bg-light text-success" 
                                       value="TZS {{ number_format($budgetYear->total_budget - $budgetYear->total_allocated, 0) }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Add Multiple Allocations</h6>
                    </div>
                    <div class="card-body">
                        <div id="allocations-container">
                            <!-- Allocation rows will be added here -->
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-primary" onclick="addAllocationRow()">
                                <i class="feather-plus"></i> Add Allocation Row
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="card border-0 shadow-sm mb-3 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total Allocated:</span>
                            <span class="fw-bold fs-5 text-primary" id="totalAllocated">TZS 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-semibold">Remaining Budget:</span>
                            <span class="fw-bold fs-5 text-success" id="remainingAfterAllocation">
                                TZS {{ number_format($budgetYear->total_budget - $budgetYear->total_allocated, 0) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Save Allocations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let rowCount = 0;
const departments = @json($departments);
const categories = @json($categories);
const remainingBudget = {{ $budgetYear->total_budget - $budgetYear->total_allocated }};

function addAllocationRow(data = null) {
    const container = document.getElementById('allocations-container');
    const rowId = `row_${rowCount}`;
    
    let departmentOptions = '<option value="">Select Department</option>';
    departments.forEach(dept => {
        departmentOptions += `<option value="${dept.id}" ${data?.department_id == dept.id ? 'selected' : ''}>${dept.name}</option>`;
    });
    
    let categoryOptions = '<option value="">Select Category</option>';
    categories.forEach(cat => {
        categoryOptions += `<option value="${cat.id}" ${data?.category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`;
    });
    
    const row = document.createElement('div');
    row.className = 'allocation-row border rounded p-3 mb-3';
    row.id = rowId;
    row.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Allocation #${rowCount + 1}</h6>
            <button type="button" class="btn btn-sm btn-icon btn-light text-danger" onclick="removeRow('${rowId}')">
                <i class="feather-x"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-4">
                <select class="form-select form-select-sm" name="allocations[${rowCount}][department_id]" required>
                    ${departmentOptions}
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" name="allocations[${rowCount}][category_id]" required>
                    ${categoryOptions}
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control form-control-sm amount-input" 
                       name="allocations[${rowCount}][amount]" 
                       value="${data?.amount || ''}" 
                       placeholder="Amount" 
                       min="0" step="1000" 
                       onchange="updateTotal()" required>
            </div>
            <div class="col-md-12 mt-2">
                <textarea class="form-control form-control-sm" 
                          name="allocations[${rowCount}][notes]" 
                          placeholder="Notes (optional)">${data?.notes || ''}</textarea>
            </div>
        </div>
    `;
    
    container.appendChild(row);
    rowCount++;
    updateTotal();
}

function removeRow(rowId) {
    document.getElementById(rowId).remove();
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.amount-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalAllocated').innerText = 'TZS ' + total.toLocaleString();
    
    const remaining = remainingBudget - total;
    const remainingElement = document.getElementById('remainingAfterAllocation');
    remainingElement.innerText = 'TZS ' + remaining.toLocaleString();
    
    if (remaining < 0) {
        remainingElement.className = 'fw-bold fs-5 text-danger';
    } else {
        remainingElement.className = 'fw-bold fs-5 text-success';
    }
}

// Add first row by default
addAllocationRow();
</script>
@endpush
@endsection