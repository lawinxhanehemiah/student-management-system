@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-plus-circle text-primary"></i> 
            Add Fee for: {{ $programme->name }} ({{ $programme->code }})
        </h1>
        <div>
            <a href="{{ route('superadmin.programmes.fees.index', $programme->id) }}" class="btn btn-secondary">
                <i class="feather-arrow-left"></i> Back to Fees
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('superadmin.programmes.fees.store', $programme->id) }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} ({{ date('Y', strtotime($year->start_date)) }} - {{ date('Y', strtotime($year->end_date)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="level" class="form-control @error('level') is-invalid @enderror" required>
                                <option value="">Select Level</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}" {{ old('level') == $level ? 'selected' : '' }}>
                                        Level {{ $level }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Registration Fee (TZS) <span class="text-danger">*</span></label>
                            <input type="text" name="registration_fee" class="form-control @error('registration_fee') is-invalid @enderror" 
                                   value="{{ old('registration_fee', 0) }}" 
                                   min="0" step="1000" required>
                            @error('registration_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Semester 1 Fee (TZS) <span class="text-danger">*</span></label>
                            <input type="text" name="semester_1_fee" class="form-control @error('semester_1_fee') is-invalid @enderror" 
                                   value="{{ old('semester_1_fee', 0) }}" 
                                   min="0" step="1000" required>
                            @error('semester_1_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Semester 2 Fee (TZS) <span class="text-danger">*</span></label>
                            <input type="text~" name="semester_2_fee" class="form-control @error('semester_2_fee') is-invalid @enderror" 
                                   value="{{ old('semester_2_fee', 0) }}" 
                                   min="0" step="1000" required>
                            @error('semester_2_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-save"></i> Save Fee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-format numbers as user types
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('blur', function() {
        if(this.value) {
            this.value = parseInt(this.value).toLocaleString();
        }
    });
    
    input.addEventListener('focus', function() {
        if(this.value) {
            this.value = this.value.replace(/,/g, '');
        }
    });
});
</script>

<style>
.form-control.is-invalid {
    border-color: #dc3545;
}
</style>
@endsection