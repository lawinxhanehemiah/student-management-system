@extends('layouts.financecontroller')

@section('title', 'Department Budget Allocation')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Department Budget Allocation</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $allocation->budget_year_id) }}" class="text-muted">{{ $allocation->budgetYear->name }}</a> > 
                <span>Allocation Details</span>
            </div>
        </div>
        <div class="btn-list">
            @if($allocation->budgetYear->isDraft())
            <button class="btn btn-sm btn-primary" onclick="editAllocation()">
                <i class="feather-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteAllocation()">
                <i class="feather-trash-2"></i> Delete
            </button>
            @endif
            <a href="{{ route('finance.budget.years.show', $allocation->budget_year_id) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Allocation Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Department</span>
                            <h6 class="mb-0 fw-semibold">{{ $allocation->department->name }}</h6>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-users text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Category</span>
                            <h6 class="mb-0">
                                <span class="badge bg-{{ $allocation->category->type_color }}">
                                    {{ $allocation->category->name }}
                                </span>
                            </h6>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-tag text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Allocated</span>
                            <h4 class="mb-0 fw-semibold text-primary">{{ $allocation->formatted_allocated }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-dollar-sign text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Utilized</span>
                            <h4 class="mb-0 fw-semibold text-warning">{{ $allocation->formatted_utilized }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-trending-up text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Utilization Progress</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Progress</span>
                        <span class="small fw-semibold">{{ $allocation->percentage_utilized }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-{{ $allocation->status_color }}" 
                             style="width: {{ min($allocation->percentage_utilized, 100) }}%"></div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <span class="d-block small text-muted">Remaining</span>
                                <span class="fw-semibold {{ $allocation->remaining_amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $allocation->formatted_remaining }}
                                </span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <span class="d-block small text-muted">Utilized</span>
                                <span class="fw-semibold text-warning">{{ $allocation->formatted_utilized }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <span class="d-block small text-muted">Allocated</span>
                                <span class="fw-semibold text-primary">{{ $allocation->formatted_allocated }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Budget Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allocation->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $item->formatted_unit_price }}</td>
                                    <td class="text-end fw-semibold">{{ $item->formatted_total }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-2">
                                        <p class="text-muted small mb-0">No items for this allocation</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($allocation->notes)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Notes</h6>
        </div>
        <div class="card-body">
            <p class="small">{{ $allocation->notes }}</p>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function editAllocation() {
    // Implement edit functionality or redirect to edit page
    alert('Edit functionality coming soon');
}

function deleteAllocation() {
    if (confirm('Are you sure you want to delete this allocation?')) {
        fetch(`/finance/budget/departments/{{ $allocation->budget_year_id }}/{{ $allocation->id }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Allocation deleted successfully');
                window.location.href = '{{ route("finance.budget.years.show", $allocation->budget_year_id) }}';
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
@endpush
@endsection