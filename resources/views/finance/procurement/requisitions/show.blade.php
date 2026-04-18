@extends('layouts.financecontroller')

@section('title', 'Requisition Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Requisition Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.requisitions.index') }}">Requisitions</a></li>
                <li class="breadcrumb-item active">{{ $requisition->requisition_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if(in_array($requisition->status, ['draft', 'rejected']))
            <a href="{{ route('finance.procurement.requisitions.edit', $requisition->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        @endif
        @if($requisition->status == 'draft')
            <form action="{{ route('finance.procurement.requisitions.submit', $requisition->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Submit for approval?')">
                    <i class="fas fa-paper-plane me-2"></i>Submit
                </button>
            </form>
        @endif
        @if(in_array($requisition->status, ['draft', 'submitted', 'under_review']))
            <form action="{{ route('finance.procurement.requisitions.cancel', $requisition->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this requisition?')">
                    <i class="fas fa-times-circle me-2"></i>Cancel
                </button>
            </form>
        @endif
        <a href="{{ route('finance.procurement.requisitions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Requisition Info -->
        <div class="card">
            <div class="card-header">
                <h5>Requisition Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Number:</th>
                        <td><strong>{{ $requisition->requisition_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'submitted' => 'info',
                                    'under_review' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'dark'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$requisition->status] }}">
                                {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Priority:</th>
                        <td>
                            @php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$requisition->priority] }}">
                                {{ ucfirst($requisition->priority) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Request Date:</th>
                        <td>{{ $requisition->request_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Required Date:</th>
                        <td>{{ $requisition->required_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Department:</th>
                        <td>{{ $requisition->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Requested By:</th>
                        <td>{{ $requisition->requester->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Estimated Total:</th>
                        <td><strong>{{ number_format($requisition->estimated_total, 2) }}</strong></td>
                    </tr>
                </table>

                @if($requisition->justification)
                    <div class="mt-3">
                        <h6>Justification:</h6>
                        <p class="text-muted">{{ $requisition->justification }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Approval Progress -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Approval Progress</h5>
            </div>
            <div class="card-body">
                @forelse($requisition->approvals as $approval)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Level {{ $approval->approvalLevel->level_order ?? '?' }}</strong>
                            @if($approval->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($approval->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($approval->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </div>
                        <small>{{ $approval->approvalLevel->name ?? 'N/A' }}</small>
                        @if($approval->comments)
                            <p class="small text-muted mt-1">{{ $approval->comments }}</p>
                        @endif
                        @if($approval->action_date)
                            <small class="text-muted">{{ $approval->action_date->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">No approvals configured yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Description -->
        <div class="card">
            <div class="card-header">
                <h5>Description</h5>
            </div>
            <div class="card-body">
                <p>{{ $requisition->description }}</p>
            </div>
        </div>

        <!-- Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Items Required</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisition->items as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td class="text-end">{{ number_format($item->estimated_unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->estimated_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">GRAND TOTAL:</th>
                                <th class="text-end">{{ number_format($requisition->estimated_total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Approval Actions (for pending approvals) -->
        @php
            $pendingApproval = $requisition->approvals()->where('status', 'pending')->first();
        @endphp
        @if($pendingApproval)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Approval Action Required</h5>
                </div>
                <div class="card-body">
                    <p>Level {{ $pendingApproval->approvalLevel->level_order }}: {{ $pendingApproval->approvalLevel->name }}</p>
                    
                    <form action="{{ route('finance.procurement.requisitions.approve', $pendingApproval->id) }}" method="POST" class="d-inline">
                        @csrf
                        <div class="mb-3">
                            <label>Comments (optional)</label>
                            <textarea name="comments" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this requisition?')">
                            <i class="fas fa-check me-2"></i>Approve
                        </button>
                    </form>
                    
                    <form action="{{ route('finance.procurement.requisitions.reject', $pendingApproval->id) }}" method="POST" class="d-inline ms-2">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this requisition?')">
                            <i class="fas fa-times me-2"></i>Reject
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection