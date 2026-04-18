{{-- resources/views/finance/dashboard.blade.php --}}
@extends('layouts.financecontroller')

@section('title', 'Finance Dashboard - System Overview')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Finance Controller Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt me-2"></i>{{ $period ?? 'This Month' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('finance.dashboard', ['period' => 'today', 'academic_year' => $selectedYearId]) }}">Today</a></li>
                        <li><a class="dropdown-item" href="{{ route('finance.dashboard', ['period' => 'week', 'academic_year' => $selectedYearId]) }}">This Week</a></li>
                        <li><a class="dropdown-item" href="{{ route('finance.dashboard', ['period' => 'month', 'academic_year' => $selectedYearId]) }}">This Month</a></li>
                        <li><a class="dropdown-item" href="{{ route('finance.dashboard', ['period' => 'quarter', 'academic_year' => $selectedYearId]) }}">This Quarter</a></li>
                        <li><a class="dropdown-item" href="{{ route('finance.dashboard', ['period' => 'year', 'academic_year' => $selectedYearId]) }}">This Year</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-outline-primary" id="refreshDashboard">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

   <!-- Academic Year Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <span class="fw-medium me-3">Academic Year:</span>
                        <form method="GET" action="{{ route('finance.dashboard') }}" id="yearFilterForm" class="d-flex align-items-center">
                            <select name="academic_year" class="form-select form-select-sm" style="width: 250px;" onchange="this.form.submit()">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} ({{ \Carbon\Carbon::parse($year->start_date)->format('M Y') }} - {{ \Carbon\Carbon::parse($year->end_date)->format('M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <noscript><button type="submit" class="btn btn-sm btn-primary ms-2">Go</button></noscript>
                        </form>
                    </div>
                    @if($selectedAcademicYear)
                        <span class="badge bg-info">Showing: {{ $selectedAcademicYear->name }}</span>
                    @else
                        <span class="badge bg-secondary">Showing: All Years</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Academic Year & Fiscal Year Info -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="alert alert-info mb-0 d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2 fa-lg"></i>
            <div>
                <strong>Current Academic Year:</strong> 
                @if($currentAcademicYear)
                    {{ $currentAcademicYear->name }} 
                    ({{ \Carbon\Carbon::parse($currentAcademicYear->start_date)->format('d M Y') }} - 
                     {{ \Carbon\Carbon::parse($currentAcademicYear->end_date)->format('d M Y') }})
                @else
                    <span class="text-muted">Not configured</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-success mb-0 d-flex align-items-center">
            <i class="fas fa-chart-line me-2 fa-lg"></i>
            <div>
                <strong>Fiscal Year:</strong> 
                @if($currentFiscalYear)
                    {{ $currentFiscalYear->name }} 
                    ({{ \Carbon\Carbon::parse($currentFiscalYear->start_date)->format('d M Y') }} - 
                     {{ \Carbon\Carbon::parse($currentFiscalYear->end_date)->format('d M Y') }})
                @else
                    <span class="text-muted">Not configured</span>
                @endif
            </div>
        </div>
    </div>
</div>

    <!-- ==================== FINANCIAL HEALTH CARDS ==================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title mb-1">CURRENT RATIO</div>
                            <div class="stat-number mb-2">{{ number_format($financialHealth['current_ratio'], 2) }}</div>
                            <small class="text-muted">
                                <i class="fas fa-circle text-primary me-1" style="font-size: 8px;"></i>
                                Assets: TZS {{ number_format($financialHealth['current_assets'], 0) }}
                            </small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title mb-1">BUDGET UTILIZATION</div>
                            <div class="stat-number mb-2">{{ $financialHealth['budget_utilization'] }}%</div>
                            <small class="text-muted">
                                <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>
                                Used: TZS {{ number_format($financialHealth['budget_used'], 0) }}
                            </small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-pie-chart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title mb-1">COLLECTION RATE</div>
                            <div class="stat-number mb-2">{{ $stats['collection_rate'] }}%</div>
                            <small class="text-muted">
                                <i class="fas fa-circle text-info me-1" style="font-size: 8px;"></i>
                                Collected: TZS {{ number_format($stats['total_paid'], 0) }}
                            </small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title mb-1">DAYS SALES OUTSTANDING</div>
                            <div class="stat-number mb-2">{{ $financialHealth['dso'] }}</div>
                            <small class="text-muted">
                                <i class="fas fa-circle text-warning me-1" style="font-size: 8px;"></i>
                                Avg. collection days
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== REVENUE SECTION ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-dollar-sign me-2 text-primary"></i>Revenue Overview</h5>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Total Invoiced</h6>
                            <h4 class="mb-0 fw-bold">TZS {{ number_format($stats['total_amount'], 0) }}</h4>
                            <small class="text-muted">{{ number_format($stats['total_invoices']) }} invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Collected</h6>
                            <h4 class="mb-0 fw-bold">TZS {{ number_format($stats['total_paid'], 0) }}</h4>
                            <small class="text-muted">{{ $stats['paid_percentage'] }}% of total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Outstanding</h6>
                            <h4 class="mb-0 fw-bold">TZS {{ number_format($stats['total_balance'], 0) }}</h4>
                            <small class="text-muted">{{ $stats['pending_invoices'] }} pending invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Overdue</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['overdue_invoices']) }}</h4>
                            <small class="text-muted">invoices overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== COLLECTIONS TIMELINE ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-chart-line me-2 text-success"></i>Collections Timeline</h5>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Monthly Chart -->
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monthly Collections Trend</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary chart-period active" data-period="monthly">Monthly</button>
                        <button class="btn btn-outline-primary chart-period" data-period="quarterly">Quarterly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Fee Distribution -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Revenue by Fee Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="feeDistributionChart" height="200"></canvas>
                    <div class="mt-4">
                        @foreach($feeDistribution as $type => $amount)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <span class="badge bg-{{ $loop->index == 0 ? 'primary' : ($loop->index == 1 ? 'warning' : ($loop->index == 2 ? 'info' : 'success')) }} me-2">●</span>
                                {{ $type }}
                            </span>
                            <span class="fw-bold">TZS {{ number_format($amount, 0) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== RECEIVABLES & PAYABLES ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-exchange-alt me-2 text-info"></i>Receivables & Payables</h5>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Aging Summary -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Accounts Receivable Aging</h5>
                </div>
                <div class="card-body">
                    <canvas id="agingChart" height="200"></canvas>
                    <div class="row mt-4">
                        @foreach($outstandingSummary as $period => $amount)
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small">{{ ucwords(str_replace('_', ' ', $period)) }}</span>
                                <span class="fw-bold {{ $amount > 0 ? 'text-danger' : '' }}">
                                    TZS {{ number_format($amount, 0) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Payables Summary -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Accounts Payable</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <h6 class="text-muted">Total Payables</h6>
                            <h3 class="fw-bold text-danger">TZS {{ number_format($payablesSummary['total'], 0) }}</h3>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted">Due Within 30 Days</h6>
                            <h3 class="fw-bold text-warning">TZS {{ number_format($payablesSummary['due_soon'], 0) }}</h3>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayables as $payable)
                                <tr>
                                    <td>{{ $payable->supplier->name ?? 'N/A' }}</td>
                                    <td class="text-end">TZS {{ number_format($payable->total_amount, 0) }}</td>
                                    <td class="text-end">
                                        @if($payable->due_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            {{ $payable->due_date->format('d/m/Y') }}
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3">No payables found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BANK & CASH ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-university me-2 text-warning"></i>Bank & Cash Position</h5>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Bank Balances -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Bank Accounts</h5>
                </div>
                <div class="card-body">
                    @foreach($bankAccounts as $account)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="mb-1">{{ $account->bank_name }}</h6>
                            <small class="text-muted">{{ $account->account_name }}</small>
                            @if($account->is_default)
                                <span class="badge bg-info ms-2">Default</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="fw-bold">{{ number_format($account->current_balance, 0) }}</span>
                            <br>
                            <small class="text-muted">{{ $account->currency }}</small>
                        </div>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <h6 class="mb-0">Total Cash Position</h6>
                        <h5 class="fw-bold text-success mb-0">TZS {{ number_format($totalCashPosition, 0) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Summary -->
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Cash Flow Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Opening Balance</h6>
                                <h5 class="fw-bold mb-0">TZS {{ number_format($cashFlowSummary['opening'], 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Net Cash Flow</h6>
                                <h5 class="fw-bold mb-0 {{ $cashFlowSummary['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TZS {{ number_format($cashFlowSummary['net'], 0) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Closing Balance</h6>
                                <h5 class="fw-bold mb-0">TZS {{ number_format($cashFlowSummary['closing'], 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <canvas id="cashFlowChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BUDGET & ASSETS ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-chart-pie me-2 text-danger"></i>Budget & Assets</h5>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Budget Status -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Budget vs Actual - Current Year</h5>
                </div>
                <div class="card-body">
                    <canvas id="budgetChart" height="200"></canvas>
                    <div class="row mt-4">
                        @foreach($budgetSummary as $item)
                        <div class="col-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small">{{ $item['category'] }}</span>
                                <span class="fw-bold {{ $item['variance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item['utilization'] }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-{{ $item['variance'] > 10 ? 'danger' : ($item['variance'] > 0 ? 'warning' : 'success') }}" 
                                     style="width: {{ min($item['utilization'], 100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Summary -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Asset Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <h6 class="text-muted">Total Assets</h6>
                            <h3 class="fw-bold">{{ number_format($assetSummary['total_assets']) }}</h3>
                        </div>
                        <div class="col-4">
                            <h6 class="text-muted">Total Value</h6>
                            <h3 class="fw-bold">TZS {{ number_format($assetSummary['total_value'], 0) }}</h3>
                        </div>
                        <div class="col-4">
                            <h6 class="text-muted">Depreciation</h6>
                            <h3 class="fw-bold">TZS {{ number_format($assetSummary['total_depreciation'], 0) }}</h3>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assetSummary['by_category'] as $category)
                                <tr>
                                    <td>{{ $category['name'] }}</td>
                                    <td class="text-end">{{ number_format($category['count']) }}</td>
                                    <td class="text-end">TZS {{ number_format($category['value'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== PROCUREMENT ==================== -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="section-title"><i class="fas fa-shopping-cart me-2 text-secondary"></i>Procurement Overview</h5>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-info bg-opacity-10 text-info">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Pending Requisitions</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($procurementSummary['pending_requisitions']) }}</h4>
                            <small class="text-muted">Value: TZS {{ number_format($procurementSummary['requisition_value'], 0) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-gavel"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Active Tenders</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($procurementSummary['active_tenders']) }}</h4>
                            <small class="text-muted">Closing soon: {{ $procurementSummary['tenders_closing_soon'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-success bg-opacity-10 text-success">
                                <i class="fas fa-file-signature"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Active Contracts</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($procurementSummary['active_contracts']) }}</h4>
                            <small class="text-muted">Value: TZS {{ number_format($procurementSummary['contract_value'], 0) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-sm bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Expiring Contracts</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($procurementSummary['expiring_contracts']) }}</h4>
                            <small class="text-muted">Next 30 days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== RECENT ACTIVITIES ==================== -->
    <div class="row g-4">
        <!-- Recent Invoices -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Invoices</h5>
                    <a href="{{ route('finance.invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('finance.invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a></td>
                                    <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                                    <td>
                                        @if($invoice->invoice_type == 'repeat_module')
                                            <span class="badge bg-primary">Repeat</span>
                                        @elseif($invoice->invoice_type == 'supplementary')
                                            <span class="badge bg-warning">Supplementary</span>
                                        @elseif($invoice->invoice_type == 'hostel')
                                            <span class="badge bg-info">Hostel</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $invoice->invoice_type }}</span>
                                        @endif
                                    </td>
                                    <td>TZS {{ number_format($invoice->total_amount, 0) }}</td>
                                    <td>
                                        @if($invoice->payment_status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->payment_status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @elseif($invoice->due_date->isPast() && $invoice->balance > 0)
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-3">No recent invoices</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Payments</h5>
                    <a href="{{ route('finance.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Student</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                <tr>
                                    <td><a href="{{ route('finance.payments.show', $payment->id) }}">{{ $payment->payment_number }}</a></td>
                                    <td>{{ $payment->student->user->first_name ?? '' }} {{ $payment->student->user->last_name ?? '' }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>TZS {{ number_format($payment->amount, 0) }}</td>
                                    <td>
                                        @if($payment->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($payment->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-3">No recent payments</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Dashboard Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('finance.export-report') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select name="type" class="form-select">
                            <option value="executive">Executive Summary</option>
                            <option value="financial">Financial Report</option>
                            <option value="receivables">Receivables Report</option>
                            <option value="payables">Payables Report</option>
                            <option value="budget">Budget Report</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="current">Current Period</option>
                            <option value="ytd">Year to Date</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <input type="hidden" name="academic_year" value="{{ $selectedYearId }}">
                    <button type="submit" class="btn btn-success w-100">Download Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Refresh Dashboard
    $('#refreshDashboard').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        location.reload();
    });

    // Monthly Collections Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyData['labels']) !!},
            datasets: [
                {
                    label: 'Collections',
                    data: {!! json_encode($monthlyData['collections']) !!},
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Target',
                    data: {!! json_encode($monthlyData['targets']) !!},
                    borderColor: '#e74c3c',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'TZS ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Fee Distribution Chart
    const distributionCtx = document.getElementById('feeDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($feeDistribution)) !!},
            datasets: [{
                data: {!! json_encode(array_values($feeDistribution)) !!},
                backgroundColor: ['#27ae60', '#f39c12', '#3498db', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Aging Chart
    const agingCtx = document.getElementById('agingChart').getContext('2d');
    new Chart(agingCtx, {
        type: 'bar',
        data: {
            labels: ['0-30 Days', '31-60 Days', '61-90 Days', '90+ Days'],
            datasets: [{
                data: [
                    {{ $outstandingSummary['0_30_days'] ?? 0 }},
                    {{ $outstandingSummary['31_60_days'] ?? 0 }},
                    {{ $outstandingSummary['61_90_days'] ?? 0 }},
                    {{ $outstandingSummary['90_plus_days'] ?? 0 }}
                ],
                backgroundColor: ['#27ae60', '#f39c12', '#e74c3c', '#c0392b'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'TZS ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Cash Flow Chart
    const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
    new Chart(cashFlowCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($cashFlowChart['labels'] ?? ['Jan', 'Feb', 'Mar']) !!},
            datasets: [{
                label: 'Cash Balance',
                data: {!! json_encode($cashFlowChart['balances'] ?? [0, 0, 0]) !!},
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Budget Chart
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($budgetChart['labels'] ?? []) !!},
            datasets: [
                {
                    label: 'Budget',
                    data: {!! json_encode($budgetChart['budget'] ?? []) !!},
                    backgroundColor: '#3498db'
                },
                {
                    label: 'Actual',
                    data: {!! json_encode($budgetChart['actual'] ?? []) !!},
                    backgroundColor: '#27ae60'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'TZS ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0;
}

.stat-card {
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.stat-icon-sm {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #343a40;
    line-height: 1.2;
}

.border-primary { border-color: #27ae60 !important; }
.border-success { border-color: #2ecc71 !important; }
.border-warning { border-color: #f39c12 !important; }
.border-danger { border-color: #e74c3c !important; }
.border-info { border-color: #3498db !important; }

.bg-primary { background-color: #27ae60 !important; }
.bg-success { background-color: #2ecc71 !important; }
.bg-warning { background-color: #f39c12 !important; }
.bg-danger { background-color: #e74c3c !important; }
.bg-info { background-color: #3498db !important; }

.text-primary { color: #27ae60 !important; }
.text-success { color: #2ecc71 !important; }
.text-warning { color: #f39c12 !important; }
.text-danger { color: #e74c3c !important; }
.text-info { color: #3498db !important; }
</style>
@endpush
@endsection