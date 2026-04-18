@extends('layouts.financecontroller')

@section('title', 'Purchase Order - ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Purchase Order Details</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}" class="text-muted">Purchase Orders</a> > 
                <span>{{ $purchaseOrder->po_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if(in_array($purchaseOrder->status, ['draft', 'pending_approval']))
            <a href="{{ route('finance.accounts-payable.purchase-orders.edit', $purchaseOrder->id) }}" 
               class="btn btn-sm btn-primary">
                <i class="feather-edit"></i> Edit
            </a>
            @endif
            
            @if($purchaseOrder->status == 'draft')
            <button class="btn btn-sm btn-info" onclick="submitForApproval()">
                <i class="feather-send"></i> Submit for Approval
            </button>
            @endif
            
            @if($purchaseOrder->status == 'pending_approval')
            <button class="btn btn-sm btn-success" onclick="approvePO()">
                <i class="feather-check"></i> Approve
            </button>
            <button class="btn btn-sm btn-danger" onclick="rejectPO()">
                <i class="feather-x"></i> Reject
            </button>
            @endif
            
            @if($purchaseOrder->status == 'approved')
            <a href="{{ route('finance.accounts-payable.grn.create') }}?purchase_order_id={{ $purchaseOrder->id }}" 
               class="btn btn-sm btn-success-light">
                <i class="feather-package"></i> Receive Goods
            </a>
            @endif
            
            <button class="btn btn-sm btn-primary-light" onclick="window.print()">
                <i class="feather-printer"></i> Print
            </button>
            
            <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}" class="btn btn-sm btn-light">
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
                            'draft' => 'secondary',
                            'pending_approval' => 'warning',
                            'approved' => 'info',
                            'ordered' => 'primary',
                            'partially_received' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ][$purchaseOrder->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }} ms-2">
                        {{ str_replace('_', ' ', ucwords($purchaseOrder->status)) }}
                    </span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">PO Number:</span>
                    <span class="fw-semibold ms-2">{{ $purchaseOrder->po_number }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Order Date:</span>
                    <span class="ms-2">{{ $purchaseOrder->order_date->format('d/m/Y') }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Expected Delivery:</span>
                    <span class="ms-2">{{ $purchaseOrder->expected_delivery_date?->format('d/m/Y') ?? 'Not set' }}</span>
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
                    <h6 class="fw-semibold">{{ $purchaseOrder->supplier->name }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Code:</span>
                    <h6>{{ $purchaseOrder->supplier->supplier_code }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Tax Number:</span>
                    <h6>{{ $purchaseOrder->supplier->tax_number ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Payment Terms:</span>
                    <h6>{{ str_replace('_', ' ', ucwords($purchaseOrder->payment_terms)) }}</h6>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <span class="text-muted small">Contact Person:</span>
                    <h6>{{ $purchaseOrder->supplier->contact_person ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Email:</span>
                    <h6>{{ $purchaseOrder->supplier->email ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">Phone:</span>
                    <h6>{{ $purchaseOrder->supplier->phone ?? 'N/A' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Order Items</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th>Unit</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-end">Tax Amount</th>
                            <th class="text-end">Discount %</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 0) }}</td>
                            <td class="text-end">{{ $item->tax_rate }}%</td>
                            <td class="text-end">{{ number_format($item->tax_amount, 0) }}</td>
                            <td class="text-end">{{ $item->discount_rate }}%</td>
                            <td class="text-end">{{ number_format($item->discount_amount, 0) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->total, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="9" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end">{{ number_format($purchaseOrder->subtotal, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="9" class="text-end">Tax Total:</td>
                            <td class="text-end">{{ number_format($purchaseOrder->tax_amount, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="9" class="text-end">Discount Total:</td>
                            <td class="text-end">{{ number_format($purchaseOrder->discount_amount, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="9" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($purchaseOrder->total_amount, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Shipping & Billing -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Shipping Address</h6>
                </div>
                <div class="card-body">
                    <p class="small">{{ $purchaseOrder->shipping_address ?? 'Not specified' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Billing Address</h6>
                </div>
                <div class="card-body">
                    <p class="small">{{ $purchaseOrder->billing_address ?? 'Not specified' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes & Audit -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Notes</h6>
                </div>
                <div class="card-body">
                    <p class="small">{{ $purchaseOrder->notes ?? 'No notes' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Audit Trail</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div><span class="text-muted">Created By:</span> {{ $purchaseOrder->creator->name ?? 'System' }}</div>
                        <div><span class="text-muted">Created At:</span> {{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</div>
                        
                        @if($purchaseOrder->approved_by)
                        <div class="mt-2"><span class="text-muted">Approved By:</span> {{ $purchaseOrder->approver->name ?? 'N/A' }}</div>
                        <div><span class="text-muted">Approved At:</span> {{ $purchaseOrder->approved_at?->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitForApproval() {
    if (confirm('Submit this purchase order for approval?')) {
        fetch('{{ route("finance.accounts-payable.purchase-orders.submit", $purchaseOrder->id) }}', {
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

function approvePO() {
    if (confirm('Approve this purchase order?')) {
        fetch('{{ route("finance.accounts-payable.purchase-orders.approve", $purchaseOrder->id) }}', {
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

function rejectPO() {
    const reason = prompt('Please enter rejection reason:');
    if (reason) {
        fetch('{{ route("finance.accounts-payable.purchase-orders.reject", $purchaseOrder->id) }}', {
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
</script>
@endpush
@endsection