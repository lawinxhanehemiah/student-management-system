@extends('layouts.financecontroller')

@section('title', 'Contract Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Contract Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.contracts.index') }}">Contracts</a></li>
                <li class="breadcrumb-item active">{{ $contract->contract_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($contract->status == 'draft')
            <a href="{{ route('finance.procurement.contracts.edit', $contract->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <form action="{{ route('finance.procurement.contracts.activate', $contract->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Activate this contract?')">
                    <i class="fas fa-check-circle me-2"></i>Activate
                </button>
            </form>
        @endif
        @if($contract->status == 'active')
            <form action="{{ route('finance.procurement.contracts.complete', $contract->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info" onclick="return confirm('Mark as completed?')">
                    <i class="fas fa-check me-2"></i>Complete
                </button>
            </form>
        @endif
        <a href="{{ route('finance.procurement.contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Contract Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Number:</th>
                        <td><strong>{{ $contract->contract_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>Title:</th>
                        <td>{{ $contract->title }}</td>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <td>{{ $contract->supplier->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'active' => 'success',
                                    'completed' => 'info',
                                    'terminated' => 'danger',
                                    'expired' => 'warning'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$contract->status] }}">
                                {{ ucfirst($contract->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Start Date:</th>
                        <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Remaining Days:</th>
                        <td>
                            @if($contract->remaining_days > 0)
                                <span class="badge bg-info">{{ $contract->remaining_days }} days</span>
                            @else
                                <span class="badge bg-danger">Expired</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Contract Value:</th>
                        <td><strong>{{ number_format($contract->contract_value, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Project Manager:</th>
                        <td>{{ $contract->projectManager->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($contract->tender)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Related Tender</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $contract->tender->tender_number }}</strong></p>
                <p>{{ $contract->tender->title }}</p>
                <a href="{{ route('finance.procurement.tenders.show', $contract->tender->id) }}" class="btn btn-sm btn-info">
                    View Tender
                </a>
            </div>
        </div>
        @endif

        @if($contract->requisition)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Related Requisition</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $contract->requisition->requisition_number }}</strong></p>
                <p>{{ $contract->requisition->title }}</p>
                <a href="{{ route('finance.procurement.requisitions.show', $contract->requisition->id) }}" class="btn btn-sm btn-info">
                    View Requisition
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <!-- Payment Terms -->
        @if($contract->payment_terms)
        <div class="card">
            <div class="card-header">
                <h5>Payment Terms</h5>
            </div>
            <div class="card-body">
                <p>{{ $contract->payment_terms }}</p>
            </div>
        </div>
        @endif

        <!-- Delivery Terms -->
        @if($contract->delivery_terms)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Delivery Terms</h5>
            </div>
            <div class="card-body">
                <p>{{ $contract->delivery_terms }}</p>
            </div>
        </div>
        @endif

        <!-- Terms and Conditions -->
        @if($contract->terms)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Terms and Conditions</h5>
            </div>
            <div class="card-body">
                <pre style="white-space: pre-wrap;">{{ $contract->terms }}</pre>
            </div>
        </div>
        @endif

        <!-- Deliverables -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Deliverables</h5>
                @if($contract->status == 'active')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeliverableModal">
                        <i class="fas fa-plus me-2"></i>Add Deliverable
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Deliverable</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contract->deliverables as $del)
                                <tr>
                                    <td>{{ $del->name }}</td>
                                    <td>{{ $del->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($del->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($del->due_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @elseif($del->status == 'in_progress')
                                            <span class="badge bg-warning">In Progress</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($del->value ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No deliverables added</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Deliverable Modal -->
@if($contract->status == 'active')
<div class="modal fade" id="addDeliverableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('finance.procurement.contracts.add-deliverable', $contract->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Deliverable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="number" step="0.01" name="value" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Deliverable</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Audit Trail -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created By:</strong> {{ $contract->creator->name ?? 'N/A' }}</p>
                        <p><strong>Created At:</strong> {{ $contract->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($contract->approved_by)
                    <div class="col-md-6">
                        <p><strong>Approved By:</strong> {{ $contract->approver->name ?? 'N/A' }}</p>
                        <p><strong>Approved At:</strong> {{ $contract->approved_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection