@extends('layouts.financecontroller')

@section('title', 'Tender Management')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Tender Management</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                <li class="breadcrumb-item active">Tenders</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.procurement.tenders.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Tender
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Tenders</h6>
                <h3 class="text-white">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Open</h6>
                <h3 class="text-white">{{ number_format($stats['open']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Evaluating</h6>
                <h3 class="text-white">{{ number_format($stats['evaluating']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Awarded</h6>
                <h3 class="text-white">{{ number_format($stats['awarded']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.procurement.tenders.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by number or title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="evaluating" {{ request('status') == 'evaluating' ? 'selected' : '' }}>Evaluating</option>
                    <option value="awarded" {{ request('status') == 'awarded' ? 'selected' : '' }}>Awarded</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="open" {{ request('type') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('type') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="restricted" {{ request('type') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                    <option value="direct" {{ request('type') == 'direct' ? 'selected' : '' }}>Direct</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('finance.procurement.tenders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tenders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tender #</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Closing Date</th>
                        <th>Est. Value</th>
                        <th>Bids</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenders as $tender)
                        <tr>
                            <td><strong>{{ $tender->tender_number }}</strong></td>
                            <td>{{ $tender->title }}</td>
                            <td>{{ ucfirst($tender->type) }}</td>
                            <td>{{ $tender->closing_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($tender->estimated_value, 2) }}</td>
                            <td class="text-center">{{ $tender->bids_count ?? 0 }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'published' => 'success',
                                        'evaluating' => 'warning',
                                        'awarded' => 'info',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$tender->status] }}">
                                    {{ ucfirst($tender->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.procurement.tenders.show', $tender->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($tender->status == 'draft')
                                    <a href="{{ route('finance.procurement.tenders.edit', $tender->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No tenders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tenders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection