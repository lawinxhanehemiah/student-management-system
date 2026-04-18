@extends('layouts.financecontroller')

@section('title', 'Create Tender')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Create New Tender</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.tenders.index') }}">Tenders</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.procurement.tenders.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Tender Number</label>
                    <input type="text" class="form-control" value="{{ $nextNumber }}" readonly>
                    <small class="text-muted">Auto-generated</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label required">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label required">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="4" required>{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Related Requisition</label>
                    <select name="requisition_id" class="form-select @error('requisition_id') is-invalid @enderror">
                        <option value="">None</option>
                        @foreach($requisitions as $req)
                            <option value="{{ $req->id }}" {{ old('requisition_id') == $req->id ? 'selected' : '' }}>
                                {{ $req->requisition_number }} - {{ $req->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('requisition_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Tender Type</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">Select</option>
                        <option value="open" {{ old('type') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ old('type') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="restricted" {{ old('type') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                        <option value="direct" {{ old('type') == 'direct' ? 'selected' : '' }}>Direct</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Closing Date</label>
                    <input type="date" name="closing_date" class="form-control @error('closing_date') is-invalid @enderror" 
                           value="{{ old('closing_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    @error('closing_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Estimated Value</label>
                    <div class="input-group">
                        <span class="input-group-text">TZS</span>
                        <input type="number" step="0.01" name="estimated_value" 
                               class="form-control @error('estimated_value') is-invalid @enderror" 
                               value="{{ old('estimated_value') }}" required>
                    </div>
                    @error('estimated_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Terms and Conditions</label>
                    <textarea name="terms_and_conditions" class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                              rows="4">{{ old('terms_and_conditions') }}</textarea>
                    @error('terms_and_conditions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Tender
                </button>
                <a href="{{ route('finance.procurement.tenders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection