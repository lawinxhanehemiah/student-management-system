@extends('layouts.financecontroller')

@section('title', 'Edit Contract')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Contract</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.contracts.index') }}">Contracts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.contracts.show', $contract->id) }}">{{ $contract->contract_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Editing this contract will reset its status to draft if currently active.
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.procurement.contracts.update', $contract->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Contract Number</label>
                                <input type="text" class="form-control" value="{{ $contract->contract_number }}" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Title</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $contract->title) }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Supplier</label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                            {{ old('supplier_id', $contract->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Manager</label>
                                <select name="project_manager" class="form-select @error('project_manager') is-invalid @enderror">
                                    <option value="">None</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('project_manager', $contract->project_manager) == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_manager') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contract Period -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Contract Period</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Start Date</label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">End Date</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" required>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Financial Information</h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Contract Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" name="contract_value" 
                                           class="form-control @error('contract_value') is-invalid @enderror" 
                                           value="{{ old('contract_value', $contract->contract_value) }}" required>
                                </div>
                                @error('contract_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Payment Terms</label>
                                <textarea name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror" 
                                          rows="3">{{ old('payment_terms', $contract->payment_terms) }}</textarea>
                                @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Delivery Terms</label>
                                <textarea name="delivery_terms" class="form-control @error('delivery_terms') is-invalid @enderror" 
                                          rows="3">{{ old('delivery_terms', $contract->delivery_terms) }}</textarea>
                                @error('delivery_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Terms and Conditions</h5>
                        
                        <div class="mb-3">
                            <textarea name="terms" class="form-control @error('terms') is-invalid @enderror" 
                                      rows="4">{{ old('terms', $contract->terms) }}</textarea>
                            @error('terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Current Status
                                </h6>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $statusColors[$contract->status] ?? 'secondary' }}">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </p>
                                <p><strong>Created:</strong> {{ $contract->created_at->format('d/m/Y') }}</p>
                                <p><strong>Last Updated:</strong> {{ $contract->updated_at->format('d/m/Y') }}</p>
                                <hr>
                                <p class="small text-muted">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Changes will reset status to draft
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Contract
                </button>
                <a href="{{ route('finance.procurement.contracts.show', $contract->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection