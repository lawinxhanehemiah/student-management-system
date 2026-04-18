@extends('layouts.financecontroller')

@section('title', 'Depreciation Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Depreciation Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.depreciation.index') }}">Depreciation</a></li>
                <li class="breadcrumb-item active">Period {{ $depreciation->period_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.depreciation.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Depreciation Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Asset:</span>
                        <span class="value">
                            <a href="{{ route('finance.asset.assets.show', $depreciation->asset->id) }}">
                                {{ $depreciation->asset->asset_tag }} - {{ $depreciation->asset->name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Category:</span>
                        <span class="value">{{ $depreciation->asset->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Period Number:</span>
                        <span class="value fw-bold">{{ $depreciation->period_number }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Depreciation Date:</span>
                        <span class="value">{{ $depreciation->depreciation_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Method:</span>
                        <span class="value">
                            <span class="badge bg-info">
                                {{ $depreciation->method == 'straight_line' ? 'Straight Line' : 'Declining Balance' }}
                            </span>
                        </span>
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
                        <span class="label">Period Depreciation:</span>
                        <span class="value fw-bold text-danger">{{ number_format($depreciation->period_depreciation, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Accumulated Depreciation:</span>
                        <span class="value">{{ number_format($depreciation->accumulated_depreciation, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Book Value After:</span>
                        <span class="value fw-bold">{{ number_format($depreciation->book_value, 2) }}</span>
                    </div>
                    <hr>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Original Cost:</span>
                        <span class="value">{{ number_format($depreciation->asset->purchase_cost, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Salvage Value:</span>
                        <span class="value">{{ number_format($depreciation->asset->salvage_value, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Useful Life:</span>
                        <span class="value">{{ $depreciation->asset->useful_life_years }} years</span>
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
                        <span class="label">Created By:</span>
                        <span class="value">{{ $depreciation->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $depreciation->created_at->format('d M Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection