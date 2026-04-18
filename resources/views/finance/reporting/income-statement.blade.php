@extends('layouts.financecontroller')

@section('title', 'Income Statement')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Income Statement</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Financial Reporting</a></li>
                <li class="breadcrumb-item active">Income Statement</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.reporting.income-statement.export', request()->query()) }}" class="btn btn-secondary">
            <i class="fas fa-download me-2"></i>Export
        </a>
        <a href="#" onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print me-2"></i>Print
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.reporting.income-statement') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="fiscal_year_id" class="form-label">Fiscal Year</label>
                <select name="fiscal_year_id" id="fiscal_year_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Custom Period</option>
                    @foreach($fiscalYears as $year)
                        <option value="{{ $year->id }}" {{ request('fiscal_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }} ({{ $year->start_date->format('M Y') }} - {{ $year->end_date->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" id="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label for="comparison" class="form-label">Comparison</label>
                <select name="comparison" id="comparison" class="form-select">
                    <option value="none" {{ $comparison == 'none' ? 'selected' : '' }}>None</option>
                    <option value="previous_year" {{ $comparison == 'previous_year' ? 'selected' : '' }}>Previous Year</option>
                    <option value="budget" {{ $comparison == 'budget' ? 'selected' : '' }}>Budget</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Generate Report
                </button>
                <a href="{{ route('finance.reporting.income-statement') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="text-center mb-4">
    <h3>INCOME STATEMENT</h3>
    <p class="text-muted">
        For the period <strong>{{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}</strong> 
        to <strong>{{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</strong>
    </p>
    @if(request('fiscal_year_id'))
        @php $fy = $fiscalYears->firstWhere('id', request('fiscal_year_id')); @endphp
        @if($fy)
            <p class="text-muted">Fiscal Year: <strong>{{ $fy->name }}</strong></p>
        @endif
    @endif
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Revenue</h6>
                <h3 class="text-white">{{ number_format($totalRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Expenses</h6>
                <h3 class="text-white">{{ number_format($totalExpense, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card {{ $netIncome >= 0 ? 'bg-success' : 'bg-warning' }} text-white">
            <div class="card-body">
                <h6 class="text-white-50">Net Income</h6>
                <h3 class="text-white">{{ number_format($netIncome, 2) }}</h3>
                <small>{{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Income Statement Table -->
<div class="card">
    <div class="card-header">
        <h5>Income Statement Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50%">Account</th>
                        <th class="text-end">Current Period</th>
                        @if($comparisonData)
                            <th class="text-end">Previous Period</th>
                            <th class="text-end">Variance</th>
                            <th class="text-end">Variance %</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <!-- REVENUE SECTION -->
                    <tr class="table-primary">
                        <th colspan="5">REVENUE</th>
                    </tr>
                    
                    @forelse($revenues as $revenue)
                        <tr>
                            <td>
                                <strong>{{ $revenue->account_code }}</strong><br>
                                <small>{{ $revenue->account_name }}</small>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($revenue->amount, 2) }}</td>
                            @if($comparisonData)
                                @php
                                    $prevAmount = $comparisonData['revenues']->firstWhere('id', $revenue->id)?->amount ?? 0;
                                    $variance = $revenue->amount - $prevAmount;
                                    $variancePercent = $prevAmount != 0 ? round(($variance / $prevAmount) * 100, 2) : 0;
                                @endphp
                                <td class="text-end">{{ number_format($prevAmount, 2) }}</td>
                                <td class="text-end {{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $variance >= 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                </td>
                                <td class="text-end {{ $variancePercent >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $variancePercent >= 0 ? '+' : '' }}{{ $variancePercent }}%
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No revenue transactions for this period</td>
                        </tr>
                    @endforelse
                    
                    <tr class="fw-bold bg-light">
                        <td>TOTAL REVENUE</td>
                        <td class="text-end">{{ number_format($totalRevenue, 2) }}</td>
                        @if($comparisonData)
                            <td class="text-end">{{ number_format($comparisonData['total_revenue'], 2) }}</td>
                            @php
                                $revVariance = $totalRevenue - $comparisonData['total_revenue'];
                                $revVariancePercent = $comparisonData['total_revenue'] != 0 
                                    ? round(($revVariance / $comparisonData['total_revenue']) * 100, 2) 
                                    : 0;
                            @endphp
                            <td class="text-end {{ $revVariance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $revVariance >= 0 ? '+' : '' }}{{ number_format($revVariance, 2) }}
                            </td>
                            <td class="text-end {{ $revVariancePercent >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $revVariancePercent >= 0 ? '+' : '' }}{{ $revVariancePercent }}%
                            </td>
                        @endif
                    </tr>

                    <!-- EXPENSES SECTION -->
                    <tr class="table-danger">
                        <th colspan="5">EXPENSES</th>
                    </tr>
                    
                    @forelse($expenses as $expense)
                        <tr>
                            <td>
                                <strong>{{ $expense->account_code }}</strong><br>
                                <small>{{ $expense->account_name }}</small>
                            </td>
                            <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                            @if($comparisonData)
                                @php
                                    $prevAmount = $comparisonData['expenses']->firstWhere('id', $expense->id)?->amount ?? 0;
                                    $variance = $expense->amount - $prevAmount;
                                    $variancePercent = $prevAmount != 0 ? round(($variance / $prevAmount) * 100, 2) : 0;
                                @endphp
                                <td class="text-end">{{ number_format($prevAmount, 2) }}</td>
                                <td class="text-end {{ $variance <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $variance >= 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                </td>
                                <td class="text-end {{ $variancePercent <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $variancePercent >= 0 ? '+' : '' }}{{ $variancePercent }}%
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No expense transactions for this period</td>
                        </tr>
                    @endforelse
                    
                    <tr class="fw-bold bg-light">
                        <td>TOTAL EXPENSES</td>
                        <td class="text-end">{{ number_format($totalExpense, 2) }}</td>
                        @if($comparisonData)
                            <td class="text-end">{{ number_format($comparisonData['total_expense'], 2) }}</td>
                            @php
                                $expVariance = $totalExpense - $comparisonData['total_expense'];
                                $expVariancePercent = $comparisonData['total_expense'] != 0 
                                    ? round(($expVariance / $comparisonData['total_expense']) * 100, 2) 
                                    : 0;
                            @endphp
                            <td class="text-end {{ $expVariance <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $expVariance >= 0 ? '+' : '' }}{{ number_format($expVariance, 2) }}
                            </td>
                            <td class="text-end {{ $expVariancePercent <= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $expVariancePercent >= 0 ? '+' : '' }}{{ $expVariancePercent }}%
                            </td>
                        @endif
                    </tr>

                    <!-- NET INCOME -->
                    <tr class="table-success fw-bold" style="font-size: 1.1rem;">
                        <td>NET INCOME</td>
                        <td class="text-end">{{ number_format($netIncome, 2) }}</td>
                        @if($comparisonData)
                            <td class="text-end">{{ number_format($comparisonData['net_income'], 2) }}</td>
                            @php
                                $netVariance = $netIncome - $comparisonData['net_income'];
                                $netVariancePercent = $comparisonData['net_income'] != 0 
                                    ? round(($netVariance / abs($comparisonData['net_income'])) * 100, 2) 
                                    : 0;
                            @endphp
                            <td class="text-end {{ $netVariance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $netVariance >= 0 ? '+' : '' }}{{ number_format($netVariance, 2) }}
                            </td>
                            <td class="text-end {{ $netVariancePercent >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $netVariancePercent >= 0 ? '+' : '' }}{{ $netVariancePercent }}%
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Revenue vs Expenses Trend</h5>
    </div>
    <div class="card-body">
        <canvas id="incomeChart" height="100"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('incomeChart').getContext('2d');
    
    // Prepare data for chart
    const revenueData = {!! json_encode($revenues->pluck('amount')->values()) !!};
    const expenseData = {!! json_encode($expenses->pluck('amount')->values()) !!};
    const labels = {!! json_encode($revenues->pluck('account_name')->values()) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: expenseData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
@endpush