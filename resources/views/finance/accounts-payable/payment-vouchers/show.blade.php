@extends('layouts.financecontroller')

@section('title', 'Payment Voucher - ' . $voucher->voucher_number)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Payment Voucher Details</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.payment-vouchers.index') }}" class="text-muted">Payment Vouchers</a> > 
                <span>{{ $voucher->voucher_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($voucher->status == 'draft')
            <button class="btn btn-sm btn-info" onclick="submitForApproval()">
                <i class="feather-send"></i> Submit for Approval
            </button>
            @endif
            
            @if($voucher->status == 'pending')
            <button class="btn btn-sm btn-success" onclick="approveVoucher()">
                <i class="feather-check"></i> Approve
            </button>
            <button class="btn btn-sm btn-danger" onclick="rejectVoucher()">
                <i class="feather-x"></i> Reject
            </button>
            @endif
            
            @if($voucher->status == 'approved')
            <button class="btn btn-sm btn-primary" onclick="markAsPaid()">
                <i class="feather-dollar-sign"></i> Mark as Paid
            </button>
            @endif
            
            <button class="btn btn-sm btn-primary-light" onclick="window.print()">
                <i class="feather-printer"></i> Print
            </button>
            <a href="{{ route('finance.accounts-payable.payment-vouchers.index') }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Voucher Header -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <span class="text-muted small">Voucher #:</span>
                    <h5 class="fw-semibold">{{ $voucher->voucher_number }}</h5>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Status:</span><br>
                    @php
                        $statusColor = [
                            'draft' => 'secondary',
                            'pending' => 'warning',
                            'approved' => 'info',
                            'paid' => 'success',
                            'cancelled' => 'danger'
                        ][$voucher->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6">{{ ucwords($voucher->status) }}</span>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Payment Date:</span>
                    <h6>{{ $voucher->payment_date->format('d/m/Y') }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Payment Method:</span>
                    <h6>{{ str_replace('_', ' ', ucwords($voucher->payment_method)) }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Reference:</span>
                    <h6>{{ $voucher->reference_number ?? 'N/A' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier & Amount -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Supplier Information</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-semibold">{{ $voucher->supplier->name }}</h5>
                    <p class="small mb-1">{{ $voucher->supplier->address }}</p>
                    <p class="small mb-0">TIN: {{ $voucher->supplier->tax_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Payment Amount</h6>
                </div>
                <div class="card-body text-center">
                    <span class="text-muted small">Amount Paid</span>
                    <h2 class="text-primary fw-bold">TZS {{ number_format($voucher->amount, 2) }}</h2>
                    <span class="small text-muted">In words: {{ ucwords(str_replace('_', ' ', $voucher->description ?? 'Payment')) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Details (if applicable) -->
    @if(in_array($voucher->payment_method, ['bank_transfer', 'cheque']))
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Bank Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <span class="text-muted small">Bank Name:</span>
                    <p class="fw-semibold">{{ $voucher->bank_name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Account Number:</span>
                    <p class="fw-semibold">{{ $voucher->bank_account ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Reference/Cheque No:</span>
                    <p class="fw-semibold">{{ $voucher->reference_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Linked Invoice -->
    @if($voucher->supplierInvoice)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Linked Invoice</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <span class="text-muted small">Invoice Number:</span>
                    <p>
                        <a href="{{ route('finance.accounts-payable.invoices.show', $voucher->supplierInvoice->id) }}">
                            {{ $voucher->supplierInvoice->invoice_number }}
                        </a>
                    </p>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Supplier Invoice:</span>
                    <p>{{ $voucher->supplierInvoice->supplier_invoice_number }}</p>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Invoice Total:</span>
                    <p>TZS {{ number_format($voucher->supplierInvoice->total_amount, 0) }}</p>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Invoice Balance:</span>
                    <p class="fw-semibold text-danger">TZS {{ number_format($voucher->supplierInvoice->balance, 0) }}</p>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Status:</span><br>
                    @php
                        $statusColor = [
                            'pending' => 'warning',
                            'verified' => 'info',
                            'approved' => 'primary',
                            'partial_paid' => 'warning',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'secondary'
                        ][$voucher->supplierInvoice->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }}">
                        {{ str_replace('_', ' ', ucwords($voucher->supplierInvoice->status)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Description & Notes -->
    @if($voucher->description)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Description</h6>
        </div>
        <div class="card-body">
            <p>{{ $voucher->description }}</p>
        </div>
    </div>
    @endif

    @if($voucher->notes)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Notes</h6>
        </div>
        <div class="card-body">
            <p class="small">{{ $voucher->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Audit Trail -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Audit Trail</h6>
        </div>
        <div class="card-body">
            <div class="row small">
                <div class="col-md-3">
                    <span class="text-muted">Created By:</span> {{ $voucher->creator->name ?? 'System' }}
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Created At:</span> {{ $voucher->created_at->format('d/m/Y H:i') }}
                </div>
                @if($voucher->approved_by)
                <div class="col-md-3">
                    <span class="text-muted">Approved By:</span> {{ $voucher->approver->name ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Approved At:</span> {{ $voucher->approved_at?->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitForApproval() {
    if (confirm('Submit this voucher for approval?')) {
        fetch('{{ route("finance.accounts-payable.payment-vouchers.submit", $voucher->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function approveVoucher() {
    if (confirm('Approve this payment voucher?')) {
        fetch('{{ route("finance.accounts-payable.payment-vouchers.approve", $voucher->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectVoucher() {
    const reason = prompt('Please enter rejection reason:');
    if (reason) {
        fetch('{{ route("finance.accounts-payable.payment-vouchers.reject", $voucher->id) }}', {
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
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function markAsPaid() {
    if (confirm('Mark this voucher as paid?')) {
        fetch('{{ route("finance.accounts-payable.payment-vouchers.paid", $voucher->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
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