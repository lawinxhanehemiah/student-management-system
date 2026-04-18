@extends('layouts.financecontroller')

@section('title', 'Fiscal Years')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Fiscal Years</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item active">Fiscal Years</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.general-ledger.fiscal-years.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Fiscal Year
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.fiscal-years.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Year name..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-lg-5 col-md-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('finance.general-ledger.fiscal-years.index') }}" class="btn btn-reset">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Fiscal Years Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Journals</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fiscalYears as $year)
                        <tr>
                            <td><strong>{{ $year->name }}</strong></td>
                            <td>
                                {{ $year->start_date->format('d M Y') }} - 
                                {{ $year->end_date->format('d M Y') }}
                            </td>
                            <td>{!! $year->status_badge !!}</td>
                            <td>{!! $year->active_badge !!}</td>
                            <td>
                                <span class="badge bg-info">{{ $year->journals_count ?? 0 }}</span>
                            </td>
                            <td>{{ $year->creator->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('finance.general-ledger.fiscal-years.show', $year->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($year->status == 'open')
                                    <a href="{{ route('finance.general-ledger.fiscal-years.edit', $year->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if(!$year->is_active)
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="setActive({{ $year->id }})" title="Set as Active">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="closeYear({{ $year->id }})" title="Close Year">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" 
                                            onclick="reopenYear({{ $year->id }})" title="Reopen Year">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No fiscal years found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $fiscalYears->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Action Forms -->
<form id="set-active-form" method="POST" style="display: none;">
    @csrf
</form>
<form id="close-form" method="POST" style="display: none;">
    @csrf
</form>
<form id="reopen-form" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
function setActive(id) {
    if(confirm('Set this fiscal year as active?')) {
        const form = document.getElementById('set-active-form');
        form.action = '{{ url("finance/general-ledger/fiscal-years") }}/' + id + '/set-active';
        form.submit();
    }
}

function closeYear(id) {
    if(confirm('Are you sure you want to close this fiscal year? This will create closing entries and cannot be undone easily.')) {
        const form = document.getElementById('close-form');
        form.action = '{{ url("finance/general-ledger/fiscal-years") }}/' + id + '/close';
        form.submit();
    }
}

function reopenYear(id) {
    if(confirm('Are you sure you want to reopen this fiscal year? This should only be done if no transactions exist in later periods.')) {
        const form = document.getElementById('reopen-form');
        form.action = '{{ url("finance/general-ledger/fiscal-years") }}/' + id + '/reopen';
        form.submit();
    }
}
</script>
@endpush