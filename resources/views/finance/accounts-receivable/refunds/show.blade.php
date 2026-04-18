@extends('layouts.financecontroller')

@section('title', 'Refund Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Refund Details</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <a href="{{ route('finance.refunds.index') }}" class="breadcrumb-item">Refunds</a>
                <span class="breadcrumb-item active">{{ $refund->refund_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($refund->status == 'pending' && auth()->user()->can('approve-refunds'))
            <button class="btn btn-success btn-wave" onclick="approveRefund({{ $refund->id }})">
                <i class="feather-check"></i> Approve
            </button>
            <button class="btn btn-danger btn-wave" onclick="rejectRefund({{ $refund->id }})">
                <i class="feather-x"></i> Reject
            </button>
            @endif
            
            @if($refund->status == 'approved' && auth()->user()->can('process-refunds'))
            <button class="btn btn-primary btn-wave" onclick="processRefund({{ $refund->id }})">
                <i class="feather-play"></i> Process
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Refund Details Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Refund Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Refund Number</label>
                            <h5>{{ $refund->refund_number }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Status</label><br>
                            @if($refund->status == 'pending')
                                <span class="badge bg-warning fs-6">Pending</span>
                            @elseif($refund->status == 'approved')
                                <span class="badge bg-info fs-6">Approved</span>
                            @elseif($refund->status == 'processed')
                                <span class="badge bg-success fs-6">Processed</span>
                            @elseif($refund->status == 'rejected')
                                <span class="badge bg-danger fs-6">Rejected</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Request Date</label>
                            <h5>{{ $refund->created_at->format('d F Y H:i') }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Refund Method</label>
                            <h5>{{ str_replace('_', ' ', ucwords($refund->refund_method)) }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Amount</label>
                            <h3 class="text-primary">TZS {{ number_format($refund->amount, 2) }}</h3>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Reason</label>
                            <h5>{{ ucfirst(str_replace('_', ' ', $refund->refund_reason)) }}</h5>
                        </div>
                        
                        @if($refund->description)
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Description</label>
                            <p>{{ $refund->description }}</p>
                        </div>
                        @endif

                        <!-- Method-specific details -->
                        @if($refund->refund_method == 'bank_transfer')
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Bank Name</label>
                            <h5>{{ $refund->bank_name }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Account Number</label>
                            <h5>{{ $refund->bank_account }}</h5>
                        </div>
                        @endif

                        @if($refund->refund_method == 'mpesa')
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Phone Number</label>
                            <h5>{{ $refund->phone_number }}</h5>
                        </div>
                        @endif

                        @if($refund->refund_method == 'cheque')
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Cheque Number</label>
                            <h5>{{ $refund->cheque_number }}</h5>
                        </div>
                        @endif

                        @if($refund->transaction_reference)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Transaction Reference</label>
                            <h5>{{ $refund->transaction_reference }}</h5>
                        </div>
                        @endif

                        @if($refund->rejection_reason)
                        <div class="col-md-12 mb-3">
                            <label class="text-muted text-danger">Rejection Reason</label>
                            <p class="text-danger">{{ $refund->rejection_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Details Card -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Original Payment Details</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Number</label>
                            <h5>{{ $refund->payment->payment_number ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Date</label>
                            <h5>{{ $refund->payment ? $refund->payment->created_at->format('d F Y') : 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Method</label>
                            <h5>{{ $refund->payment ? ucwords($refund->payment->payment_method) : 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Original Amount</label>
                            <h5>TZS {{ number_format($refund->payment->amount ?? 0, 2) }}</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Invoice</label>
                            <h5>
                                <a href="#">{{ $refund->invoice->invoice_number ?? 'N/A' }}</a>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Student Info Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Student Information</div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto">
                            <span class="fs-1">{{ substr($refund->student->user->first_name ?? 'S', 0, 1) }}</span>
                        </div>
                        <h5 class="mt-2">{{ $refund->student->user->first_name ?? '' }} {{ $refund->student->user->last_name ?? '' }}</h5>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Registration No</label>
                        <p class="fw-semibold">{{ $refund->student->registration_number ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Programme</label>
                        <p>{{ $refund->student->programme->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Academic Year</label>
                        <p>{{ $refund->academicYear->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Audit Info Card -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Audit Trail</div>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted">Requested By</label>
                        <p>{{ $refund->requestedBy->name ?? 'N/A' }}</p>
                        <small class="text-muted">{{ $refund->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    
                    @if($refund->approved_by)
                    <div class="mb-2 mt-3">
                        <label class="text-muted">Approved By</label>
                        <p>{{ $refund->approvedBy->name ?? 'N/A' }}</p>
                        <small class="text-muted">{{ $refund->approved_at ? $refund->approved_at->format('d/m/Y H:i') : '' }}</small>
                    </div>
                    @endif
                    
                    @if($refund->processed_by)
                    <div class="mb-2 mt-3">
                        <label class="text-muted">Processed By</label>
                        <p>{{ $refund->processedBy->name ?? 'N/A' }}</p>
                        <small class="text-muted">{{ $refund->processed_at ? $refund->processed_at->format('d/m/Y H:i') : '' }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveRefund(id) {
    Swal.fire({
        title: 'Approve Refund',
        text: 'Are you sure you want to approve this refund?',
        icon: 'question',
        input: 'textarea',
        inputPlaceholder: 'Add approval notes (optional)',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, approve'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("finance.refunds.approve", "") }}/' + id,
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
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to approve refund', 'error');
                }
            });
        }
    });
}

function rejectRefund(id) {
    Swal.fire({
        title: 'Reject Refund',
        text: 'Are you sure you want to reject this refund?',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Reason for rejection...',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason';
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, reject'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("finance.refunds.reject", "") }}/' + id,
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
                    }
                }
            });
        }
    });
}

function processRefund(id) {
    Swal.fire({
        title: 'Process Refund',
        html: `
            <div class="mb-3">
                <label class="form-label">Transaction Reference</label>
                <input type="text" id="transactionRef" class="form-control" placeholder="Enter reference number">
            </div>
            <div class="mb-3">
                <label class="form-label">Processing Notes</label>
                <textarea id="processNotes" class="form-control" rows="2"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        confirmButtonText: 'Process',
        preConfirm: () => {
            const ref = $('#transactionRef').val();
            if (!ref) {
                Swal.showValidationMessage('Transaction reference is required');
                return false;
            }
            return { ref, notes: $('#processNotes').val() };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("finance.refunds.process", "") }}/' + id,
                method: 'POST',
                data: {
                    transaction_reference: result.value.ref,
                    processed_notes: result.value.notes,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Processed!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                }
            });
        }
    });
}
</script>
@endpush
@endsection