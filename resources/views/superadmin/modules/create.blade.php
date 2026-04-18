@extends('layouts.superadmin')

@section('title', 'Add New Module')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-title">
            <h5>Add New Module</h5>
        </div>
        <div class="page-header-right">
            <a href="{{ route('superadmin.modules.index') }}" class="btn btn-secondary">Back to Modules</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.modules.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Module Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" 
                               class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code') }}" 
                               placeholder="e.g., CS101" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Module Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" 
                               placeholder="e.g., Introduction to Programming" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="nta_level" class="form-label">NTA Level <span class="text-danger">*</span></label>
                        <select name="nta_level" id="nta_level" class="form-control @error('nta_level') is-invalid @enderror" required>
                            <option value="">Select Level</option>
                            @for($i=1; $i<=6; $i++)
                                <option value="{{ $i }}" {{ old('nta_level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                            @endfor
                        </select>
                        @error('nta_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Module Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Core" {{ old('type') == 'Core' ? 'selected' : '' }}>Core (Lazima)</option>
                            <option value="Fundamental" {{ old('type') == 'Fundamental' ? 'selected' : '' }}>Fundamental (Msingi)</option>
                            <option value="Elective" {{ old('type') == 'Elective' ? 'selected' : '' }}>Elective (Chaguzi)</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="default_credits" class="form-label">Default Credits <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" name="default_credits" id="default_credits" 
                               class="form-control @error('default_credits') is-invalid @enderror" 
                               value="{{ old('default_credits') }}" 
                               placeholder="e.g., 3.0" required>
                        @error('default_credits')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="pass_mark" class="form-label">Pass Mark (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" name="pass_mark" id="pass_mark" 
                               class="form-control @error('pass_mark') is-invalid @enderror" 
                               value="{{ old('pass_mark', 50) }}" required>
                        @error('pass_mark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            <option value="">-- No Department / Not Assigned --</option>
                            @foreach($departments as $id => $name)
                                <option value="{{ $id }}" {{ old('department_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Optional: Assign module to a specific department.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" 
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Brief description of the module...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" class="form-check-label">Active (module can be used in programmes)</label>
                </div>

                <button type="submit" class="btn btn-primary">Save Module</button>
                <a href="{{ route('superadmin.modules.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection