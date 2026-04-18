@extends('layouts.financecontroller')

@section('title', 'Payment Vouchers')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Payment Vouchers</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Accounts Payable</a> > 
                <span>Payment Vouchers</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.payment-vouchers.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New Payment Voucher
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Payment Vouchers</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.payment-vouchers.index') }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="Voucher #, Supplier...">
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
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_from" 
                               value="{{ request('date_from') }}" placeholder="From">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_to" 
                               value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-10 text-end">
                        <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                        <a href="{{ route('finance.accounts-payable.payment-vouchers.index') }}" class="btn btn-sm btn-light">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Payment Vouchers List</h6>
            <span class="badge bg-secondary">{{ $vouchers->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Voucher #</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                        <tr>
                            <td>
                                <a href="{{ route('finance.accounts-payable.payment-vouchers.show', $voucher->id) }}" class="fw-semibold">
                                    {{ $voucher->voucher_number }}
                                </a>
                            </td>
                            <td>{{ $voucher->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $voucher->supplier->name }}</td>
                            <td>
                                @if($voucher->supplierInvoice)
                                    {{ $voucher->supplierInvoice->invoice_number }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ str_replace('_', ' ', ucwords($voucher->payment_method)) }}</td>
                            <td>{{ $voucher->reference_number ?? 'N/A' }}</td>
                            <td class="text-end fw-semibold">TZS {{ number_format($voucher->amount, 0) }}</td>
                            <td>
                                @php
                                    $statusColor = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'paid' => 'success',
                                        'cancelled' => 'danger'
                                    ][$voucher->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucwords($voucher->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.accounts-payable.payment-vouchers.show', $voucher->id) }}" 
                                   class="btn btn-sm btn-icon btn-light" title="View">
                                    <i class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No payment vouchers found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $vouchers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection