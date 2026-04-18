@extends('layouts.financecontroller')

@section('title', 'Asset Register')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Register</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Asset Management</a></li>
                <li class="breadcrumb-item active">Asset Register</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.assets.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>Add Asset
        </a>
        <a href="{{ route('finance.asset.assets.export', request()->query()) }}" class="btn btn-secondary">
            <i class="fas fa-download me-2"></i>Export
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Assets</h6>
                <h3 class="text-white">{{ number_format($stats['total_assets']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Value</h6>
                <h3 class="text-white">{{ number_format($stats['total_value'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Active Assets</h6>
                <h3 class="text-white">{{ number_format($stats['active_assets']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Under Maintenance</h6>
                <h3 class="text-white">{{ number_format($stats['under_maintenance']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.asset.assets.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" 
                       placeholder="Asset tag, name, serial..." value="{{ request('search') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
                <a href="{{ route('finance.asset.assets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Assets Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Asset Tag</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>Purchase Date</th>
                        <th>Purchase Cost</th>
                        <th>Current Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td><strong>{{ $asset->asset_tag }}</strong></td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->category->name ?? 'N/A' }}</td>
                            <td>{{ $asset->department->name ?? 'N/A' }}</td>
                            <td>{{ $asset->purchase_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($asset->purchase_cost, 2) }}</td>
                            <td class="text-end">{{ number_format($asset->current_value, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $asset->status == 'active' ? 'success' : 
                                    ($asset->status == 'under_maintenance' ? 'warning' : 
                                    ($asset->status == 'disposed' ? 'danger' : 
                                    ($asset->status == 'transferred' ? 'info' : 'secondary'))) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.asset.assets.show', $asset->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$asset->isDisposed())
                                    <a href="{{ route('finance.asset.assets.edit', $asset->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('finance.asset.assets.depreciation.calculate', $asset->id) }}" 
                                       class="btn btn-sm btn-secondary" title="Calculate Depreciation">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted">No assets found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $assets->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection