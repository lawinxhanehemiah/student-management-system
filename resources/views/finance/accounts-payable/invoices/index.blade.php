@extends('layouts.financecontroller')

@section('title', 'Supplier Invoices')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Supplier Invoices</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Accounts Payable</a> > 
                <span>Invoices</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.invoices.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New Invoice
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Invoices</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.invoices.index') }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="Invoice #, Supplier...">
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
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="overdue" value="1" 
                                   {{ request('overdue') ? 'checked' : '' }} id="overdue">
                            <label class="form-check-label small" for="overdue">Show Overdue Only</label>
                        </div>
                    </div>
                    <div class="col-md-10 text-end">
                        <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                        <a href="{{ route('finance.accounts-payable.invoices.index') }}" class="btn btn-sm btn-light">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Supplier Invoices List</h6>
            <span class="badge bg-secondary">{{ $invoices->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier Invoice</th>
                            <th>Supplier</th>
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
                        @forelse($invoices as $invoice)
                        @php
                            $isOverdue = $invoice->due_date < now() && $invoice->balance > 0 && !in_array($invoice->status, ['paid', 'cancelled']);
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('finance.accounts-payable.invoices.show', $invoice->id) }}" class="fw-semibold">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $invoice->supplier_invoice_number }}</td>
                            <td>{{ $invoice->supplier->name }}</td>
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
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.accounts-payable.invoices.show', $invoice->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" title="View">
                                        <i class="feather-eye"></i>
                                    </a>
                                    @if(in_array($invoice->status, ['approved', 'partial_paid']) && $invoice->balance > 0)
                                    <a href="{{ route('finance.accounts-payable.payment-vouchers.create') }}?invoice_id={{ $invoice->id }}" 
                                       class="btn btn-sm btn-icon btn-light" title="Make Payment">
                                        <i class="feather-dollar-sign"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No supplier invoices found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection