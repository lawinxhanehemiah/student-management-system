@extends('layouts.financecontroller')

@section('title', 'Suppliers')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Suppliers</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Accounts Payable</a> > 
                <span>Suppliers</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-payable.suppliers.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New Supplier
            </a>
            <button class="btn btn-sm btn-success-light" onclick="exportSuppliers()">
                <i class="feather-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Suppliers</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.suppliers.index') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="Search by name, code, email...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="city">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Suppliers List</h6>
            <span class="badge bg-secondary">{{ $suppliers->total() }} suppliers</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th class="text-end">Credit Limit</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark">{{ $supplier->supplier_code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('finance.accounts-payable.suppliers.show', $supplier->id) }}" 
                                   class="text-dark fw-semibold">
                                    {{ $supplier->name }}
                                </a>
                            </td>
                            <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                            <td>{{ $supplier->email ?? 'N/A' }}</td>
                            <td>{{ $supplier->phone ?? 'N/A' }}</td>
                            <td>{{ $supplier->city ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($supplier->credit_limit, 0) }}</td>
                            <td class="text-end {{ $supplier->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($supplier->current_balance, 0) }}
                            </td>
                            <td>
                                @if($supplier->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.accounts-payable.suppliers.show', $supplier->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" data-bs-toggle="tooltip" title="View">
                                        <i class="feather-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.accounts-payable.suppliers.edit', $supplier->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" data-bs-toggle="tooltip" title="Edit">
                                        <i class="feather-edit"></i>
                                    </a>
                                    <a href="{{ route('finance.accounts-payable.suppliers.statement', $supplier->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" data-bs-toggle="tooltip" title="Statement">
                                        <i class="feather-file-text"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No suppliers found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $suppliers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportSuppliers() {
    const params = new URLSearchParams(window.location.search).toString();
    window.location.href = '{{ route("finance.accounts-payable.suppliers.export") }}?' + params;
}
</script>
@endpush
@endsection