@extends('layouts.financecontroller')

@section('title', 'Role Activity Logs')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Role Activity Logs</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Audit & Compliance</a></li>
                <li class="breadcrumb-item active">Role Activity</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Role Summary Cards -->
<div class="row mb-4">
    @foreach($roleSummary as $role)
        <div class="col-md-3">
            <div class="card bg-{{ $loop->index % 4 == 0 ? 'primary' : ($loop->index % 4 == 1 ? 'success' : ($loop->index % 4 == 2 ? 'info' : 'warning')) }} text-white">
                <div class="card-body">
                    <h6 class="text-white-50">{{ $role->user_role }}</h6>
                    <h3 class="text-white">{{ number_format($role->total) }}</h3>
                    <small>activities</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.audit.role-activity') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($roleSummary as $role)
                        <option value="{{ $role->user_role }}" {{ request('role') == $role->user_role ? 'selected' : '' }}>
                            {{ $role->user_role }}
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
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Activity by Role -->
<div class="row">
    @foreach($activityByRole as $roleName => $data)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ $roleName }} ({{ number_format($data['total']) }} activities)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['actions'] as $action)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $action->action == 'created' ? 'success' : 
                                                ($action->action == 'updated' ? 'info' : 
                                                ($action->action == 'deleted' ? 'danger' : 'primary')) 
                                            }}">
                                                {{ ucfirst($action->action) }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($action->count) }}</td>
                                        <td class="text-end">
                                            {{ round(($action->count / $data['total']) * 100, 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Activity Logs Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Recent Activity</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Identifier</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td><strong>{{ $log->user_name }}</strong></td>
                            <td>{{ $log->user_role }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $log->action == 'created' ? 'success' : 
                                    ($log->action == 'updated' ? 'info' : 
                                    ($log->action == 'deleted' ? 'danger' : 'primary')) 
                                }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td>{{ class_basename($log->model_type) }}</td>
                            <td>{{ $log->model_identifier ?? 'N/A' }}</td>
                            <td>{{ Str::limit($log->description, 50) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No activity logs found</p>
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
@endsection