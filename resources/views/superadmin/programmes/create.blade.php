@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-plus-circle text-primary"></i> 
            Add New Programme
        </h1>
        <a href="{{ route('superadmin.programmes.index') }}" class="btn btn-secondary">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <!-- Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Programme Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.programmes.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <!-- Code -->
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Programme Code *</label>
                        <input type="text" 
                               class="form-control @error('code') is-invalid @enderror" 
                               id="code" 
                               name="code" 
                               value="{{ old('code') }}" 
                               required
                               placeholder="e.g., BIT, BBA">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Unique programme code</small>
                    </div>
                    
                    <!-- Name -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Programme Name *</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required
                               placeholder="e.g., Bachelor of Information Technology">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Study Mode -->
                    <div class="col-md-6 mb-3">
                        <label for="study_mode" class="form-label">Study Mode *</label>
                        <select class="form-control @error('study_mode') is-invalid @enderror" 
                                id="study_mode" 
                                name="study_mode" 
                                required>
                            <option value="">Select Study Mode</option>
                            @foreach($studyModes as $mode)
                                <option value="{{ $mode }}" 
                                    {{ old('study_mode') == $mode ? 'selected' : '' }}>
                                    {{ $mode }}
                                </option>
                            @endforeach
                        </select>
                        @error('study_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Available Seats -->
                    <div class="col-md-6 mb-3">
                        <label for="available_seats" class="form-label">Available Seats *</label>
                        <input type="number" 
                               class="form-control @error('available_seats') is-invalid @enderror" 
                               id="available_seats" 
                               name="available_seats" 
                               value="{{ old('available_seats', 0) }}" 
                               min="0" 
                               required>
                        @error('available_seats')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Set 0 for unlimited seats</small>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" 
                                    {{ old('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Active Checkbox -->
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Programme is Active
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-secondary">
                                <i class="feather-refresh-ccw"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Save Programme
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-capitalize code
        $('#code').on('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endsection