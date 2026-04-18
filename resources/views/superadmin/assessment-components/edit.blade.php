@extends('layouts.superadmin')

@section('title', 'Edit Assessment Component')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Component: {{ $component->name }}</h3>
                </div>
                <form action="{{ route('superadmin.assessment-components.update', $component) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="module_id">Module *</label>
                            <select name="module_id" id="module_id" class="form-control @error('module_id') is-invalid @enderror" required>
                                <option value="">Select Module</option>
                                @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ old('module_id', $component->module_id) == $module->id ? 'selected' : '' }}>{{ $module->code }} - {{ $module->name }}</option>
                                @endforeach
                            </select>
                            @error('module_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Component Name *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $component->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="weight">Weight (%) *</label>
                            <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $component->weight) }}" required>
                            @error('weight')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $component->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('superadmin.assessment-components.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection