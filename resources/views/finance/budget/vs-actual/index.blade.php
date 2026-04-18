@extends('layouts.financecontroller')

@section('title', 'Budget vs Actual')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Budget vs Actual</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <span>Budget vs Actual</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-sm btn-success-light" onclick="exportReport()">
                <i class="feather-download"></i> Export
            </button>
            <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Budget</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($budgetYear->total_budget, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-dollar-sign text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Actual Expenditure</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($actualData['total'] ?? 0, 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-trending-up text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Variance</span>
                            <h4 class="mb-0 fw-semibold {{ ($budgetYear->total_utilized - ($actualData['total'] ?? 0)) > 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($budgetYear->total_utilized - ($actualData['total'] ?? 0), 0) }}
                            </h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-bar-chart-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Budget vs Actual Overview</h6>
        </div>
        <div class="card-body">
            <canvas id="budgetChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Department Breakdown -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Department Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Department</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Actual</th>
                            <th class="text-end">Variance</th>
                            <th>Variance %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgetYear->departmentBudgets as $deptBudget)
                        @php
                            $actual = $actualData['departments'][$deptBudget->department_id] ?? 0;
                            $variance = $deptBudget->allocated_amount - $actual;
                            $variancePercent = $deptBudget->allocated_amount > 0 ? ($variance / $deptBudget->allocated_amount) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $deptBudget->department->name }}</td>
                            <td class="text-end">{{ $deptBudget->formatted_allocated }}</td>
                            <td class="text-end">TZS {{ number_format($actual, 0) }}</td>
                            <td class="text-end {{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
                                TZS {{ number_format($variance, 0) }}
                            </td>
                            <td>
                                <span class="{{ $variancePercent >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($variancePercent, 1) }}%
                                </span>
                            </td>
                            <td>
                                @if($variancePercent >= 0)
                                    <span class="badge bg-success">Under Budget</span>
                                @else
                                    <span class="badge bg-danger">Over Budget</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('budgetChart').getContext('2d');
    
    const departments = @json($budgetYear->departmentBudgets->pluck('department.name'));
    const budgetData = @json($budgetYear->departmentBudgets->pluck('allocated_amount'));
    const actualData = @json(collect($actualData['departments'] ?? [])->values());
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: departments,
            datasets: [
                {
                    label: 'Budget',
                    data: budgetData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Actual',
                    data: actualData,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
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

function exportReport() {
    // Implement export functionality
    alert('Export feature coming soon');
}
</script>
@endpush
@endsection