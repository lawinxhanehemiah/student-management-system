@extends('layouts.financecontroller')

@section('title', 'GRN - ' . $grn->grn_number)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Goods Received Note</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.grn.index') }}" class="text-muted">GRN</a> > 
                <span>{{ $grn->grn_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            @if($grn->status == 'completed')
            <a href="{{ route('finance.accounts-payable.invoices.create') }}?grn_id={{ $grn->id }}" 
               class="btn btn-sm btn-primary">
                <i class="feather-file-plus"></i> Create Invoice
            </a>
            @endif
            <button class="btn btn-sm btn-primary-light" onclick="window.print()">
                <i class="feather-printer"></i> Print
            </button>
            <a href="{{ route('finance.accounts-payable.grn.index') }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- GRN Info -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <span class="text-muted small">GRN Number:</span>
                    <h6 class="fw-semibold">{{ $grn->grn_number }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Status:</span><br>
                    @if($grn->status == 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($grn->status == 'draft')
                        <span class="badge bg-warning">Draft</span>
                    @else
                        <span class="badge bg-danger">Cancelled</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Receipt Date:</span>
                    <h6>{{ $grn->receipt_date->format('d/m/Y') }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Delivery Note:</span>
                    <h6>{{ $grn->delivery_note_number ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Received By:</span>
                    <h6>{{ $grn->received_by ?? 'N/A' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Reference -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Purchase Order Reference</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <span class="text-muted small">PO Number:</span>
                    <h6>
                        <a href="{{ route('finance.accounts-payable.purchase-orders.show', $grn->purchaseOrder->id) }}">
                            {{ $grn->purchaseOrder->po_number }}
                        </a>
                    </h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Supplier:</span>
                    <h6>{{ $grn->supplier->name }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Order Date:</span>
                    <h6>{{ $grn->purchaseOrder->order_date->format('d/m/Y') }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Total Amount:</span>
                    <h6 class="text-primary">TZS {{ number_format($grn->purchaseOrder->total_amount, 0) }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Received Items</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Ordered</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Accepted</th>
                            <th class="text-end">Rejected</th>
                            <th>Reason</th>
                            <th>Batch No</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grn->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->purchaseOrderItem->description }}</td>
                            <td class="text-end">{{ number_format($item->purchaseOrderItem->quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->quantity_received, 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($item->quantity_accepted, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($item->quantity_rejected, 2) }}</td>
                            <td>{{ $item->rejection_reason ?? 'N/A' }}</td>
                            <td>{{ $item->batch_number ?? 'N/A' }}</td>
                            <td>{{ $item->expiry_date ? $item->expiry_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($grn->notes)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Notes</h6>
        </div>
        <div class="card-body">
            <p class="small">{{ $grn->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection