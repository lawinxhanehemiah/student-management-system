@extends('layouts.principal')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Adjustment Request Details</h5>
            <span class="badge {{ $request->status == 'pending' ? 'bg-warning' : ($request->status == 'executed' ? 'bg-success' : 'bg-danger') }}">
                {{ ucfirst($request->status) }}
            </span>
        </div>
        <div class="card-body">
            {{-- Student Information --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Student Information</h6>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="35%">Full Name</th><td>{{ $request->student->user->first_name ?? '' }} {{ $request->student->user->last_name ?? '' }}</td></tr>
                        <tr><th>Registration Number</th><td>{{ $request->student->registration_number ?? 'N/A' }}</td></tr>
                        <tr><th>Programme</th><td>{{ $request->student->programme->name ?? 'N/A' }}</td></tr>
                        <tr><th>Current Level</th><td>Year {{ $request->student->current_level ?? 'N/A' }}, Semester {{ $request->student->current_semester ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Request Details --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Request Details</h6>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="35%">Request Type</th><td><span class="badge bg-info">{{ ucfirst($request->request_type) }}</span></td></tr>
                        <tr><th>Amount</th><td><strong class="text-primary">{{ number_format($request->amount, 0) }} TZS</strong></td></tr>
                        <tr><th>Requested By</th><td>{{ $request->creator->name ?? $request->creator->first_name ?? 'System' }}</td></tr>
                        <tr><th>Requested On</th><td>{{ $request->created_at->format('d/m/Y H:i:s') }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="35%">Related Invoice</th><td>
                            @if(isset($request->metadata['invoice_id']) && $request->metadata['invoice_id'])
                                <a href="{{ route('finance.invoices.show', $request->metadata['invoice_id']) }}" class="text-primary" target="_blank">
                                    Invoice #{{ $request->metadata['invoice_id'] }}
                                </a>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td></tr>
                    </table>
                </div>
            </div>

            {{-- Reason --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Reason for Request</h6>
                    <div class="p-3 bg-light rounded">
                        {{ $request->reason }}
                    </div>
                </div>
            </div>

            {{-- Additional Notes --}}
            @if(isset($request->metadata['notes']) && $request->metadata['notes'])
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Additional Notes</h6>
                    <div class="p-3 bg-light rounded">
                        {{ $request->metadata['notes'] }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Attachment Section --}}
            @if(isset($request->metadata['attachment']) && $request->metadata['attachment'])
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Supporting Documents</h6>
                    <div class="p-3 bg-light rounded">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <i class="feather-file-text text-primary" style="font-size: 2rem;"></i>
                                <span class="ms-2">
                                    <strong>{{ basename($request->metadata['attachment']) }}</strong>
                                </span>
                            </div>
                            <div>
                                <a href="{{ asset('storage/' . $request->metadata['attachment']) }}" 
                                   class="btn btn-sm btn-primary" 
                                   target="_blank">
                                    <i class="feather-eye"></i> View
                                </a>
                                <a href="{{ asset('storage/' . $request->metadata['attachment']) }}" 
                                   class="btn btn-sm btn-secondary" 
                                   download>
                                    <i class="feather-download"></i> Download
                                </a>
                            </div>
                        </div>
                        <div class="text-muted small mt-2">
                            <i class="feather-info"></i> 
                            Uploaded on: {{ $request->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Approval/Rejection Info --}}
            @if($request->approved_by && $request->status != 'pending')
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="fw-bold border-bottom pb-2">Review Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><th width="35%">Reviewed By</th><td>{{ $request->approver->name ?? $request->approver->first_name ?? 'N/A' }}</td></tr>
                                <tr><th>Reviewed On</th><td>{{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') : 'N/A' }}</td></tr>
                                <tr><th>Decision</th>
                                    <td>
                                        @if($request->status == 'executed')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                     </td>
                                </tr>
                            </table>
                        </div>
                        @if(isset($request->metadata['rejection_reason']) && $request->metadata['rejection_reason'])
                        <div class="col-md-12 mt-2">
                            <div class="alert alert-danger">
                                <strong><i class="feather-alert-circle"></i> Rejection Reason:</strong><br>
                                {{ $request->metadata['rejection_reason'] }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            @if($request->status == 'pending')
            <div class="row mt-4">
                <div class="col-12">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('principal.payment-adjustments.pending') }}" class="btn btn-secondary">
                            <i class="feather-arrow-left"></i> Back to Pending List
                        </a>
                        <div>
                            <form action="{{ route('principal.payment-adjustments.approve', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to APPROVE this adjustment request?')">
                                    <i class="feather-check-circle"></i> Approve Request
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="feather-x-circle"></i> Reject Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="row mt-4">
                <div class="col-12">
                    <hr>
                    <a href="{{ route('principal.payment-adjustments.pending') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left"></i> Back to Pending List
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('principal.payment-adjustments.reject', $request->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="feather-alert-triangle text-danger"></i> Reject Adjustment Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="feather-info"></i> 
                        Please provide a reason for rejecting this request. This will be visible to the Financial Controller.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required 
                            placeholder="Example: Insufficient supporting documents, Incorrect amount, Payment already processed, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="feather-x"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .table-borderless th, .table-borderless td {
        padding: 0.3rem 0;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .feather-file-text {
        width: 32px;
        height: 32px;
    }
    .btn {
        border-radius: 6px;
    }
</style>
@endsection