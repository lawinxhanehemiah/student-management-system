@extends('layouts.financecontroller')

@section('title', 'Transfer Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Transfer Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.transfers.index') }}">Transfers</a></li>
                <li class="breadcrumb-item active">Transfer #{{ $transfer->id }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($transfer->status == 'pending')
            <form action="{{ route('finance.asset.transfers.approve', $transfer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this transfer?')">
                    <i class="fas fa-check me-2"></i>Approve
                </button>
            </form>
            <form action="{{ route('finance.asset.transfers.reject', $transfer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this transfer?')">
                    <i class="fas fa-times me-2"></i>Reject
                </button>
            </form>
        @endif
        <a href="{{ route('finance.asset.transfers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Transfer Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Asset:</span>
                        <span class="value">
                            <a href="{{ route('finance.asset.assets.show', $transfer->asset->id) }}">
                                {{ $transfer->asset->asset_tag }} - {{ $transfer->asset->name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Transfer Date:</span>
                        <span class="value">{{ $transfer->transfer_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">
                            @if($transfer->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($transfer->status == 'approved')
                                <span class="badge bg-info">Approved</span>
                            @elseif($transfer->status == 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($transfer->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Reason:</span>
                        <span class="value">{{ $transfer->reason ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>From / To</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>From</h6>
                        <p>
                            @if($transfer->fromDepartment)
                                <strong>Department:</strong> {{ $transfer->fromDepartment->name }}<br>
                            @endif
                            @if($transfer->fromUser)
                                <strong>User:</strong> {{ $transfer->fromUser->first_name }} {{ $transfer->fromUser->last_name }}<br>
                            @endif
                            @if($transfer->from_location)
                                <strong>Location:</strong> {{ $transfer->from_location }}<br>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>To</h6>
                        <p>
                            @if($transfer->toDepartment)
                                <strong>Department:</strong> {{ $transfer->toDepartment->name }}<br>
                            @endif
                            @if($transfer->toUser)
                                <strong>User:</strong> {{ $transfer->toUser->first_name }} {{ $transfer->toUser->last_name }}<br>
                            @endif
                            @if($transfer->to_location)
                                <strong>Location:</strong> {{ $transfer->to_location }}<br>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Approval & Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Requested By:</span>
                        <span class="value">{{ $transfer->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Requested At:</span>
                        <span class="value">{{ $transfer->created_at->format('d M Y H:i:s') }}</span>
                    </div>
                    @if($transfer->approved_by)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Approved By:</span>
                            <span class="value">{{ $transfer->approver->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Approved At:</span>
                            <span class="value">{{ $transfer->approved_at->format('d M Y H:i:s') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection