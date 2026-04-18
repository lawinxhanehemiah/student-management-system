@extends('layouts.financecontroller')

@section('title', 'Depreciation Schedule')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Depreciation Schedule</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Asset Management</a></li>
                <li class="breadcrumb-item active">Depreciation</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.depreciation.create') }}" class="btn btn-success">
            <i class="fas fa-calculator me-2"></i>Calculate Depreciation
        </a>
        <a href="{{ route('finance.asset.depreciation.run') }}" class="btn btn-primary">
            <i class="fas fa-play me-2"></i>Run for All Assets
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.asset.depreciation.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="asset_id" class="form-label">Asset</label>
                <select name="asset_id" id="asset_id" class="form-select">
                    <option value="">All Assets</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->asset_tag }} - {{ $asset->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Depreciation Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Asset</th>
                        <th>Category</th>
                        <th>Period</th>
                        <th>Method</th>
                        <th class="text-end">Period Depr.</th>
                        <th class="text-end">Accumulated</th>
                        <th class="text-end">Book Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depreciations as $dep)
                        <tr>
                            <td>{{ $dep->depreciation_date->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $dep->asset->asset_tag }}</strong><br>
                                <small>{{ $dep->asset->name }}</small>
                            </td>
                            <td>{{ $dep->asset->category->name ?? 'N/A' }}</td>
                            <td>{{ $dep->period_number }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $dep->method == 'straight_line' ? 'Straight Line' : 'Declining' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($dep->period_depreciation, 2) }}</td>
                            <td class="text-end">{{ number_format($dep->accumulated_depreciation, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($dep->book_value, 2) }}</td>
                            <td>
                                <a href="{{ route('finance.asset.depreciation.show', $dep->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted">No depreciation records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $depreciations->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection