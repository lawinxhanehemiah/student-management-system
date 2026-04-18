@extends('layouts.superadmin')

@section('title', 'Edit Grading System')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Grading System: {{ $gradingSystem->name }}</h3>
                </div>
                <form action="{{ route('superadmin.grading-systems.update', $gradingSystem) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $gradingSystem->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="min_score">Minimum Score *</label>
                            <input type="number" name="min_score" id="min_score" class="form-control @error('min_score') is-invalid @enderror" value="{{ old('min_score', $gradingSystem->min_score) }}" required>
                            @error('min_score')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="max_score">Maximum Score *</label>
                            <input type="number" name="max_score" id="max_score" class="form-control @error('max_score') is-invalid @enderror" value="{{ old('max_score', $gradingSystem->max_score) }}" required>
                            @error('max_score')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="grade">Grade *</label>
                            <input type="text" name="grade" id="grade" class="form-control @error('grade') is-invalid @enderror" value="{{ old('grade', $gradingSystem->grade) }}" maxlength="2" required>
                            @error('grade')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="grade_point">Grade Point *</label>
                            <input type="number" step="0.01" name="grade_point" id="grade_point" class="form-control @error('grade_point') is-invalid @enderror" value="{{ old('grade_point', $gradingSystem->grade_point) }}" required>
                            @error('grade_point')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="academic_year_id">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror">
                                <option value="">-- None --</option>
                                @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ old('academic_year_id', $gradingSystem->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $gradingSystem->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('superadmin.grading-systems.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection