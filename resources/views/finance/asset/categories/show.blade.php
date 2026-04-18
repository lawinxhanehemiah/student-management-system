@extends('layouts.financecontroller')

@section('title', 'Asset Category Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Category Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.categories.index') }}">Categories</a></li>
                <li class="breadcrumb-item active">{{ $category->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.categories.edit', $category->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('finance.asset.categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Category Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Code:</span>
                        <span class="value fw-bold">{{ $category->code }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Name:</span>
                        <span class="value">{{ $category->name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Depreciation Method:</span>
                        <span class="value">{{ $category->depreciation_method_name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Useful Life (Years):</span>
                        <span class="value">{{ $category->default_useful_life_years ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Salvage Value:</span>
                        <span class="value">{{ $category->default_salvage_value_percentage ?? 0 }}%</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">
                            @if($category->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $category->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $category->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                @if($category->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $category->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Total Assets</h6>
                        <h3>{{ number_format($stats['total_assets']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Total Value</h6>
                        <h3>{{ number_format($stats['total_value'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>Active Assets</h6>
                        <h3>{{ number_format($stats['active_assets']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assets in this Category -->
        <div class="card">
            <div class="card-header">
                <h5>Assets in this Category</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Asset Tag</th>
                                <th>Name</th>
                                <th>Purchase Date</th>
                                <th>Purchase Cost</th>
                                <th>Current Value</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($category->assets as $asset)
                                <tr>
                                    <td><strong>{{ $asset->asset_tag }}</strong></td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->purchase_date->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($asset->purchase_cost, 2) }}</td>
                                    <td class="text-end">{{ number_format($asset->current_value, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $asset->status == 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('finance.asset.assets.show', $asset->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">
                                        <p class="text-muted">No assets in this category</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection