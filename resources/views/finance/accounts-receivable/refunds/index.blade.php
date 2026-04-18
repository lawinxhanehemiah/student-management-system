@extends('layouts.financecontroller')

@section('title', 'Refund Processing')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Refund Processing</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <span class="breadcrumb-item active">Refunds</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.refunds.create') }}" class="btn btn-primary btn-wave">
                <i class="feather-plus"></i> New Refund Request
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Total Requested</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['total_requested'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-primary-transparent">
                            <i class="feather-dollar-sign fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Approved</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['total_approved'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-success-transparent">
                            <i class="feather-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Processed</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['total_processed'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-info-transparent">
                            <i class="feather-check-square fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Pending</span>
                            <h3 class="fw-semibold mb-2">{{ $summary['pending_count'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-warning-transparent">
                            <i class="feather-clock fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.refunds.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Refund Method</label>
                                <select class="form-select" name="refund_method">
                                    <option value="">All</option>
                                    <option value="bank_transfer" {{ request('refund_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mpesa" {{ request('refund_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                                    <option value="cash" {{ request('refund_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="cheque" {{ request('refund_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-12 text-end">
                                <a href="{{ route('finance.refunds.index') }}" class="btn btn-light">Clear</a>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Refund Requests</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Refund #</th>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Payment #</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Requested By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($refunds as $refund)
                                <tr class="{{ $refund->status == 'pending' ? 'table-warning' : '' }}">
                                    <td>
                                        <a href="{{ route('finance.refunds.show', $refund->id) }}" class="fw-semibold">
                                            {{ $refund->refund_number }}
                                        </a>
                                    </td>
                                    <td>{{ $refund->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-primary-transparent">
                                                <span>{{ substr($refund->student->user->first_name ?? 'S', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <span>{{ $refund->student->user->first_name ?? '' }} {{ $refund->student->user->last_name ?? '' }}</span>
                                                <small class="d-block text-muted">{{ $refund->student->registration_number ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $refund->payment->payment_number ?? '' }}</td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            {{ str_replace('_', ' ', ucwords($refund->refund_method)) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($refund->amount, 2) }}</td>
                                    <td>
                                        @if($refund->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($refund->status == 'approved')
                                            <span class="badge bg-info">Approved</span>
                                        @elseif($refund->status == 'processed')
                                            <span class="badge bg-success">Processed</span>
                                        @elseif($refund->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $refund->requestedBy->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('finance.refunds.show', $refund->id) }}" class="btn btn-sm btn-icon btn-info-light" data-bs-toggle="tooltip" title="View">
                                                <i class="feather-eye"></i>
                                            </a>
                                            
                                            @if($refund->status == 'pending' && auth()->user()->can('approve-refunds'))
                                            <button class="btn btn-sm btn-icon btn-success-light approve-refund" 
                                                    data-id="{{ $refund->id }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Approve">
                                                <i class="feather-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-danger-light reject-refund" 
                                                    data-id="{{ $refund->id }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Reject">
                                                <i class="feather-x"></i>
                                            </button>
                                            @endif
                                            
                                            @if($refund->status == 'approved' && auth()->user()->can('process-refunds'))
                                            <button class="btn btn-sm btn-icon btn-primary-light process-refund" 
                                                    data-id="{{ $refund->id }}"
                                                    data-method="{{ $refund->refund_method }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Process">
                                                <i class="feather-play"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 150px;">
                                        <h5 class="mt-3">No Refund Requests Found</h5>
                                        <p class="text-muted">Create your first refund request to get started</p>
                                        <a href="{{ route('finance.refunds.create') }}" class="btn btn-primary">
                                            <i class="feather-plus"></i> New Refund Request
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $refunds->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Approve Refund
    $('.approve-refund').click(function() {
        const refundId = $(this).data('id');
        
        Swal.fire({
            title: 'Approve Refund',
            text: 'Are you sure you want to approve this refund request?',
            icon: 'question',
            input: 'textarea',
            inputPlaceholder: 'Add approval notes (optional)',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, approve'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("finance.refunds.approve", "") }}/' + refundId,
                    method: 'POST',
                    data: {
                        notes: result.value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Approved!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Reject Refund
    $('.reject-refund').click(function() {
        const refundId = $(this).data('id');
        
        Swal.fire({
            title: 'Reject Refund',
            text: 'Are you sure you want to reject this refund request?',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Reason for rejection...',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for rejection';
                }
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, reject'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("finance.refunds.reject", "") }}/' + refundId,
                    method: 'POST',
                    data: {
                        rejection_reason: result.value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rejected!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Process Refund
    $('.process-refund').click(function() {
        const refundId = $(this).data('id');
        const method = $(this).data('method');
        
        let html = '';
        
        if (method === 'bank_transfer') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Transaction Reference</label>
                    <input type="text" id="transactionRef" class="form-control" placeholder="Enter bank reference">
                </div>
                <div class="mb-3">
                    <label class="form-label">Processing Notes</label>
                    <textarea id="processNotes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            `;
        } else if (method === 'mpesa') {
            html = `
                <div class="mb-3">
                    <label class="form-label">M-Pesa Reference</label>
                    <input type="text" id="transactionRef" class="form-control" placeholder="Enter M-Pesa reference">
                </div>
                <div class="mb-3">
                    <label class="form-label">Processing Notes</label>
                    <textarea id="processNotes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            `;
        } else if (method === 'cash') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Receipt Number</label>
                    <input type="text" id="transactionRef" class="form-control" placeholder="Enter receipt number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Processing Notes</label>
                    <textarea id="processNotes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            `;
        } else if (method === 'cheque') {
            html = `
                <div class="mb-3">
                    <label class="form-label">Cheque Number</label>
                    <input type="text" id="transactionRef" class="form-control" placeholder="Enter cheque number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Processing Notes</label>
                    <textarea id="processNotes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            `;
        }
        
        Swal.fire({
            title: 'Process Refund',
            html: html,
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Process',
            preConfirm: () => {
                const transactionRef = $('#transactionRef').val();
                const processNotes = $('#processNotes').val();
                
                if (!transactionRef) {
                    Swal.showValidationMessage('Transaction reference is required');
                    return false;
                }
                
                return { transactionRef, processNotes };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("finance.refunds.process", "") }}/' + refundId,
                    method: 'POST',
                    data: {
                        transaction_reference: result.value.transactionRef,
                        processed_notes: result.value.processNotes,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Processed!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection