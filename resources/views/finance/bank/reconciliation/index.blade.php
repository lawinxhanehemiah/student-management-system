@extends('layouts.financecontroller')

@section('title', 'Bank Reconciliation')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Bank Reconciliation</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Bank & Cash</a></li>
                <li class="breadcrumb-item active">Reconciliation</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.bank.reconciliation.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Reconciliation
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.bank.reconciliation.index') }}" method="GET" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label for="bank_account_id" class="form-label">Bank Account</label>
                <select name="bank_account_id" id="bank_account_id" class="form-select">
                    <option value="">All Accounts</option>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ request('bank_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->bank_name }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-select">
                    <option value="">All</option>
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select">
                    <option value="">All</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-12 d-flex align-items-end">
                <button type="submit" class="btn btn-filter me-2">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('finance.bank.reconciliation.index') }}" class="btn btn-reset">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Reconciliations</h6>
                        <h3 class="text-white">{{ $reconciliations->total() }}</h3>
                    </div>
                    <i class="fas fa-balance-scale fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Completed</h6>
                        <h3 class="text-white">{{ $reconciliations->where('status', 'completed')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Pending</h6>
                        <h3 class="text-white">{{ $reconciliations->where('status', 'in_progress')->count() }}</h3>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reconciliations Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Reconciliation #</th>
                        <th>Bank Account</th>
                        <th>Statement Date</th>
                        <th class="text-end">Statement Balance</th>
                        <th class="text-end">System Balance</th>
                        <th class="text-end">Difference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliations as $rec)
                        <tr>
                            <td>
                                <strong>{{ $rec->reconciliation_number }}</strong>
                            </td>
                            <td>
                                {{ $rec->bankAccount->bank_name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $rec->bankAccount->account_number ?? '' }}</small>
                            </td>
                            <td>{{ $rec->statement_date->format('d M Y') }}</td>
                            <td class="text-end">{{ number_format($rec->statement_balance, 2) }}</td>
                            <td class="text-end">{{ number_format($rec->system_balance, 2) }}</td>
                            <td class="text-end {{ $rec->difference > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($rec->difference, 2) }}
                            </td>
                            <td>
                                @if($rec->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($rec->status == 'in_progress')
                                    <span class="badge bg-warning">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.bank.reconciliation.show', $rec->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($rec->status == 'in_progress')
                                    <a href="{{ route('finance.bank.reconciliation.show', $rec->id) }}" 
                                       class="btn btn-sm btn-success" title="Complete">
                                        <i class="fas fa-check"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No reconciliations found</p>
                                <a href="{{ route('finance.bank.reconciliation.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Start New Reconciliation
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $reconciliations->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection