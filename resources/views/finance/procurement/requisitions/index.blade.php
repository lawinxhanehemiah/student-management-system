@extends('layouts.financecontroller')

@section('title', 'Requisition Requests')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Requisition Requests</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                <li class="breadcrumb-item active">Requisitions</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.procurement.requisitions.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>New Requisition
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Requisitions</h6>
                <h3 class="text-white">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Pending Approval</h6>
                <h3 class="text-white">{{ number_format($stats['pending']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Approved</h6>
                <h3 class="text-white">{{ number_format($stats['approved']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Value</h6>
                <h3 class="text-white">{{ number_format($stats['total_value'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.procurement.requisitions.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by number or title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Requisitions Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Requisition #</th>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Requested By</th>
                        <th>Required Date</th>
                        <th>Priority</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requisitions as $req)
                        <tr>
                            <td><strong>{{ $req->requisition_number }}</strong></td>
                            <td>{{ Str::limit($req->title, 30) }}</td>
                            <td>{{ $req->department->name ?? 'N/A' }}</td>
                            <td>{{ $req->requester->name ?? 'N/A' }}</td>
                            <td>{{ $req->required_date->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$req->priority] }}">
                                    {{ ucfirst($req->priority) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($req->estimated_total, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'submitted' => 'info',
                                        'under_review' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$req->status] }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('finance.procurement.requisitions.show', $req->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($req->status, ['draft', 'rejected']))
                                    <a href="{{ route('finance.procurement.requisitions.edit', $req->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($req->status == 'draft')
                                    <form action="{{ route('finance.procurement.requisitions.submit', $req->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('Submit for approval?')">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted">No requisitions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $requisitions->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection