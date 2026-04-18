{{-- resources/views/superadmin/hostel-fees/create.blade.php --}}
@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- SHOW ALL ERRORS --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-triangle"></i> System Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Add Hostel Accommodation Fee - {{ $programme->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.programmes.hostel-fees.index', $programme->id) }}" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Hostel Accommodation Fee:</strong> 
                        Ada ya malazi kwa wanafunzi wanaochagua kuishi hosteli.
                        <br>Hii ni ada ya <strong>JUMLA</strong> kwa muhula, si kwa kila somo.
                    </div>

                    <form method="POST" 
                          action="{{ route('superadmin.programmes.hostel-fees.store', $programme->id) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Level <span class="text-danger">*</span></label>
                                    <select name="level" class="form-control @error('level') is-invalid @enderror" required>
                                        <option value="">Select Level</option>
                                        @foreach($levels as $level)
                                            <option value="{{ $level }}" {{ old('level') == $level ? 'selected' : '' }}>
                                                Year {{ $level }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('level')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Semester <span class="text-danger">*</span></label>
                                    <select name="semester" class="form-control @error('semester') is-invalid @enderror" required>
                                        <option value="">Select Semester</option>
                                        @foreach($semesters as $key => $semester)
                                            <option value="{{ $key }}" {{ old('semester') == $key ? 'selected' : '' }}>
                                                {{ $semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hostel Fee (TZS) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">TZS</span>
                                        </div>
                                        <input type="number" 
                                               name="total_fee" 
                                               class="form-control @error('total_fee') is-invalid @enderror"
                                               value="{{ old('total_fee', 350000) }}"
                                               step="1000"
                                               min="0"
                                               required>
                                    </div>
                                    <small class="text-muted">
                                        Jumla ya ada ya hosteli kwa muhula
                                    </small>
                                    @error('total_fee')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           class="form-check-input" 
                                           id="is_active"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (Fee itakayotumika kwa mwaka huu)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Hostel Fee
                            </button>
                            <a href="{{ route('superadmin.programmes.hostel-fees.index', $programme->id) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection