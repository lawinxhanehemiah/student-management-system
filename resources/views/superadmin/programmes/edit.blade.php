@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-edit text-primary"></i> 
            Edit Programme: {{ $programme->code }}
        </h1>
        <a href="{{ route('superadmin.programmes.index') }}" class="btn btn-secondary">
            <i class="feather-arrow-left"></i> Back to Programmes
        </a>
    </div>

    <!-- Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Programme Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.programmes.update', $programme) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Code -->
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Programme Code *</label>
                        <input type="text" 
                               class="form-control @error('code') is-invalid @enderror" 
                               id="code" 
                               name="code" 
                               value="{{ old('code', $programme->code) }}" 
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
                               value="{{ old('name', $programme->name) }}" 
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
                                    {{ old('study_mode', $programme->study_mode) == $mode ? 'selected' : '' }}>
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
                               value="{{ old('available_seats', $programme->available_seats) }}" 
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
                                    {{ old('status', $programme->status) == $status ? 'selected' : '' }}>
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
                                   {{ old('is_active', $programme->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Programme is Active
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Info -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="feather-info"></i>
                            <strong>Programme Information:</strong><br>
                            Created: {{ $programme->created_at->format('d M Y, H:i') }}<br>
                            Last Updated: {{ $programme->updated_at->format('d M Y, H:i') }}<br>
                            @if($programme->fees()->count() > 0)
                                Total Fee Records: {{ $programme->fees()->count() }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-secondary">
                                <i class="feather-refresh-ccw"></i> Reset Changes
                            </button>
                            <div>
                                <a href="{{ route('superadmin.programmes.fees.index', $programme) }}" 
                                   class="btn btn-info">
                                    <i class="feather-dollar-sign"></i> Manage Fees
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save"></i> Update Programme
                                </button>
                            </div>
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
        
        // Confirm reset
        $('button[type="reset"]').click(function(e) {
            if (!confirm('Are you sure you want to reset all changes?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection