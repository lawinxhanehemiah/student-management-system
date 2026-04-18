@extends('layouts.admission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-user-plus me-2"></i> Walk-in Student Registration
        </h1>
        <div>
            <a href="{{ route('admission.students.register') }}" class="btn btn-sm btn-primary me-2">
                <i class="feather-file-text me-1"></i> From Application
            </a>
            <a href="{{ route('admission.students.index') }}" class="btn btn-sm btn-secondary">
                <i class="feather-list me-1"></i> View All Students
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="feather-user me-1"></i> Walk-in Student Registration Form
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admission.students.store') }}" id="walkinForm">
                @csrf
                <input type="hidden" name="registration_type" value="walk_in">

                <div class="row">
                    <!-- Personal Information - Users Table -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="feather-user me-1"></i> Personal Information
                        </h6>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                               value="{{ old('first_name') }}" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" 
                               value="{{ old('middle_name') }}">
                        @error('middle_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                               value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" placeholder="optional">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Must be unique if provided</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Must be unique</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Academic Information - Students Table -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="feather-book me-1"></i> Academic Information
                        </h6>
                    </div>

                    @include('admission.students._form_fields', [
                        'programmes' => $programmes,
                        'courses' => $courses,
                        'academicYears' => $academicYears
                    ])
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="feather-user-check me-2"></i> Register Walk-in Student
                    </button>
                    <button type="reset" class="btn btn-secondary btn-lg px-4 ms-2">
                        <i class="feather-x me-1"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection