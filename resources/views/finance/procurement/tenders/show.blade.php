
@extends('layouts.financecontroller')

@section('title', 'Tender Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Tender Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.tenders.index') }}">Tenders</a></li>
                <li class="breadcrumb-item active">{{ $tender->tender_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($tender->status == 'draft')
            <form action="{{ route('finance.procurement.tenders.publish', $tender->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Publish this tender?')">
                    <i class="fas fa-check-circle me-2"></i>Publish
                </button>
            </form>
        @endif
        @if($tender->status == 'published')
            <form action="{{ route('finance.procurement.tenders.close', $tender->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Close bidding?')">
                    <i class="fas fa-lock me-2"></i>Close Bidding
                </button>
            </form>
        @endif
        <a href="{{ route('finance.procurement.tenders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Tender Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Tender Number</th>
                        <td><strong>{{ $tender->tender_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>Title</th>
                        <td>{{ $tender->title }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{{ ucfirst($tender->type) }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'published' => 'success',
                                    'evaluating' => 'warning',
                                    'awarded' => 'info',
                                    'cancelled' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$tender->status] }}">
                                {{ ucfirst($tender->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Published Date</th>
                        <td>{{ $tender->published_date ? $tender->published_date->format('d/m/Y') : 'Not published' }}</td>
                    </tr>
                    <tr>
                        <th>Closing Date</th>
                        <td>{{ $tender->closing_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Estimated Value</th>
                        <td>{{ number_format($tender->estimated_value, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Description</h5>
            </div>
            <div class="card-body">
                <p>{{ $tender->description }}</p>
                @if($tender->terms_and_conditions)
                    <hr>
                    <h6>Terms and Conditions</h6>
                    <p>{{ $tender->terms_and_conditions }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bids Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Bids Received</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bid #</th>
                                <th>Supplier</th>
                                <th>Bid Amount</th>
                                <th>Status</th>
                                <th>Submitted Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tender->bids as $bid)
                                <tr>
                                    <td>{{ $bid->bid_number }}</td>
                                    <td>{{ $bid->supplier->name }}</td>
                                    <td class="text-end">{{ number_format($bid->bid_amount, 2) }}</td>
                                    <td>
                                        @if($bid->status == 'submitted')
                                            <span class="badge bg-info">Submitted</span>
                                        @elseif($bid->status == 'shortlisted')
                                            <span class="badge bg-success">Shortlisted</span>
                                        @elseif($bid->status == 'accepted')
                                            <span class="badge bg-primary">Accepted</span>
                                        @elseif($bid->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $bid->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">No bids received yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Trail -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created By:</strong> {{ $tender->creator->name ?? 'N/A' }}</p>
                        <p><strong>Created At:</strong> {{ $tender->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        @if($tender->approved_by)
                            <p><strong>Approved By:</strong> {{ $tender->approver->name ?? 'N/A' }}</p>
                            <p><strong>Approved At:</strong> {{ $tender->approved_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection