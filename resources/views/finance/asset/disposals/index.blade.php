@extends('layouts.financecontroller')

@section('title', 'Asset Disposals')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Disposals</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Asset Management</a></li>
                <li class="breadcrumb-item active">Disposals</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.disposals.create') }}" class="btn btn-danger">
            <i class="fas fa-trash-alt me-2"></i>New Disposal
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.asset.disposals.index') }}" method="GET" class="row g-3">
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
                <label for="disposal_method" class="form-label">Method</label>
                <select name="disposal_method" id="disposal_method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="sold" {{ request('disposal_method') == 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="scrapped" {{ request('disposal_method') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
                    <option value="donated" {{ request('disposal_method') == 'donated' ? 'selected' : '' }}>Donated</option>
                    <option value="lost" {{ request('disposal_method') == 'lost' ? 'selected' : '' }}>Lost</option>
                    <option value="stolen" {{ request('disposal_method') == 'stolen' ? 'selected' : '' }}>Stolen</option>
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
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Disposals Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Asset</th>
                        <th>Method</th>
                        <th class="text-end">Book Value</th>
                        <th class="text-end">Disposal Amount</th>
                        <th class="text-end">Gain/Loss</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disposals as $disposal)
                        <tr>
                            <td>{{ $disposal->disposal_date->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $disposal->asset->asset_tag }}</strong><br>
                                <small>{{ $disposal->asset->name }}</small>
                            </td>
                            <td>{{ $disposal->disposal_method_name }}</td>
                            <td class="text-end">{{ number_format($disposal->book_value_at_disposal, 2) }}</td>
                            <td class="text-end">{{ number_format($disposal->disposal_amount ?? 0, 2) }}</td>
                            <td class="text-end {{ $disposal->gain_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($disposal->gain_loss, 2) }}
                            </td>
                            <td>{{ Str::limit($disposal->reason, 30) }}</td>
                            <td>
                                <a href="{{ route('finance.asset.disposals.show', $disposal->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No disposal records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $disposals->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection