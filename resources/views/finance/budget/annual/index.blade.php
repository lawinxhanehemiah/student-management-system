@extends('layouts.financecontroller')

@section('title', 'Budget Years')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Budget Years</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="#" class="text-muted">Budget Management</a> > 
                <span>Budget Years</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.budget.years.create') }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> New Budget Year
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Budget Years</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.budget.years.index') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               value="{{ request('search') }}" placeholder="Search by name...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Budget Years Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Budget Years List</h6>
            <span class="badge bg-secondary">{{ $budgetYears->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Period</th>
                            <th class="text-end">Total Budget</th>
                            <th class="text-end">Allocated</th>
                            <th class="text-end">Utilized</th>
                            <th class="text-end">Remaining</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgetYears as $year)
                        <tr>
                            <td>
                                <a href="{{ route('finance.budget.years.show', $year->id) }}" class="fw-semibold">
                                    {{ $year->name }}
                                </a>
                            </td>
                            <td>
                                <small>{{ $year->start_date->format('d/m/Y') }} - {{ $year->end_date->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">TZS {{ number_format($year->total_budget, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($year->total_allocated, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($year->total_utilized, 0) }}</td>
                            <td class="text-end {{ $year->total_remaining > 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($year->total_remaining, 0) }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span>{{ $year->progress }}%</span>
                                    <div class="progress progress-sm" style="width: 50px; height: 4px;">
                                        <div class="progress-bar {{ $year->progress >= 100 ? 'bg-danger' : ($year->progress >= 80 ? 'bg-warning' : 'bg-success') }}" 
                                             style="width: {{ min($year->progress, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $year->status_color }}">
                                    {{ ucwords($year->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="hstack gap-1">
                                    <a href="{{ route('finance.budget.years.show', $year->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" title="View">
                                        <i class="feather-eye"></i>
                                    </a>
                                    @if($year->isDraft())
                                    <a href="{{ route('finance.budget.years.edit', $year->id) }}" 
                                       class="btn btn-sm btn-icon btn-light" title="Edit">
                                        <i class="feather-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No budget years found</p>
                                <a href="{{ route('finance.budget.years.create') }}" class="btn btn-sm btn-primary mt-2">
                                    Create First Budget Year
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $budgetYears->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// For AJAX requests (if needed for the select budget year functionality)
function getBudgetYearsForSelect() {
    return @json($budgetYears->items());
}
</script>
@endpush
@endsection