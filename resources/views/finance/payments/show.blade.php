@extends('layouts.financecontroller')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Payment Details</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.all-payments.index') }}" class="breadcrumb-item">All Payments</a>
                <span class="breadcrumb-item active">{{ $payment->payment_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($payment->status == 'completed')
            <a href="{{ route('finance.payments.receipt', $payment->id) }}" class="btn btn-primary-light btn-wave" target="_blank">
                <i class="feather-printer"></i> Print Receipt
            </a>
            @endif
            @if($payment->status == 'pending_verification' && $canVerify)
            <button class="btn btn-success btn-wave" onclick="verifyPayment({{ $payment->id }}, 'approve')">
                <i class="feather-check"></i> Approve
            </button>
            <button class="btn btn-danger btn-wave" onclick="verifyPayment({{ $payment->id }}, 'reject')">
                <i class="feather-x"></i> Reject
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Payment Details Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Payment Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Number</label>
                            <h5>{{ $payment->payment_number }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Status</label><br>
                            @if($payment->status == 'completed')
                                <span class="badge bg-success fs-6">Completed</span>
                            @elseif($payment->status == 'pending')
                                <span class="badge bg-warning fs-6">Pending</span>
                            @elseif($payment->status == 'pending_verification')
                                <span class="badge bg-info fs-6">Pending Verification</span>
                            @elseif($payment->status == 'failed')
                                <span class="badge bg-danger fs-6">Failed</span>
                            @elseif($payment->status == 'rejected')
                                <span class="badge bg-danger fs-6">Rejected</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Date</label>
                            <h5>{{ $payment->created_at->format('d F Y H:i') }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Payment Method</label>
                            <h5>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Amount</label>
                            <h3 class="text-primary">TZS {{ number_format($payment->amount, 2) }}</h3>
                        </div>
                        @if($payment->control_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Control Number</label>
                            <h5>{{ $payment->control_number }}</h5>
                        </div>
                        @endif
                        @if($payment->transaction_reference)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Transaction Reference</label>
                            <h5>{{ $payment->transaction_reference }}</h5>
                        </div>
                        @endif
                        @if($payment->receipt_number)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Receipt Number</label>
                            <h5>{{ $payment->receipt_number }}</h5>
                        </div>
                        @endif
                    </div>

                    @if($payment->metadata)
                    <hr>
                    <h6 class="fw-semibold mb-3">Additional Information</h6>
                    <div class="row">
                        @if(isset($payment->metadata['payment_date']))
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Payment Date (Manual)</small>
                            <span>{{ $payment->metadata['payment_date'] }}</span>
                        </div>
                        @endif
                        @if(isset($payment->metadata['notes']))
                        <div class="col-md-8 mb-2">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $payment->metadata['notes'] }}</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Details Card -->
            @if($payment->payable && $payment->payable_type == 'App\\Models\\Invoice')
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Invoice Details</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Invoice Number</label>
                            <h5>
                                <a href="{{ route('finance.invoices.show', $payment->payable->id) }}">
                                    {{ $payment->payable->invoice_number }}
                                </a>
                            </h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Invoice Type</label>
                            <h5>{{ ucwords(str_replace('_', ' ', $payment->payable->invoice_type)) }}</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Invoice Total</label>
                            <h5>TZS {{ number_format($payment->payable->total_amount, 2) }}</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Invoice Balance</label>
                            <h5 class="{{ $payment->payable->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($payment->payable->balance, 2) }}
                            </h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Due Date</label>
                            <h5>{{ $payment->payable->due_date->format('d F Y') }}</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Payment Status</label><br>
                            @if($payment->payable->payment_status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($payment->payable->payment_status == 'partial')
                                <span class="badge bg-warning">Partial</span>
                            @else
                                <span class="badge bg-danger">Unpaid</span>
                            @endif
                        </div>
                    </div>

                    @if($payment->payable->items && $payment->payable->items->count() > 0)
                    <hr>
                    <h6 class="fw-semibold mb-3">Invoice Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->payable->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">TZS {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Payment Attempts -->
            @if($payment->attempts && $payment->attempts->count() > 0)
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Payment Attempts</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->attempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($attempt->status == 'success')
                                            <span class="badge bg-success">Success</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ $attempt->response_message ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-xl-4">
            <!-- Student Info Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Student Information</div>
                </div>
                <div class="card-body">
                    @if($payment->student)
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto">
                            <span class="fs-1">{{ substr($payment->student->user->first_name ?? 'S', 0, 1) }}</span>
                        </div>
                        <h5 class="mt-2">{{ $payment->student->user->first_name ?? '' }} {{ $payment->student->user->last_name ?? '' }}</h5>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Registration No</label>
                        <p class="fw-semibold">{{ $payment->student->registration_number ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Programme</label>
                        <p>{{ $payment->student->programme->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Level / Semester</label>
                        <p>Year {{ $payment->student->current_level ?? 'N/A' }}, Semester {{ $payment->student->current_semester ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <label class="text-muted">Academic Year</label>
                        <p>{{ $payment->academicYear->name ?? 'N/A' }}</p>
                    </div>
                    @else
                    <p class="text-muted text-center">No student information available</p>
                    @endif
                </div>
            </div>

            <!-- Audit Info Card -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Audit Information</div>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted">Created By</label>
                        <p>{{ $payment->createdBy->name ?? 'System' }}</p>
                        <small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    
                    @if($payment->updated_by && $payment->updated_at != $payment->created_at)
                    <div class="mb-2 mt-3">
                        <label class="text-muted">Last Updated By</label>
                        <p>{{ $payment->updatedBy->name ?? 'N/A' }}</p>
                        <small class="text-muted">{{ $payment->updated_at->format('d/m/Y H:i') }}</small>
                    </div>
                    @endif

                    @if(isset($payment->metadata['verified_by']))
                    <div class="mb-2 mt-3">
                        <label class="text-muted">Verified By</label>
                        <p>{{ $payment->metadata['verified_by'] ?? 'N/A' }}</p>
                        <small class="text-muted">{{ $payment->metadata['verified_at'] ?? '' }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Gateway Info Card -->
            @if($payment->gateway)
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Payment Gateway</div>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted">Gateway</label>
                        <p>{{ $payment->gateway->name }}</p>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted">Gateway Code</label>
                        <p>{{ $payment->gateway->code }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function verifyPayment(paymentId, action) {
    let title = action === 'approve' ? 'Approve Payment' : 'Reject Payment';
    let inputHtml = '';
    
    if (action === 'approve') {
        inputHtml = `
            <div class="mb-3">
                <label class="form-label">Receipt Number</label>
                <input type="text" id="receiptNumber" class="form-control" required>
            </div>
        `;
    } else {
        inputHtml = `
            <div class="mb-3">
                <label class="form-label">Rejection Reason</label>
                <textarea id="rejectionReason" class="form-control" rows="3" required></textarea>
            </div>
        `;
    }
    
    Swal.fire({
        title: title,
        html: inputHtml,
        showCancelButton: true,
        confirmButtonText: action === 'approve' ? 'Approve' : 'Reject',
        confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
        preConfirm: () => {
            if (action === 'approve') {
                const receipt = $('#receiptNumber').val();
                if (!receipt) {
                    Swal.showValidationMessage('Receipt number is required');
                    return false;
                }
                return { receipt };
            } else {
                const reason = $('#rejectionReason').val();
                if (!reason) {
                    Swal.showValidationMessage('Rejection reason is required');
                    return false;
                }
                return { reason };
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let data = {
                action: action,
                _token: '{{ csrf_token() }}'
            };
            
            if (action === 'approve') {
                data.receipt_number = result.value.receipt;
            } else {
                data.rejection_reason = result.value.reason;
            }
            
            $.ajax({
                url: '{{ route("finance.payments.verify", "") }}/' + paymentId,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to process request', 'error');
                }
            });
        }
    });
}
</script>
@endpush
@endsection