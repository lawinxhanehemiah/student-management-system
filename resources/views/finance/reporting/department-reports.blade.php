@extends('layouts.financecontroller')

@section('title', 'Department Reports')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Department Reports</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Financial Reporting</a></li>
                <li class="breadcrumb-item active">Department Reports</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="#" onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print me-2"></i>Print
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.reporting.departments') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">All Departments (Summary)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
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
                <label for="report_type" class="form-label">Report Type</label>
                <select name="report_type" id="report_type" class="form-select">
                    <option value="summary" {{ $reportType == 'summary' ? 'selected' : '' }}>Summary</option>
                    <option value="expenses" {{ $reportType == 'expenses' ? 'selected' : '' }}>Detailed Expenses</option>
                    <option value="budget" {{ $reportType == 'budget' ? 'selected' : '' }}>Budget vs Actual</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Generate Report
                </button>
                <a href="{{ route('finance.reporting.departments') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="text-center mb-4">
    <h3>DEPARTMENT {{ $reportType == 'summary' ? 'SUMMARY' : ($reportType == 'expenses' ? 'EXPENSES' : 'BUDGET VS ACTUAL') }}</h3>
    <p class="text-muted">
        Period: <strong>{{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}</strong> 
        to <strong>{{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</strong>
    </p>
    @if($departmentId && isset($departmentData['department']))
        <p class="text-muted">Department: <strong>{{ $departmentData['department']->name }}</strong></p>
    @endif
</div>

@if($departmentId && isset($departmentData['department']))
    <!-- SINGLE DEPARTMENT REPORT -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Expenses</h6>
                    <h3 class="text-white">{{ number_format($departmentData['total_expense'], 2) }}</h3>
                </div>
            </div>
        </div>
        @if($departmentData['budget'])
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Budget Amount</h6>
                        <h3 class="text-white">{{ number_format($departmentData['budget'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card {{ $departmentData['variance'] <= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Budget Variance</h6>
                        <h3 class="text-white">{{ number_format($departmentData['variance'], 2) }}</h3>
                        <small>{{ $departmentData['variance_percentage'] }}%</small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Expense Details - {{ $departmentData['department']->name }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th class="text-end">Amount</th>
                            @if($reportType == 'budget' && $departmentData['budget'])
                                <th class="text-end">Budget</th>
                                <th class="text-end">Variance</th>
                                <th class="text-end">%</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departmentData['expenses'] as $expense)
                            <tr>
                                <td>{{ $expense->account_code }}</td>
                                <td>{{ $expense->account_name }}</td>
                                <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                                @if($reportType == 'budget' && $departmentData['budget'])
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end">-</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3">No expenses found for this department</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <th class="text-end">{{ number_format($departmentData['total_expense'], 2) }}</th>
                            @if($reportType == 'budget' && $departmentData['budget'])
                                <th class="text-end">{{ number_format($departmentData['budget'], 2) }}</th>
                                <th class="text-end {{ $departmentData['variance'] <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($departmentData['variance'], 2) }}
                                </th>
                                <th class="text-end {{ $departmentData['variance_percentage'] <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $departmentData['variance_percentage'] }}%
                                </th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@else
    <!-- ALL DEPARTMENTS SUMMARY -->
    <div class="card">
        <div class="card-header">
            <h5>Department Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th class="text-end">Total Expenses</th>
                            <th class="text-end">Transaction Count</th>
                            <th>% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = collect($summary)->sum('total_expense'); @endphp
                        @forelse($summary as $item)
                            <tr>
                                <td><strong>{{ $item['department']->name }}</strong></td>
                                <td class="text-end fw-bold">{{ number_format($item['total_expense'], 2) }}</td>
                                <td class="text-end">{{ $item['expense_count'] }}</td>
                                <td>
                                    @if($grandTotal > 0)
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $loop->index % 2 == 0 ? 'primary' : 'info' }}" 
                                                 style="width: {{ ($item['total_expense'] / $grandTotal) * 100 }}%">
                                                {{ round(($item['total_expense'] / $grandTotal) * 100, 1) }}%
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3">No department data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-end">{{ number_format($grandTotal, 2) }}</th>
                            <th class="text-end">{{ collect($summary)->sum('expense_count') }}</th>
                            <th>100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Department Chart -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Expenses by Department</h5>
        </div>
        <div class="card-body">
            <canvas id="departmentChart" height="100"></canvas>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(!$departmentId)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('departmentChart').getContext('2d');
    const deptNames = {!! json_encode(collect($summary)->pluck('department.name')->values()) !!};
    const deptExpenses = {!! json_encode(collect($summary)->pluck('total_expense')->values()) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: deptNames,
            datasets: [{
                label: 'Expenses',
                data: deptExpenses,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
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
@endif
@endpush