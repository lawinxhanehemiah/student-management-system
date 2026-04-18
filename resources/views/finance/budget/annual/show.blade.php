@extends('layouts.financecontroller')

@section('title', 'Budget Year - ' . $budgetYear->name)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Budget Year Details</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <span>{{ $budgetYear->name }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($budgetYear->isDraft())
            <a href="{{ route('finance.budget.years.edit', $budgetYear->id) }}" class="btn btn-sm btn-primary">
                <i class="feather-edit"></i> Edit
            </a>
            <button class="btn btn-sm btn-info" onclick="submitForApproval()">
                <i class="feather-send"></i> Submit for Approval
            </button>
            @endif
            
            @if($budgetYear->isActive())
            <a href="{{ route('finance.budget.revisions.create', $budgetYear->id) }}" class="btn btn-sm btn-warning">
                <i class="feather-edit"></i> Request Revision
            </a>
            <a href="{{ route('finance.budget.years.vs-actual', $budgetYear->id) }}" class="btn btn-sm btn-info-light">
                <i class="feather-trending-up"></i> Budget vs Actual
            </a>
            <button class="btn btn-sm btn-danger" onclick="closeBudgetYear()">
                <i class="feather-lock"></i> Close Budget
            </button>
            @endif
            
            <a href="{{ route('finance.budget.years.index') }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <span class="text-muted small">Status:</span>
                    <span class="badge bg-{{ $budgetYear->status_color }} ms-2">
                        {{ ucwords($budgetYear->status) }}
                    </span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Budget Year:</span>
                    <span class="fw-semibold ms-2">{{ $budgetYear->name }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Period:</span>
                    <span class="ms-2">
                        {{ $budgetYear->start_date->format('d M Y') }} - 
                        {{ $budgetYear->end_date->format('d M Y') }}
                    </span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Created By:</span>
                    <span class="ms-2">{{ $budgetYear->creator->name ?? 'System' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Budget</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($budgetYear->total_budget, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-dollar-sign text-primary"></i>
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
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($budgetYear->total_allocated, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-pie-chart text-info"></i>
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
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($budgetYear->total_utilized, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-trending-up text-warning"></i>
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
                            <span class="text-muted small">Remaining</span>
                            <h4 class="mb-0 fw-semibold {{ $budgetYear->total_remaining > 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($budgetYear->total_remaining, 0) }}
                            </h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-muted">Overall Progress</span>
                <span class="small fw-semibold">{{ $budgetYear->progress }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $budgetYear->progress >= 100 ? 'bg-danger' : ($budgetYear->progress >= 80 ? 'bg-warning' : 'bg-success') }}" 
                     style="width: {{ min($budgetYear->progress, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="budgetTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="departments-tab" data-bs-toggle="tab" 
                    data-bs-target="#departments" type="button" role="tab">
                Department Allocations
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="items-tab" data-bs-toggle="tab" 
                    data-bs-target="#items" type="button" role="tab">
                Budget Items
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approvals-tab" data-bs-toggle="tab" 
                    data-bs-target="#approvals" type="button" role="tab">
                Approval Workflow
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="revisions-tab" data-bs-toggle="tab" 
                    data-bs-target="#revisions" type="button" role="tab">
                Revisions
            </button>
        </li>
    </ul>

    <div class="tab-content" id="budgetTabsContent">
        <!-- Department Allocations Tab -->
        <div class="tab-pane fade show active" id="departments" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Department Allocations</h6>
                    @if($budgetYear->isDraft())
                    <a href="{{ route('finance.budget.departments.create', $budgetYear->id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="feather-plus"></i> Add Allocation
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th class="text-end">Allocated</th>
                                    <th class="text-end">Utilized</th>
                                    <th class="text-end">Remaining</th>
                                    <th>Progress</th>
                                    @if($budgetYear->isDraft())
                                    <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetYear->departmentBudgets as $alloc)
                                <tr>
                                    <td>{{ $alloc->department->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $alloc->category->type_color }}">
                                            {{ $alloc->category->name }}
                                        </span>
                                    </td>
                                    <td class="text-end">TZS {{ number_format($alloc->allocated_amount, 0) }}</td>
                                    <td class="text-end">TZS {{ number_format($alloc->utilized_amount, 0) }}</td>
                                    <td class="text-end {{ $alloc->remaining_amount > 0 ? 'text-success' : 'text-danger' }}">
                                        TZS {{ number_format($alloc->remaining_amount, 0) }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span>{{ $alloc->percentage_utilized }}%</span>
                                            <div class="progress progress-sm" style="width: 50px; height: 4px;">
                                                <div class="progress-bar bg-{{ $alloc->status_color }}" 
                                                     style="width: {{ min($alloc->percentage_utilized, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @if($budgetYear->isDraft())
                                    <td>
                                        <a href="{{ route('finance.budget.departments.show', [$budgetYear->id, $alloc->id]) }}" 
                                           class="btn btn-sm btn-icon btn-light">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $budgetYear->isDraft() ? '7' : '6' }}" class="text-center py-3">
                                        <p class="text-muted small mb-0">No department allocations yet</p>
                                        @if($budgetYear->isDraft())
                                        <a href="{{ route('finance.budget.departments.create', $budgetYear->id) }}" 
                                           class="btn btn-sm btn-primary mt-2">
                                            Add First Allocation
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Items Tab -->
        <div class="tab-pane fade" id="items" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Budget Items</h6>
                    @if($budgetYear->isDraft())
                    <a href="{{ route('finance.budget.items.create', $budgetYear->id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="feather-plus"></i> Add Item
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                    @if($budgetYear->isDraft())
                                    <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetYear->budgetItems as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->department->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->category->type_color }}">
                                            {{ $item->category->name }}
                                        </span>
                                    </td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">TZS {{ number_format($item->unit_price, 0) }}</td>
                                    <td class="text-end fw-semibold">TZS {{ number_format($item->total_amount, 0) }}</td>
                                    @if($budgetYear->isDraft())
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-light text-danger" 
                                                onclick="deleteItem({{ $item->id }})">
                                            <i class="feather-trash-2"></i>
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $budgetYear->isDraft() ? '8' : '7' }}" class="text-center py-3">
                                        <p class="text-muted small mb-0">No budget items yet</p>
                                        @if($budgetYear->isDraft())
                                        <a href="{{ route('finance.budget.items.create', $budgetYear->id) }}" 
                                           class="btn btn-sm btn-primary mt-2">
                                            Add First Item
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Workflow Tab -->
        <div class="tab-pane fade" id="approvals" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Approval Workflow</h6>
                </div>
                <div class="card-body">
                    @if($budgetYear->approvals->isEmpty())
                        <p class="text-muted small text-center py-3">No approval records yet</p>
                    @else
                        <div class="timeline">
                            @foreach($budgetYear->approvals as $approval)
                            <div class="d-flex gap-3 mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-sm bg-{{ $approval->status_color }} rounded-circle">
                                        <i class="feather-{{ $approval->status == 'approved' ? 'check' : ($approval->status == 'rejected' ? 'x' : 'clock') }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $approval->level_text }}</h6>
                                    <p class="small text-muted mb-1">
                                        Status: <span class="badge bg-{{ $approval->status_color }}">{{ ucwords($approval->status) }}</span>
                                    </p>
                                    @if($approval->comments)
                                    <p class="small text-muted mb-0">Comment: {{ $approval->comments }}</p>
                                    @endif
                                    @if($approval->approved_at)
                                    <p class="small text-muted">
                                        {{ $approval->approver->name ?? 'N/A' }} - {{ $approval->approved_at->format('d/m/Y H:i') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Revisions Tab -->
        <div class="tab-pane fade" id="revisions" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Budget Revisions</h6>
                    @if($budgetYear->isActive())
                    <a href="{{ route('finance.budget.revisions.create', $budgetYear->id) }}" 
                       class="btn btn-sm btn-warning">
                        <i class="feather-edit"></i> New Revision
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Revision #</th>
                                    <th>Date</th>
                                    <th>Department</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($budgetYear->revisions as $revision)
                                <tr>
                                    <td>{{ $revision->revision_number }}</td>
                                    <td>{{ $revision->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $revision->department->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $revision->type_color }}">
                                            {{ $revision->type_text }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $revision->formatted_amount }}</td>
                                    <td>{{ $revision->reason }}</td>
                                    <td>
                                        <span class="badge bg-{{ $revision->status_color }}">
                                            {{ ucwords($revision->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-light" 
                                                onclick="viewRevision({{ $revision->id }})">
                                            <i class="feather-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3">
                                        <p class="text-muted small mb-0">No revisions yet</p>
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
</div>

@push('scripts')
<script>
function submitForApproval() {
    if (confirm('Submit this budget year for approval?')) {
        fetch('{{ route("finance.budget.years.submit", $budgetYear->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Budget year submitted for approval');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function closeBudgetYear() {
    if (confirm('Are you sure you want to close this budget year? This action cannot be undone.')) {
        fetch('{{ route("finance.budget.years.close", $budgetYear->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Budget year closed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this budget item?')) {
        fetch(`/finance/budget/items/{{ $budgetYear->id }}/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function viewRevision(revisionId) {
    // Implement view revision modal or redirect
    alert('View revision ' + revisionId);
}
</script>
@endpush
@endsection