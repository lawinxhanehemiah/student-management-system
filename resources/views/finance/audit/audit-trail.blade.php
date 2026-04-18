@extends('layouts.financecontroller')

@section('title', 'Audit Trail')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Audit Trail</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Audit & Compliance</a></li>
                <li class="breadcrumb-item active">Audit Trail</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.audit.export', request()->query()) }}" class="btn btn-secondary">
            <i class="fas fa-download me-2"></i>Export
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Logs</h6>
                <h3 class="text-white">{{ number_format($stats['total_logs']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Today</h6>
                <h3 class="text-white">{{ number_format($stats['today']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">This Week</h6>
                <h3 class="text-white">{{ number_format($stats['this_week']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">This Month</h6>
                <h3 class="text-white">{{ number_format($stats['this_month']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.audit.audit-trail') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name ?? $user->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Action</label>
                <select name="action" id="action" class="form-select">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="model_type" class="form-label">Model</label>
                <select name="model_type" id="model_type" class="form-select">
                    <option value="">All Models</option>
                    @foreach($models as $model)
                        @php $className = class_basename($model); @endphp
                        <option value="{{ $model }}" {{ request('model_type') == $model ? 'selected' : '' }}>
                            {{ $className }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Identifier</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $log->user_name }}</strong><br>
                                <small class="text-muted">{{ $log->user_role }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $log->action == 'created' ? 'success' : 
                                    ($log->action == 'updated' ? 'info' : 
                                    ($log->action == 'deleted' ? 'danger' : 
                                    ($log->action == 'viewed' ? 'secondary' : 'primary'))) 
                                }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>{{ class_basename($log->model_type) }}</td>
                            <td>{{ $log->model_identifier ?? 'N/A' }}</td>
                            <td>{{ Str::limit($log->description, 50) }}</td>
                            <td>{{ $log->ip_address ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('finance.audit.show-audit', $log->id) }}" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No audit logs found</p>
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

<!-- Top Actions Chart -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Actions</h5>
            </div>
            <div class="card-body">
                <canvas id="actionsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('actionsChart').getContext('2d');
    const actions = {!! json_encode($stats['by_action']->pluck('action')->values()) !!};
    const counts = {!! json_encode($stats['by_action']->pluck('total')->values()) !!};
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: actions,
            datasets: [{
                label: 'Number of Actions',
                data: counts,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush