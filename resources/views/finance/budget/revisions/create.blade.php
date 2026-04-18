
@extends('layouts.financecontroller')

@section('title', 'Request Budget Revision')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Request Budget Revision</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <a href="{{ route('finance.budget.revisions.index', $budgetYear->id) }}" class="text-muted">Revisions</a> > 
                <span>New Revision</span>
            </div>
        </div>
        <a href="{{ route('finance.budget.revisions.index', $budgetYear->id) }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('finance.budget.revisions.store', $budgetYear->id) }}" method="POST" id="revisionForm">
                @csrf
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Revision Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Department <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('department_id') is-invalid @enderror" 
                                        name="department_id" id="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Revision Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('type') is-invalid @enderror" 
                                        name="type" id="type" required>
                                    <option value="">Select Type</option>
                                    <option value="increase" {{ old('type') == 'increase' ? 'selected' : '' }}>Budget Increase</option>
                                    <option value="decrease" {{ old('type') == 'decrease' ? 'selected' : '' }}>Budget Decrease</option>
                                    <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>Transfer to Another Department</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2" id="transferFields" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label small mb-1">Target Department <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="target_department_id" id="target_department_id">
                                    <option value="">Select Target Department</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('target_department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm @error('amount') is-invalid @enderror" 
                                       name="amount" id="amount" value="{{ old('amount') }}" 
                                       min="1" step="1000" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label small mb-1">Reason for Revision <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-sm @error('reason') is-invalid @enderror" 
                                          name="reason" rows="3" required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Allocation Info -->
                <div class="card border-0 shadow-sm mb-3 bg-light" id="allocationCard" style="display: none;">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2"><i class="feather-info"></i> Current Allocation</h6>
                        <div id="allocationDetails"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('finance.budget.revisions.index', $budgetYear->id) }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-warning">
                        Submit Revision Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const transferFields = document.getElementById('transferFields');
    
    if (type === 'transfer') {
        transferFields.style.display = 'flex';
    } else {
        transferFields.style.display = 'none';
    }
});

document.getElementById('department_id').addEventListener('change', loadAllocation);

function loadAllocation() {
    const deptId = document.getElementById('department_id').value;
    
    if (deptId) {
        fetch(`/api/budget/{{ $budgetYear->id }}/department/${deptId}/allocation`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    document.getElementById('allocationCard').style.display = 'block';
                    
                    let html = '<table class="table table-sm small mb-0">';
                    html += '<tr><th>Category</th><th class="text-end">Allocated</th><th class="text-end">Utilized</th><th class="text-end">Remaining</th></tr>';
                    
                    data.allocations.forEach(alloc => {
                        html += `<tr>
                            <td>${alloc.category}</td>
                            <td class="text-end">TZS ${alloc.allocated.toLocaleString()}</td>
                            <td class="text-end">TZS ${alloc.utilized.toLocaleString()}</td>
                            <td class="text-end">TZS ${alloc.remaining.toLocaleString()}</td>
                        </tr>`;
                    });
                    
                    html += '</table>';
                    document.getElementById('allocationDetails').innerHTML = html;
                } else {
                    document.getElementById('allocationCard').style.display = 'none';
                }
            });
    } else {
        document.getElementById('allocationCard').style.display = 'none';
    }
}
</script>
@endpush
@endsection