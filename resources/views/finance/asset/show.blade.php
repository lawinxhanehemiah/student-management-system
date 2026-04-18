@extends('layouts.financecontroller')

@section('title', 'Asset Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.assets.index') }}">Asset Register</a></li>
                <li class="breadcrumb-item active">{{ $asset->asset_tag }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if(!$asset->isDisposed())
            <a href="{{ route('finance.asset.assets.edit', $asset->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        @endif
        <a href="{{ route('finance.asset.assets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Asset Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Asset Tag:</span>
                        <span class="value fw-bold">{{ $asset->asset_tag }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Name:</span>
                        <span class="value">{{ $asset->name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Serial Number:</span>
                        <span class="value">{{ $asset->serial_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Category:</span>
                        <span class="value">{{ $asset->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Department:</span>
                        <span class="value">{{ $asset->department->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Assigned To:</span>
                        <span class="value">{{ $asset->assignedTo->first_name ?? 'N/A' }} {{ $asset->assignedTo->last_name ?? '' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Location:</span>
                        <span class="value">{{ $asset->location ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Supplier:</span>
                        <span class="value">{{ $asset->supplier->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">
                            <span class="badge bg-{{ 
                                $asset->status == 'active' ? 'success' : 
                                ($asset->status == 'under_maintenance' ? 'warning' : 
                                ($asset->status == 'disposed' ? 'danger' : 
                                ($asset->status == 'transferred' ? 'info' : 'secondary'))) 
                            }}">
                                {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                            </span>
                        </span>
                    </div>
                </div>

                @if($asset->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $asset->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Financial Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Purchase Cost</h6>
                        <h4>{{ number_format($asset->purchase_cost, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Current Value</h6>
                        <h4>{{ number_format($asset->current_value, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>Salvage Value</h6>
                        <h4>{{ number_format($asset->salvage_value, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Depreciation Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Depreciation Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h6>Age</h6>
                        <h3>{{ $asset->getAgeInYears() }} yrs</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Remaining Life</h6>
                        <h3>{{ $asset->getRemainingLifeYears() }} yrs</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Annual Depreciation</h6>
                        <h3>{{ number_format($asset->calculateAnnualDepreciation(), 2) }}</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Depreciation %</h6>
                        <h3>{{ $asset->getDepreciationPercentage() }}%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5>Recent Activity</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="assetTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="depreciation-tab" data-bs-toggle="tab" 
                                data-bs-target="#depreciation" type="button" role="tab">
                            Depreciation History
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="transfers-tab" data-bs-toggle="tab" 
                                data-bs-target="#transfers" type="button" role="tab">
                            Transfers
                        </button>
                    </li>
                    @if($asset->disposals->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="disposal-tab" data-bs-toggle="tab" 
                                data-bs-target="#disposal" type="button" role="tab">
                            Disposal
                        </button>
                    </li>
                    @endif
                </ul>
                <div class="tab-content mt-3" id="assetTabsContent">
                    <div class="tab-pane fade show active" id="depreciation" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Period</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Accumulated</th>
                                        <th class="text-end">Book Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($asset->depreciations as $dep)
                                        <tr>
                                            <td>{{ $dep->depreciation_date->format('d/m/Y') }}</td>
                                            <td>{{ $dep->period_number }}</td>
                                            <td class="text-end">{{ number_format($dep->period_depreciation, 2) }}</td>
                                            <td class="text-end">{{ number_format($dep->accumulated_depreciation, 2) }}</td>
                                            <td class="text-end">{{ number_format($dep->book_value, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No depreciation records</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="transfers" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($asset->transfers as $transfer)
                                        <tr>
                                            <td>{{ $transfer->transfer_date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($transfer->fromDepartment)
                                                    {{ $transfer->fromDepartment->name }}<br>
                                                @endif
                                                @if($transfer->from_location)
                                                    <small>{{ $transfer->from_location }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($transfer->toDepartment)
                                                    {{ $transfer->toDepartment->name }}<br>
                                                @endif
                                                @if($transfer->to_location)
                                                    <small>{{ $transfer->to_location }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $transfer->status == 'completed' ? 'success' : 
                                                    ($transfer->status == 'pending' ? 'warning' : 'secondary') 
                                                }}">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No transfer records</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($asset->disposals->count() > 0)
                    <div class="tab-pane fade" id="disposal" role="tabpanel">
                        @foreach($asset->disposals as $disposal)
                            <div class="alert alert-info">
                                <strong>Disposed on:</strong> {{ $disposal->disposal_date->format('d/m/Y') }}<br>
                                <strong>Method:</strong> {{ $disposal->disposal_method_name }}<br>
                                <strong>Amount:</strong> {{ number_format($disposal->disposal_amount ?? 0, 2) }}<br>
                                <strong>Gain/Loss:</strong> 
                                <span class="{{ $disposal->gain_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($disposal->gain_loss, 2) }}
                                </span><br>
                                @if($disposal->reason)
                                    <strong>Reason:</strong> {{ $disposal->reason }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection