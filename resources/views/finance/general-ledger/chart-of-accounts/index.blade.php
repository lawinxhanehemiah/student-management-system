@extends('layouts.financecontroller')

@section('title', 'Chart of Accounts')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Chart of Accounts</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item active">Chart of Accounts</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.general-ledger.chart-of-accounts.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>Add New Account
        </a>
        <a href="{{ route('finance.general-ledger.chart-of-accounts.export') }}" class="btn btn-secondary">
            <i class="fas fa-download me-2"></i>Export
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.chart-of-accounts.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Account code or name..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="account_type" class="form-label">Account Type</label>
                    <select name="account_type" id="account_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($accountTypes as $key => $type)
                            <option value="{{ $key }}" {{ request('account_type') == $key ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('finance.general-ledger.chart-of-accounts.index') }}" class="btn btn-reset">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Accounts Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Level</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <strong>{{ $account->account_code }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($account->level > 1)
                                        <span style="margin-left: {{ ($account->level - 1) * 20 }}px">
                                            <i class="fas fa-level-down-alt text-muted me-2"></i>
                                    @endif
                                    {{ $account->account_name }}
                                    @if($account->level > 1)
                                        </span>
                                    @endif
                                    @if($account->is_header)
                                        <span class="badge bg-info ms-2">Header</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $account->account_type == 'asset' ? 'primary' : 
                                    ($account->account_type == 'liability' ? 'warning' : 
                                    ($account->account_type == 'equity' ? 'success' : 
                                    ($account->account_type == 'revenue' ? 'info' : 'danger'))) 
                                }}">
                                    {{ $account->type_name }}
                                </span>
                            </td>
                            <td>{{ $account->category_name ?? '-' }}</td>
                            <td>{{ $account->level }}</td>
                            <td class="text-end">
                                {{ number_format($account->current_balance, 2) }}
                            </td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.general-ledger.chart-of-accounts.show', $account->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$account->journal_lines_count)
                                    <a href="{{ route('finance.general-ledger.chart-of-accounts.edit', $account->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($account->is_active)
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="toggleStatus({{ $account->id }})" title="Deactivate">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="toggleStatus({{ $account->id }})" title="Activate">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No accounts found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $accounts->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Toggle Status Form -->
<form id="toggle-status-form" method="POST" style="display: none;">
    @csrf
</form>

@endsection

@push('scripts')
<script>
function toggleStatus(id) {
    if(confirm('Are you sure you want to change account status?')) {
        const form = document.getElementById('toggle-status-form');
        form.action = '{{ url("finance/general-ledger/chart-of-accounts") }}/' + id + '/toggle-status';
        form.submit();
    }
}
</script>
@endpush