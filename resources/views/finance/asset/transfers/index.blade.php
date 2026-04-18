@extends('layouts.financecontroller')

@section('title', 'Asset Transfers')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Transfers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Asset Management</a></li>
                <li class="breadcrumb-item active">Transfers</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.transfers.create') }}" class="btn btn-info">
            <i class="fas fa-exchange-alt me-2"></i>New Transfer
        </a>
        <a href="{{ route('finance.asset.transfers.pending') }}" class="btn btn-warning">
            <i class="fas fa-clock me-2"></i>Pending Transfers
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.asset.transfers.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="asset_id" class="form-label">Asset</label>
                <select name="asset_id" id="asset_id" class="form-select">
                    <option value="">All Assets</option>
                    @foreach($transfers->pluck('asset')->unique() as $asset)
                        <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->asset_tag }} - {{ $asset->name }}
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
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Transfers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Asset</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->transfer_date->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $transfer->asset->asset_tag }}</strong><br>
                                <small>{{ $transfer->asset->name }}</small>
                            </td>
                            <td>
                                @if($transfer->fromDepartment)
                                    Dept: {{ $transfer->fromDepartment->name }}<br>
                                @endif
                                @if($transfer->fromUser)
                                    User: {{ $transfer->fromUser->first_name }} {{ $transfer->fromUser->last_name }}
                                @endif
                                @if($transfer->from_location)
                                    <br><small>Loc: {{ $transfer->from_location }}</small>
                                @endif
                            </td>
                            <td>
                                @if($transfer->toDepartment)
                                    Dept: {{ $transfer->toDepartment->name }}<br>
                                @endif
                                @if($transfer->toUser)
                                    User: {{ $transfer->toUser->first_name }} {{ $transfer->toUser->last_name }}
                                @endif
                                @if($transfer->to_location)
                                    <br><small>Loc: {{ $transfer->to_location }}</small>
                                @endif
                            </td>
                            <td>
                                @if($transfer->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($transfer->status == 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($transfer->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transfer->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($transfer->reason, 30) }}</td>
                            <td>
                                <a href="{{ route('finance.asset.transfers.show', $transfer->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($transfer->status == 'pending')
                                    <form action="{{ route('finance.asset.transfers.approve', $transfer->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('Approve this transfer?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('finance.asset.transfers.reject', $transfer->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Reject this transfer?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No transfer records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transfers->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection