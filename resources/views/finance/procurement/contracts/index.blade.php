@extends('layouts.financecontroller')

@section('title', 'Contract Management')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Contract Management</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                <li class="breadcrumb-item active">Contracts</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.procurement.contracts.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Contract
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Contracts</h6>
                <h3 class="text-white">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Active</h6>
                <h3 class="text-white">{{ number_format($stats['active']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Value</h6>
                <h3 class="text-white">{{ number_format($stats['value'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Expiring Soon</h6>
                <h3 class="text-white">{{ number_format($stats['expiring']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.procurement.contracts.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by number or title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Contracts Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Contract #</th>
                        <th>Title</th>
                        <th>Supplier</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <td><strong>{{ $contract->contract_number }}</strong></td>
                            <td>{{ $contract->title }}</td>
                            <td>{{ $contract->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                            <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($contract->contract_value, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'active' => 'success',
                                        'completed' => 'info',
                                        'terminated' => 'danger',
                                        'expired' => 'warning'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$contract->status] }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.procurement.contracts.show', $contract->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($contract->status == 'draft')
                                    <a href="{{ route('finance.procurement.contracts.edit', $contract->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No contracts found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $contracts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection