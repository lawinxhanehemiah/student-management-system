@extends('layouts.financecontroller')

@section('title', 'Pending Transfers')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Pending Transfers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.transfers.index') }}">Transfers</a></li>
                <li class="breadcrumb-item active">Pending</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.transfers.create') }}" class="btn btn-info">
            <i class="fas fa-exchange-alt me-2"></i>New Transfer
        </a>
    </div>
</div>

<!-- Pending Transfers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request Date</th>
                        <th>Asset</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
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
                            <td>{{ Str::limit($transfer->reason, 30) }}</td>
                            <td>
                                <a href="{{ route('finance.asset.transfers.show', $transfer->id) }}" 
                                   class="btn btn-sm btn-info mb-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form action="{{ route('finance.asset.transfers.approve', $transfer->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success mb-1" 
                                            onclick="return confirm('Approve this transfer?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('finance.asset.transfers.reject', $transfer->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger mb-1" 
                                            onclick="return confirm('Reject this transfer?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted">No pending transfers</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection