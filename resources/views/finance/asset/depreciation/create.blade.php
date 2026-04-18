@extends('layouts.financecontroller')

@section('title', 'Calculate Depreciation')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Calculate Depreciation</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.depreciation.index') }}">Depreciation</a></li>
                <li class="breadcrumb-item active">Calculate</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.asset.depreciation.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h5 class="section-title">Depreciation Details</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_id" class="form-label required">Select Asset</label>
                                <select class="form-select @error('asset_id') is-invalid @enderror" 
                                        id="asset_id" name="asset_id" required>
                                    <option value="">Choose Asset...</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->asset_tag }} - {{ $asset->name }} 
                                            (Current: {{ number_format($asset->current_value, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="depreciation_date" class="form-label required">Depreciation Date</label>
                                <input type="date" class="form-control @error('depreciation_date') is-invalid @enderror" 
                                       id="depreciation_date" name="depreciation_date" 
                                       value="{{ old('depreciation_date', date('Y-m-d')) }}" required>
                                @error('depreciation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="method" class="form-label required">Depreciation Method</label>
                                <select class="form-select @error('method') is-invalid @enderror" 
                                        id="method" name="method" required>
                                    <option value="straight_line" {{ old('method', 'straight_line') == 'straight_line' ? 'selected' : '' }}>
                                        Straight Line
                                    </option>
                                    <option value="declining_balance" {{ old('method') == 'declining_balance' ? 'selected' : '' }}>
                                        Declining Balance (200%)
                                    </option>
                                </select>
                                @error('method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Depreciation Info
                                </h6>
                                <ul class="small text-muted">
                                    <li>Straight Line: Equal amount each period</li>
                                    <li>Declining Balance: Higher early years</li>
                                    <li>Asset cannot go below salvage value</li>
                                    <li>Disposed assets cannot be depreciated</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calculator me-2"></i>Calculate Depreciation
                </button>
                <a href="{{ route('finance.asset.depreciation.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection