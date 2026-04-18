@extends('layouts.financecontroller')

@section('title', 'Create Asset Category')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Create Asset Category</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.categories.index') }}">Categories</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.asset.categories.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">Category Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label required">Category Code</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Unique identifier, e.g., IT-EQP, FURN</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Depreciation Settings -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Depreciation Settings</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="depreciation_method" class="form-label required">Depreciation Method</label>
                                <select class="form-select @error('depreciation_method') is-invalid @enderror" 
                                        id="depreciation_method" name="depreciation_method" required>
                                    <option value="">Select Method</option>
                                    <option value="straight_line" {{ old('depreciation_method') == 'straight_line' ? 'selected' : '' }}>
                                        Straight Line
                                    </option>
                                    <option value="declining_balance" {{ old('depreciation_method') == 'declining_balance' ? 'selected' : '' }}>
                                        Declining Balance
                                    </option>
                                    <option value="none" {{ old('depreciation_method') == 'none' ? 'selected' : '' }}>
                                        No Depreciation
                                    </option>
                                </select>
                                @error('depreciation_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="default_useful_life_years" class="form-label">Default Useful Life (Years)</label>
                                <input type="number" class="form-control @error('default_useful_life_years') is-invalid @enderror" 
                                       id="default_useful_life_years" name="default_useful_life_years" 
                                       value="{{ old('default_useful_life_years') }}" min="1">
                                @error('default_useful_life_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="default_salvage_value_percentage" class="form-label">Default Salvage Value (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('default_salvage_value_percentage') is-invalid @enderror" 
                                           id="default_salvage_value_percentage" name="default_salvage_value_percentage" 
                                           value="{{ old('default_salvage_value_percentage', 0) }}" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('default_salvage_value_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Category is Active
                                    </label>
                                </div>
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
                                    Category Information
                                </h6>
                                <ul class="small text-muted">
                                    <li>Categories help organize assets</li>
                                    <li>Code must be unique</li>
                                    <li>Depreciation method affects calculations</li>
                                    <li>Default values can be overridden per asset</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Category
                </button>
                <a href="{{ route('finance.asset.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection