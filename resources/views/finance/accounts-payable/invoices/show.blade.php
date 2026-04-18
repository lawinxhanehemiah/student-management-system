@extends('layouts.financecontroller')

@section('title', 'Supplier Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Supplier Invoice Details</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.invoices.index') }}" class="text-muted">Supplier Invoices</a> > 
                <span>{{ $invoice->invoice_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($invoice->status == 'pending')
            <button class="btn btn-sm btn-info" onclick="verifyInvoice()">
                <i class="feather-check-circle"></i> Verify
            </button>
            @endif
            
            @if($invoice->status == 'verified')
            <button class="btn btn-sm btn-success" onclick="approveInvoice()">
                <i class="feather-check"></i> Approve
            </button>
            @endif
            
            @if(in_array($invoice->status, ['approved', 'partial_paid']) && $invoice->balance > 0)
            <a href="{{ route('finance.accounts-payable.payment-vouchers.create') }}?invoice_id={{ $invoice->id }}" 
               class="btn btn-sm btn-primary">
                <i class="feather-dollar-sign"></i> Make Payment
            </a>
            @endif
            
            <button class="btn btn-sm btn-primary-light" onclick="window.print()">
                <i class="feather-printer"></i> Print
            </button>
            <a href="{{ route('finance.accounts-payable.invoices.index') }}" class="btn btn-sm btn-light">
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
                    @php
                        $statusColor = [
                            'pending' => 'warning',
                            'verified' => 'info',
                            'approved' => 'primary',
                            'partial_paid' => 'warning',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'secondary'
                        ][$invoice->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }} ms-2">
                        {{ str_replace('_', ' ', ucwords($invoice->status)) }}
                    </span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Invoice Number:</span>
                    <span class="fw-semibold ms-2">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Supplier Invoice:</span>
                    <span class="ms-2">{{ $invoice->supplier_invoice_number }}</span>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Date:</span>
                    <span class="ms-2">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Due Date:</span>
                    <span class="ms-2 {{ $invoice->due_date < now() && $invoice->balance > 0 ? 'text-danger' : '' }}">
                        {{ $invoice->due_date->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Info -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Supplier Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <span class="text-muted small">Name:</span>
                    <h6 class="fw-semibold">{{ $invoice->supplier->name }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Code:</span>
                    <h6>{{ $invoice->supplier->supplier_code }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Tax Number:</span>
                    <h6>{{ $invoice->supplier->tax_number ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Phone:</span>
                    <h6>{{ $invoice->supplier->phone ?? 'N/A' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Reference Info -->
    @if($invoice->purchaseOrder || $invoice->goodsReceivedNote)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Reference Documents</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @if($invoice->purchaseOrder)
                <div class="col-md-4">
                    <span class="text-muted small">Purchase Order:</span>
                    <h6>
                        <a href="{{ route('finance.accounts-payable.purchase-orders.show', $invoice->purchaseOrder->id) }}">
                            {{ $invoice->purchaseOrder->po_number }}
                        </a>
                    </h6>
                </div>
                @endif
                @if($invoice->goodsReceivedNote)
                <div class="col-md-4">
                    <span class="text-muted small">Goods Received Note:</span>
                    <h6>
                        <a href="{{ route('finance.accounts-payable.grn.show', $invoice->goodsReceivedNote->id) }}">
                            {{ $invoice->goodsReceivedNote->grn_number }}
                        </a>
                    </h6>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Items Table -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Invoice Items</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-end">Tax Amount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 0) }}</td>
                            <td class="text-end">{{ $item->tax_rate }}%</td>
                            <td class="text-end">{{ number_format($item->tax_amount, 0) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->total, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end">{{ number_format($invoice->subtotal, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end">Tax Total:</td>
                            <td class="text-end">{{ number_format($invoice->tax_amount, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($invoice->total_amount, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Amount:</span>
                        <span class="fw-semibold">TZS {{ number_format($invoice->total_amount, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Paid Amount:</span>
                        <span class="fw-semibold text-success">TZS {{ number_format($invoice->paid_amount, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Balance:</span>
                        <span class="fw-semibold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            TZS {{ number_format($invoice->balance, 0) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Payment History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher #</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->paymentVouchers as $voucher)
                                <tr>
                                    <td>{{ $voucher->payment_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('finance.accounts-payable.payment-vouchers.show', $voucher->id) }}">
                                            {{ $voucher->voucher_number }}
                                        </a>
                                    </td>
                                    <td>{{ str_replace('_', ' ', ucwords($voucher->payment_method)) }}</td>
                                    <td>{{ $voucher->reference_number ?? 'N/A' }}</td>
                                    <td class="text-end">TZS {{ number_format($voucher->amount, 0) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-2">No payments yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Notes</h6>
        </div>
        <div class="card-body">
            <p class="small">{{ $invoice->notes }}</p>
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
                    <span class="text-muted">Created By:</span> {{ $invoice->creator->name ?? 'System' }}
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Created At:</span> {{ $invoice->created_at->format('d/m/Y H:i') }}
                </div>
                @if($invoice->verified_by)
                <div class="col-md-3">
                    <span class="text-muted">Verified By:</span> {{ $invoice->verifier->name ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Verified At:</span> {{ $invoice->verified_at?->format('d/m/Y H:i') }}
                </div>
                @endif
                @if($invoice->approved_by)
                <div class="col-md-3">
                    <span class="text-muted">Approved By:</span> {{ $invoice->approver->name ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Approved At:</span> {{ $invoice->approved_at?->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function verifyInvoice() {
    if (confirm('Verify this invoice?')) {
        fetch('{{ route("finance.accounts-payable.invoices.verify", $invoice->id) }}', {
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

function approveInvoice() {
    if (confirm('Approve this invoice?')) {
        fetch('{{ route("finance.accounts-payable.invoices.approve", $invoice->id) }}', {
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