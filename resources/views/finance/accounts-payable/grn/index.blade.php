@extends('layouts.financecontroller')

@section('title', 'Goods Received Notes')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Goods Received Notes</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Accounts Payable</a> > 
                <span>GRN</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.grn.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New GRN
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter GRN</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.grn.index') }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="GRN Number or Supplier">
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
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                        <a href="{{ route('finance.accounts-payable.grn.index') }}" class="btn btn-sm btn-light">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- GRN Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Goods Received Notes List</h6>
            <span class="badge bg-secondary">{{ $grns->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>GRN Number</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Receipt Date</th>
                            <th>Delivery Note</th>
                            <th>Received By</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grns as $grn)
                        <tr>
                            <td>
                                <a href="{{ route('finance.accounts-payable.grn.show', $grn->id) }}" class="fw-semibold">
                                    {{ $grn->grn_number }}
                                </a>
                            </td>
                            <td>{{ $grn->purchaseOrder->po_number }}</td>
                            <td>{{ $grn->supplier->name }}</td>
                            <td>{{ $grn->receipt_date->format('d/m/Y') }}</td>
                            <td>{{ $grn->delivery_note_number ?? 'N/A' }}</td>
                            <td>{{ $grn->received_by ?? 'N/A' }}</td>
                            <td>
                                @if($grn->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($grn->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.accounts-payable.grn.show', $grn->id) }}" 
                                   class="btn btn-sm btn-icon btn-light" title="View">
                                    <i class="feather-eye"></i>
                                </a>
                                @if($grn->status == 'completed')
                                <a href="{{ route('finance.accounts-payable.invoices.create') }}?grn_id={{ $grn->id }}" 
                                   class="btn btn-sm btn-icon btn-light" title="Create Invoice">
                                    <i class="feather-file-plus"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No GRN found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $grns->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection