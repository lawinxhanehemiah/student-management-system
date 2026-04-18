@extends('layouts.principal')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Adjustment Requests</h5>
            <span class="badge bg-info">{{ $requests->total() }} pending</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Amount (TZS)</th>
                            <th>Reason</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>
                                <strong>{{ $req->student->user->first_name ?? '' }} {{ $req->student->user->last_name ?? '' }}</strong>
                                <br><small class="text-muted">{{ $req->student->registration_number ?? '' }}</small>
                            </td>
                            <td>
                                @if($req->request_type == 'manual_payment')
                                    <span class="badge bg-primary">Manual Payment</span>
                                @elseif($req->request_type == 'correction')
                                    <span class="badge bg-warning">Correction</span>
                                @elseif($req->request_type == 'void')
                                    <span class="badge bg-danger">Void</span>
                                @else
                                    <span class="badge bg-info">Refund</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($req->amount, 0) }}</td>
                            <td>{{ Str::limit($req->reason, 50) }}</td>
                            <td>{{ $req->creator->name ?? $req->creator->first_name ?? '' }}</td>
                            <td>{{ $req->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('principal.payment-adjustments.show', $req->id) }}" class="btn btn-info">
                                        <i class="feather-eye"></i> View
                                    </a>
                                    <form action="{{ route('principal.payment-adjustments.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this request?')">
                                            <i class="feather-check"></i> Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                        <i class="feather-x"></i> Reject
                                    </button>
                                </div>
                             </td>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('principal.payment-adjustments.reject', $req->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Reject Adjustment Request #{{ $req->id }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i class="feather-alert-triangle"></i> 
                                                    Please provide a reason for rejecting this request.
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="feather-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">No pending adjustment requests</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .table td, .table th {
        vertical-align: middle;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endsection