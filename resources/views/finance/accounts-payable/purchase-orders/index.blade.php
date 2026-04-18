@extends('layouts.financecontroller')

@section('title', 'Purchase Orders')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Purchase Orders</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Accounts Payable</a> > 
                <span>Purchase Orders</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.purchase-orders.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New PO
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Purchase Orders</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.purchase-orders.index') }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="PO Number or Supplier">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="supplier_id">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', ucwords($status)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_from" 
                               value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_to" 
                               value="{{ request('date_to') }}" placeholder="To">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                        <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}" class="btn btn-sm btn-light">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- POs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Purchase Orders List</h6>
            <span class="badge bg-secondary">{{ $purchaseOrders->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
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
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('finance.accounts-payable.purchase-orders.show', $po->id) }}" class="fw-semibold">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td>{{ $po->supplier->name }}</td>
                            <td>{{ $po->order_date->format('d/m/Y') }}</td>
                            <td>{{ $po->expected_delivery_date?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="text-end">TZS {{ number_format($po->total_amount, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($po->paid_amount, 0) }}</td>
                            <td class="text-end {{ $po->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($po->balance, 0) }}
                            </td>
                            <td>
                                @php
                                    $statusColor = [
                                        'draft' => 'secondary',
                                        'pending_approval' => 'warning',
                                        'approved' => 'info',
                                        'ordered' => 'primary',
                                        'partially_received' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ][$po->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ str_replace('_', ' ', ucwords($po->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.accounts-payable.purchase-orders.show', $po->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" title="View">
                                        <i class="feather-eye"></i>
                                    </a>
                                    @if(in_array($po->status, ['draft', 'pending_approval']))
                                    <a href="{{ route('finance.accounts-payable.purchase-orders.edit', $po->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" title="Edit">
                                        <i class="feather-edit"></i>
                                    </a>
                                    @endif
                                    @if($po->status == 'approved')
                                    <a href="{{ route('finance.accounts-payable.grn.create') }}?purchase_order_id={{ $po->id }}" 
                                       class="btn btn-sm btn-icon btn-light" title="Receive Goods">
                                        <i class="feather-package"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No purchase orders found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $purchaseOrders->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection