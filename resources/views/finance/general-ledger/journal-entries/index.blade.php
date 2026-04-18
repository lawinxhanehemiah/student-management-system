@extends('layouts.financecontroller')

@section('title', 'Journal Entries')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Journal Entries</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item active">Journal Entries</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.general-ledger.journal-entries.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Journal Entry
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.journal-entries.index') }}" method="GET" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Journal # or description..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" 
                           value="{{ request('from_date') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" 
                           value="{{ request('to_date') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All</option>
                        <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>System</option>
                        <option value="recurring" {{ request('type') == 'recurring' ? 'selected' : '' }}>Recurring</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="summary-card bg-light p-3 rounded">
                    <h6 class="text-muted">Total Debit</h6>
                    <h4 class="mb-0">{{ number_format($entries->sum('total_debit'), 2) }}</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card bg-light p-3 rounded">
                    <h6 class="text-muted">Total Credit</h6>
                    <h4 class="mb-0">{{ number_format($entries->sum('total_credit'), 2) }}</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card bg-light p-3 rounded">
                    <h6 class="text-muted">Posted</h6>
                    <h4 class="mb-0">{{ $entries->where('status', 'posted')->count() }}</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card bg-light p-3 rounded">
                    <h6 class="text-muted">Draft</h6>
                    <h4 class="mb-0">{{ $entries->where('status', 'draft')->count() }}</h4>
                </div>
            </div>
        </div>

        <!-- Journal Entries Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Journal #</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>
                                <strong>{{ $entry->journal_number }}</strong>
                            </td>
                            <td>{{ $entry->entry_date->format('d M Y') }}</td>
                            <td>{{ Str::limit($entry->description, 50) }}</td>
                            <td>{!! $entry->type_badge !!}</td>
                            <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            <td>{!! $entry->status_badge !!}</td>
                            <td>{{ $entry->creator->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('finance.general-ledger.journal-entries.show', $entry->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($entry->status == 'draft')
                                    <a href="{{ route('finance.general-ledger.journal-entries.edit', $entry->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="postEntry({{ $entry->id }})" title="Post">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="cancelEntry({{ $entry->id }})" title="Cancel">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No journal entries found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $entries->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Action Forms -->
<form id="post-form" method="POST" style="display: none;">
    @csrf
</form>
<form id="cancel-form" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
function postEntry(id) {
    if(confirm('Are you sure you want to post this journal entry? This action cannot be undone.')) {
        const form = document.getElementById('post-form');
        form.action = '{{ url("finance/general-ledger/journal-entries") }}/' + id + '/post';
        form.submit();
    }
}

function cancelEntry(id) {
    if(confirm('Are you sure you want to cancel this journal entry?')) {
        const form = document.getElementById('cancel-form');
        form.action = '{{ url("finance/general-ledger/journal-entries") }}/' + id + '/cancel';
        form.submit();
    }
}
</script>
@endpush