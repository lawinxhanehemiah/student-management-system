@extends('layouts.financecontroller')

@section('title', 'Disposal Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Disposal Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.disposals.index') }}">Disposals</a></li>
                <li class="breadcrumb-item active">Disposal #{{ $disposal->id }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <form action="{{ route('finance.asset.disposals.destroy', $disposal->id) }}" 
              method="POST" class="d-inline" 
              onsubmit="return confirm('Reverse this disposal? Asset will be reactivated.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-undo me-2"></i>Reverse Disposal
            </button>
        </form>
        <a href="{{ route('finance.asset.disposals.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Disposal Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Asset:</span>
                        <span class="value">
                            <a href="{{ route('finance.asset.assets.show', $disposal->asset->id) }}">
                                {{ $disposal->asset->asset_tag }} - {{ $disposal->asset->name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Disposal Date:</span>
                        <span class="value">{{ $disposal->disposal_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Method:</span>
                        <span class="value">{{ $disposal->disposal_method_name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Authorized By:</span>
                        <span class="value">{{ $disposal->authorized_by ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Reason:</span>
                        <span class="value">{{ $disposal->reason ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Financial Impact</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Book Value at Disposal:</span>
                        <span class="value fw-bold">{{ number_format($disposal->book_value_at_disposal, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Disposal Amount:</span>
                        <span class="value">{{ number_format($disposal->disposal_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Gain/Loss:</span>
                        <span class="value fw-bold {{ $disposal->gain_loss >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($disposal->gain_loss, 2) }}
                        </span>
                    </div>
                    <hr>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Original Cost:</span>
                        <span class="value">{{ number_format($disposal->asset->purchase_cost, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Accumulated Depreciation:</span>
                        <span class="value">
                            {{ number_format($disposal->asset->purchase_cost - $disposal->book_value_at_disposal, 2) }}
                        </span>
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
                <h5>Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Recorded By:</span>
                        <span class="value">{{ $disposal->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Recorded At:</span>
                        <span class="value">{{ $disposal->created_at->format('d M Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection