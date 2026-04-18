@extends('layouts.financecontroller')

@section('title', 'Cash Flow Monitoring')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Cash Flow Monitoring</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Bank & Cash</a></li>
                <li class="breadcrumb-item active">Cash Flow</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Bank Balance</h6>
                        <h3 class="text-white">{{ number_format($totalBalance, 2) }}</h3>
                    </div>
                    <i class="fas fa-university fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Expected Inflows</h6>
                        <h3 class="text-white">{{ number_format($projections['expected_inflows'], 2) }}</h3>
                        <small>Pending Payments</small>
                    </div>
                    <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Expected Outflows</h6>
                        <h3 class="text-white">{{ number_format($projections['expected_outflows'], 2) }}</h3>
                        <small>Pending Payments</small>
                    </div>
                    <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Buffer Days</h6>
                        <h3 class="text-white">{{ $projections['buffer_days'] }}</h3>
                        <small>Days of expenses covered</small>
                    </div>
                    <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.bank.cashflow.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="period" class="form-label">Period</label>
                <select name="period" id="period" class="form-select">
                    <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Daily</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Weekly</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Quarterly</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y')-3; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3" id="monthField" style="{{ $period == 'month' ? '' : 'display: none;' }}">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3" id="quarterField" style="{{ $period == 'quarter' ? '' : 'display: none;' }}">
                <label for="quarter" class="form-label">Quarter</label>
                <select name="quarter" id="quarter" class="form-select">
                    <option value="1" {{ request('quarter') == 1 ? 'selected' : '' }}>Q1 (Jan-Mar)</option>
                    <option value="2" {{ request('quarter') == 2 ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                    <option value="3" {{ request('quarter') == 3 ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                    <option value="4" {{ request('quarter') == 4 ? 'selected' : '' }}>Q4 (Oct-Dec)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Update View
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Toggle -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="{{ route('finance.bank.cashflow.index', array_merge(request()->query(), ['view' => 'chart'])) }}" 
           class="btn btn-outline-primary {{ $viewType == 'chart' ? 'active' : '' }}">
            <i class="fas fa-chart-line me-2"></i>Chart View
        </a>
        <a href="{{ route('finance.bank.cashflow.index', array_merge(request()->query(), ['view' => 'table'])) }}" 
           class="btn btn-outline-primary {{ $viewType == 'table' ? 'active' : '' }}">
            <i class="fas fa-table me-2"></i>Table View
        </a>
    </div>
</div>

@if($viewType == 'chart')
    <!-- Chart View -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Cash Flow Chart</h5>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>Cash Flow Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Inflows</th>
                                <th class="text-end">Outflows</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cashFlow as $item)
                                <tr>
                                    <td>{{ $item['label'] }}</td>
                                    <td class="text-end text-success">{{ number_format($item['inflows'], 0) }}</td>
                                    <td class="text-end text-danger">{{ number_format($item['outflows'], 0) }}</td>
                                    <td class="text-end {{ $item['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $item['net'] >= 0 ? '+' : '' }}{{ number_format($item['net'], 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Table View -->
    <div class="card">
        <div class="card-header">
            <h5>Cash Flow Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th class="text-end">Inflows (TZS)</th>
                            <th class="text-end">Outflows (TZS)</th>
                            <th class="text-end">Net Cash Flow</th>
                            @if($period == 'month')
                                <th class="text-end">Payment Inflows</th>
                                <th class="text-end">Other Inflows</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashFlow as $item)
                            <tr>
                                <td><strong>{{ $item['label'] }}</strong></td>
                                <td class="text-end text-success">{{ number_format($item['inflows'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($item['outflows'], 2) }}</td>
                                <td class="text-end {{ $item['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $item['net'] >= 0 ? '+' : '' }}{{ number_format($item['net'], 2) }}
                                </td>
                                @if($period == 'month')
                                    <td class="text-end">{{ number_format($item['payment_inflows'] ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($item['other_inflows'] ?? 0, 2) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        @php
                            $totalInflow = collect($cashFlow)->sum('inflows');
                            $totalOutflow = collect($cashFlow)->sum('outflows');
                            $totalNet = $totalInflow - $totalOutflow;
                        @endphp
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-end text-success">{{ number_format($totalInflow, 2) }}</th>
                            <th class="text-end text-danger">{{ number_format($totalOutflow, 2) }}</th>
                            <th class="text-end {{ $totalNet >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $totalNet >= 0 ? '+' : '' }}{{ number_format($totalNet, 2) }}
                            </th>
                            @if($period == 'month')
                                <th></th>
                                <th></th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Bank Balances -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Bank Account Balances</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Bank</th>
                                <th>Account</th>
                                <th>Currency</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankBalances as $bank)
                                <tr>
                                    <td>{{ $bank->bank_name }}</td>
                                    <td>{{ $bank->account_name }}<br><small>{{ $bank->account_number }}</small></td>
                                    <td>{{ $bank->currency }}</td>
                                    <td class="text-end fw-bold">{{ number_format($bank->current_balance, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Upcoming Payments -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Upcoming Payments (Next 30 Days)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>Student</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingPayments as $invoice)
                                <tr>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                                    <td class="text-end">{{ number_format($invoice->balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No upcoming payments</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5>Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                                <tr>
                                    <td>{{ $txn->transaction_date->format('d/m') }}</td>
                                    <td><small>{{ $txn->bankAccount->bank_name }}</small></td>
                                    <td>{{ Str::limit($txn->description, 25) }}</td>
                                    <td class="text-end {{ $txn->transaction_type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $txn->transaction_type == 'deposit' ? '+' : '-' }}{{ number_format($txn->amount, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent transactions</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Toggle month/quarter fields based on period
    $('#period').change(function() {
        const period = $(this).val();
        $('#monthField, #quarterField').hide();
        
        if (period === 'month') {
            $('#monthField').show();
        } else if (period === 'quarter') {
            $('#quarterField').show();
        }
    });

    // Cash Flow Chart
    @if($viewType == 'chart')
        const ctx = document.getElementById('cashFlowChart').getContext('2d');
        const cashFlowChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($cashFlow)->pluck('label')->values()) !!},
                datasets: [
                    {
                        label: 'Inflows',
                        data: {!! json_encode(collect($cashFlow)->pluck('inflows')->values()) !!},
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Outflows',
                        data: {!! json_encode(collect($cashFlow)->pluck('outflows')->values()) !!},
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
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'TZS ' + context.raw.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            }
        });
    @endif
});
</script>
@endpush