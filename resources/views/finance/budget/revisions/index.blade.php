@extends('layouts.financecontroller')

@section('title', 'Budget Revisions')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Budget Revisions</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <span>Revisions</span>
            </div>
        </div>
        <div class="btn-list">
            @if($budgetYear->isActive())
            <a href="{{ route('finance.budget.revisions.create', $budgetYear->id) }}" class="btn btn-sm btn-warning">
                <i class="feather-edit"></i> New Revision
            </a>
            @endif
            <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Revisions</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.budget.revisions.index', $budgetYear->id) }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="type">
                            <option value="">All Types</option>
                            <option value="increase" {{ request('type') == 'increase' ? 'selected' : '' }}>Increase</option>
                            <option value="decrease" {{ request('type') == 'decrease' ? 'selected' : '' }}>Decrease</option>
                            <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revisions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Budget Revisions</h6>
            <span class="badge bg-secondary">{{ $revisions->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Revision #</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revisions as $revision)
                        <tr>
                            <td>{{ $revision->revision_number }}</td>
                            <td>{{ $revision->created_at->format('d/m/Y') }}</td>
                            <td>{{ $revision->department->name }}</td>
                            <td>
                                <span class="badge bg-{{ $revision->type_color }}">
                                    {{ $revision->type_text }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">{{ $revision->formatted_amount }}</td>
                            <td>{{ $revision->reason }}</td>
                            <td>
                                <span class="badge bg-{{ $revision->status_color }}">
                                    {{ ucwords($revision->status) }}
                                </span>
                            </td>
                            <td>{{ $revision->requester->name ?? 'N/A' }}</td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-light" 
                                        onclick="viewRevision({{ $revision->id }})">
                                    <i class="feather-eye"></i>
                                </button>
                                @if($revision->status == 'pending')
                                <button class="btn btn-sm btn-icon btn-success" 
                                        onclick="approveRevision({{ $revision->id }})">
                                    <i class="feather-check"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-danger" 
                                        onclick="rejectRevision({{ $revision->id }})">
                                    <i class="feather-x"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No budget revisions found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $revisions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewRevision(id) {
    // Implement view revision details
    alert('View revision ' + id);
}

function approveRevision(id) {
    if (confirm('Approve this revision?')) {
        fetch(`/finance/budget/revisions/{{ $budgetYear->id }}/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Revision approved');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectRevision(id) {
    const reason = prompt('Please enter rejection reason:');
    if (reason) {
        fetch(`/finance/budget/revisions/{{ $budgetYear->id }}/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Revision rejected');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
@endpush
@endsection