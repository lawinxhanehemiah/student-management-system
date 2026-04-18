@extends('layouts.financecontroller')

@section('title', 'Supplier Details - ' . $supplier->name)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Supplier Details</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="text-muted">Suppliers</a> > 
                <span>{{ $supplier->supplier_code }}</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary">
                <i class="feather-edit"></i> Edit
            </a>
            <a href="{{ route('finance.accounts-payable.suppliers.statement', $supplier->id) }}" class="btn btn-sm btn-info-light">
                <i class="feather-file-text"></i> Statement
            </a>
            <a href="{{ route('finance.accounts-payable.purchase-orders.create') }}?supplier_id={{ $supplier->id }}" 
               class="btn btn-sm btn-success-light">
                <i class="feather-plus"></i> New PO
            </a>
            <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Supplier Info Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <span class="text-muted small">Supplier Code</span>
                    <h6 class="fw-semibold">{{ $supplier->supplier_code }}</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Name</span>
                    <h6 class="fw-semibold">{{ $supplier->name }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Tax Number</span>
                    <h6>{{ $supplier->tax_number ?? 'N/A' }}</h6>
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Status</span><br>
                    @if($supplier->status == 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <div class="col-md-2">
                    <span class="text-muted small">Payment Terms</span>
                    <h6>{{ str_replace('_', ' ', strtoupper($supplier->payment_terms ?? 'N/A')) }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Current Balance</span>
                            <h4 class="mb-0 fw-semibold {{ $stats['outstanding_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($supplier->current_balance, 0) }}
                            </h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total POs</span>
                            <h4 class="mb-0 fw-semibold">{{ $stats['total_purchase_orders'] }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Invoices</span>
                            <h4 class="mb-0 fw-semibold">{{ $stats['total_invoices'] }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-file-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Paid</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($stats['total_paid'], 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Contact Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted" style="width: 120px;">Contact Person:</td>
                            <td class="fw-semibold">{{ $supplier->contact_person ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $supplier->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>{{ $supplier->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address:</td>
                            <td>{{ $supplier->address ?? 'N/A' }}{{ $supplier->city ? ', ' . $supplier->city : '' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Country:</td>
                            <td>{{ $supplier->country ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Bank Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted" style="width: 120px;">Bank Name:</td>
                            <td class="fw-semibold">{{ $supplier->bank_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Number:</td>
                            <td>{{ $supplier->bank_account ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Branch:</td>
                            <td>{{ $supplier->bank_branch ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Credit Limit:</td>
                            <td>TZS {{ number_format($supplier->credit_limit, 0) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Recent Purchase Orders</h6>
            <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}?supplier_id={{ $supplier->id }}" 
               class="btn btn-sm btn-link">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>PO Number</th>
                            <th>Order Date</th>
                            <th>Expected Delivery</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('finance.accounts-payable.purchase-orders.show', $po->id) }}">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td>{{ $po->order_date->format('d/m/Y') }}</td>
                            <td>{{ $po->expected_delivery_date?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="text-end">TZS {{ number_format($po->total_amount, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($po->paid_amount, 0) }}</td>
                            <td class="text-end {{ $po->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($po->balance, 0) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $po->status == 'completed' ? 'success' : ($po->status == 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ str_replace('_', ' ', ucwords($po->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.accounts-payable.purchase-orders.show', $po->id) }}" 
                                   class="btn btn-sm btn-icon btn-light">
                                    <i class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-3">
                                <p class="text-muted small mb-0">No purchase orders found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Recent Invoices</h6>
            <a href="{{ route('finance.accounts-payable.invoices.index') }}?supplier_id={{ $supplier->id }}" 
               class="btn btn-sm btn-link">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier Invoice</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('finance.accounts-payable.invoices.show', $invoice->id) }}">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $invoice->supplier_invoice_number }}</td>
                            <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td class="text-end">TZS {{ number_format($invoice->total_amount, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($invoice->paid_amount, 0) }}</td>
                            <td class="text-end {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($invoice->balance, 0) }}
                            </td>
                            <td>
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
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ str_replace('_', ' ', ucwords($invoice->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.accounts-payable.invoices.show', $invoice->id) }}" 
                                   class="btn btn-sm btn-icon btn-light">
                                    <i class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-3">
                                <p class="text-muted small mb-0">No invoices found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle supplier status
function toggleStatus() {
    if(confirm('Are you sure you want to change supplier status?')) {
        window.location.href = '{{ route("finance.accounts-payable.suppliers.toggle-status", $supplier->id) }}';
    }
}
</script>
@endpush
@endsection