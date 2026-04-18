@extends('layouts.financecontroller')

@section('title', 'Bank Accounts')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Bank Accounts</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Bank & Cash</a></li>
                <li class="breadcrumb-item active">Bank Accounts</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.bank.accounts.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>Add Bank Account
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-2 mb-4">
    <!-- Total Balance -->
    <div class="col-xl-4 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-primary">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">TOTAL BALANCE</div>
                        <div class="stat-number">{{ number_format($totalBalance, 2) }}</div>
                        <small class="text-muted">
                            All bank accounts combined
                        </small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Accounts -->
    <div class="col-xl-4 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">ACTIVE ACCOUNTS</div>
                        <div class="stat-number">{{ $accounts->where('is_active', true)->count() }}</div>
                        <small class="text-muted">
                            {{ $accounts->where('is_active', false)->count() }} inactive
                        </small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Accounts -->
    <div class="col-xl-4 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-info">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">TOTAL ACCOUNTS</div>
                        <div class="stat-number">{{ $accounts->total() }}</div>
                        <small class="text-muted">
                            Across {{ count($currencies) }} currencies
                        </small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-university"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.bank.accounts.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Bank name, account name, number..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select">
                        <option value="">All Currencies</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}" {{ request('currency') == $currency ? 'selected' : '' }}>
                                {{ $currency }}
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
                    <a href="{{ route('finance.bank.accounts.index') }}" class="btn btn-reset">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Bank Accounts Table with Logos -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>Account Details</th>
                        <th>Branch</th>
                        <th>Currency</th>
                        <th class="text-end">Current Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Bank Logo with Fallback -->
                                    @php
                                        // Bank logos mapping
                                        $bankLogos = [
                                            'NMB' => 'nmb-bank.png',
                                            'CRDB' => 'crdb-bank.svg',
                                            'NBC' => 'nbc-bank.jpg',
                                            'Exim' => 'exim-bank.webp',
                                            'Amana' => 'amana-bank.png',
                                            'Stanbic' => 'stanbic-bank.png',
                                            'Standard' => 'standard-chartered.png',
                                            'Barclays' => 'barclays.png',
                                            'DTB' => 'dtb.png',
                                            'KCB' => 'kcb.png',
                                            'Equity' => 'equity-bank.png',
                                            'BOA' => 'boa.png',
                                            'TIB' => 'tib.png',
                                            'TWB' => 'twb.png',
                                            'Azania' => 'azania-bank.png',
                                            'Mkombozi' => 'mkombozi-bank.png',
                                            'Yetu' => 'yetu-bank.png',
                                            'Postal' => 'postal-bank.png',
                                            'Peoples' => 'peoples-bank.png',
                                            'Access' => 'access-bank.png',
                                            'ABS' => 'abs-bank.png',
                                            'Maendeleo' => 'maendeleo-bank.png',
                                            'Kilimanjaro' => 'kilimanjaro-bank.png',
                                            'Mbinga' => 'mbinga-bank.png',
                                            'Kagera' => 'kagera-bank.png',
                                        ];
                                        
                                        // Extract first word of bank name
                                        $bankKey = explode(' ', trim($account->bank_name))[0];
                                        
                                        // Get logo file or use default
                                        $logoFile = $bankLogos[$bankKey] ?? 'default-bank.png';
                                        
                                        // Construct full path
                                        $logoPath = asset('assets/img/banks/' . $logoFile);
                                    @endphp
                                    
                                    <div class="bank-logo-container me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <img src="{{ $logoPath }}" 
                                             alt="{{ $account->bank_name }}" 
                                             class="img-fluid bank-logo"
                                             style="max-height: 35px; max-width: 35px; object-fit: contain;"
                                             onerror="this.onerror=null; this.style.display='none'; this.parentNode.querySelector('.fallback-icon').style.display='inline-block';">
                                        
                                        <i class="fas fa-university fa-2x text-primary fallback-icon" style="display: none;"></i>
                                    </div>
                                    
                                    <div>
                                        <strong>{{ $account->bank_name }}</strong>
                                        @if($account->is_default)
                                            <span class="badge bg-info ms-1">Default</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $account->account_name }}</strong>
                                </div>
                                <small class="text-muted">{{ $account->account_number }}</small>
                                @if($account->swift_code)
                                    <br><small class="text-muted">SWIFT: {{ $account->swift_code }}</small>
                                @endif
                            </td>
                            <td>{{ $account->branch ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $account->currency }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-{{ $account->current_balance > 0 ? 'success' : 'danger' }}">
                                    {{ number_format($account->current_balance, 2) }}
                                </strong>
                            </td>
                            <td>{!! $account->status_badge !!}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('finance.bank.accounts.show', $account->id) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.bank.accounts.edit', $account->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('finance.bank.accounts.statement', $account->id) }}" 
                                       class="btn btn-sm btn-secondary" title="Statement">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <img src="{{ asset('assets/img/no-data.svg') }}" alt="No data" height="100">
                                <p class="mt-3">No bank accounts found</p>
                                <a href="{{ route('finance.bank.accounts.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Your First Bank Account
                                </a>
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

@push('styles')
<style>
.bank-logo-container {
    transition: transform 0.2s;
}



.bank-logo-container:hover {
    transform: scale(1.1);
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.fallback-icon {
    font-size: 1.8rem;
}
</style>
@endpush

@push('scripts')
<script>
function toggleStatus(id) {
    if(confirm('Are you sure you want to change this bank account status?')) {
        const form = document.getElementById('toggle-status-form');
        form.action = '{{ url("finance/bank/accounts") }}/' + id + '/toggle-status';
        form.submit();
    }
}
</script>
@endpush