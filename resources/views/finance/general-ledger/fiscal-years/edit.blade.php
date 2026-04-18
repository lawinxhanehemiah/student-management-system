@extends('layouts.financecontroller')

@section('title', 'Edit Fiscal Year')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Fiscal Year</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.fiscal-years.index') }}">Fiscal Years</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.fiscal-years.show', $fiscalYear->id) }}">{{ $fiscalYear->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.general-ledger.fiscal-years.update', $fiscalYear->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h5 class="section-title">Fiscal Year Information</h5>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Be careful when changing dates. This may affect existing journal entries.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">Fiscal Year Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $fiscalYear->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label required">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" 
                                       value="{{ old('start_date', $fiscalYear->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label required">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" 
                                       value="{{ old('end_date', $fiscalYear->end_date->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes', $fiscalYear->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Current Status
                                </h6>
                                <div class="mb-2">
                                    <strong>Status:</strong> {!! $fiscalYear->status_badge !!}
                                </div>
                                <div class="mb-2">
                                    <strong>Active:</strong> {!! $fiscalYear->active_badge !!}
                                </div>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Created: {{ $fiscalYear->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Fiscal Year
                </button>
                <a href="{{ route('finance.general-ledger.fiscal-years.show', $fiscalYear->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection