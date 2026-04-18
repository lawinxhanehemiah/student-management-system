@extends('layouts.financecontroller')

@section('title', 'Transaction Logs')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Transaction Logs</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Audit & Compliance</a></li>
                <li class="breadcrumb-item active">Transaction Logs</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Transactions</h6>
                <h3 class="text-white">{{ number_format($stats['total_transactions']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Amount</h6>
                <h3 class="text-white">{{ number_format($stats['total_amount'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Today's Amount</h6>
                <h3 class="text-white">{{ number_format($stats['today_amount'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.audit.transaction-logs') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->first_name }} {{ $user->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="transaction_type" class="form-label">Transaction Type</label>
                <select name="transaction_type" id="transaction_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($transactionTypes as $type)
                        <option value="{{ $type }}" {{ request('transaction_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="reference_type" class="form-label">Reference Type</label>
                <select name="reference_type" id="reference_type" class="form-select">
                    <option value="">All References</option>
                    @foreach($referenceTypes as $type)
                        <option value="{{ $type }}" {{ request('reference_type') == $type ? 'selected' : '' }}>
                            {{ class_basename($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label for="min_amount" class="form-label">Min Amount</label>
                <input type="number" class="form-control" name="min_amount" value="{{ request('min_amount') }}" placeholder="0">
            </div>
            <div class="col-md-3">
                <label for="max_amount" class="form-label">Max Amount</label>
                <input type="number" class="form-control" name="max_amount" value="{{ request('max_amount') }}" placeholder="1000000">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Transaction Logs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Transaction #</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->transaction_date->format('d/m/Y H:i:s') }}</td>
                            <td><strong>{{ $log->transaction_number }}</strong></td>
                            <td>
                                <span class="badge bg-{{ 
                                    $log->transaction_type == 'payment_received' ? 'success' : 
                                    ($log->transaction_type == 'invoice_created' ? 'info' : 
                                    ($log->transaction_type == 'journal_posted' ? 'primary' : 'secondary')) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->transaction_type)) }}
                                </span>
                            </td>
                            <td>{{ class_basename($log->reference_type) }} #{{ $log->reference_id }}</td>
                            <td class="text-end fw-bold">{{ number_format($log->amount, 2) }}</td>
                            <td>{{ $log->user_name }}</td>
                            <td>{{ Str::limit($log->description, 30) }}</td>
                            <td>
                                <a href="{{ route('finance.audit.show-transaction', $log->id) }}" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No transaction logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Transaction Types Chart -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Transactions by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="transactionChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('transactionChart').getContext('2d');
    const types = {!! json_encode($stats['by_type']->pluck('transaction_type')->map(function($type) {
        return ucfirst(str_replace('_', ' ', $type));
    })->values()) !!};
    const counts = {!! json_encode($stats['by_type']->pluck('count')->values()) !!};
    const amounts = {!! json_encode($stats['by_type']->pluck('total')->values()) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: types,
            datasets: [
                {
                    label: 'Number of Transactions',
                    data: counts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Total Amount (TZS)',
                    data: amounts,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Amount (TZS)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>
@endpush