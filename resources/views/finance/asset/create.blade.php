@extends('layouts.financecontroller')

@section('title', 'Add New Asset')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add New Asset</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.assets.index') }}">Asset Register</a></li>
                <li class="breadcrumb-item active">Add Asset</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.asset.assets.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_tag" class="form-label required">Asset Tag</label>
                                <input type="text" class="form-control @error('asset_tag') is-invalid @enderror" 
                                       id="asset_tag" name="asset_tag" value="{{ old('asset_tag', $assetTag) }}" required>
                                @error('asset_tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Unique identifier for the asset</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">Asset Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control @error('serial_number') is-invalid @enderror" 
                                       id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
                                @error('serial_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label required">Category</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Assignment -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Assignment</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Assigned To</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" name="assigned_to">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="e.g., Building A, Room 101">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Financial Information</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="purchase_date" class="form-label required">Purchase Date</label>
                                <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="purchase_cost" class="form-label required">Purchase Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('purchase_cost') is-invalid @enderror" 
                                           id="purchase_cost" name="purchase_cost" value="{{ old('purchase_cost') }}" required>
                                </div>
                                @error('purchase_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="salvage_value" class="form-label">Salvage Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('salvage_value') is-invalid @enderror" 
                                           id="salvage_value" name="salvage_value" value="{{ old('salvage_value', 0) }}">
                                </div>
                                @error('salvage_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="useful_life_years" class="form-label required">Useful Life (Years)</label>
                                <input type="number" class="form-control @error('useful_life_years') is-invalid @enderror" 
                                       id="useful_life_years" name="useful_life_years" value="{{ old('useful_life_years', 5) }}" 
                                       min="1" required>
                                @error('useful_life_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                       id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry') }}">
                                @error('warranty_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Additional Information</h5>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Asset Information
                                </h6>
                                <ul class="small text-muted">
                                    <li>Asset tag must be unique</li>
                                    <li>Serial number should be unique if provided</li>
                                    <li>Purchase cost is required for depreciation</li>
                                    <li>Salvage value is the estimated value at end of life</li>
                                    <li>Useful life determines depreciation period</li>
                                </ul>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-calculator me-2"></i>
                                    Current value will initially equal purchase cost
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Asset
                </button>
                <a href="{{ route('finance.asset.assets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection