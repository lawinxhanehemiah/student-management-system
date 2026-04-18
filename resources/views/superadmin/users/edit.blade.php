@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            {{-- Card Container --}}
            <div class="card border-0 shadow-sm">
                {{-- Card Header --}}
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                <i data-feather="edit-2" style="width: 20px; height: 20px;" class="text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fw-bold mb-0">Edit User</h6>
                            <small class="text-muted">Update user information</small>
                        </div>
                        <a href="{{ route('superadmin.users.index') }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body p-4">
                    <form method="POST" 
                          action="{{ route('superadmin.users.update', $user->id) }}"
                          enctype="multipart/form-data"
                          id="editUserForm">
                        @csrf
                        @method('PUT')

                        {{-- Personal Information Section --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i data-feather="user" style="width: 16px; height: 16px;" class="me-2"></i>
                                Personal Information
                            </h6>
                            
                            <div class="row g-3">
                                {{-- First Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="user" style="width: 14px; height: 14px;" class="me-1"></i>
                                        First Name
                                    </label>
                                    <input type="text" 
                                           name="first_name" 
                                           class="form-control form-control-sm"
                                           value="{{ old('first_name', $user->first_name) }}"
                                           required
                                           placeholder="Enter first name">
                                </div>

                                {{-- Last Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="user" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Last Name
                                    </label>
                                    <input type="text" 
                                           name="last_name" 
                                           class="form-control form-control-sm"
                                           value="{{ old('last_name', $user->last_name) }}"
                                           required
                                           placeholder="Enter last name">
                                </div>

                                {{-- Email --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-medium">
                                        <i data-feather="mail" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Email Address
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control form-control-sm"
                                           value="{{ old('email', $user->email) }}"
                                           required
                                           placeholder="user@example.com">
                                </div>

                                {{-- Phone Number --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="phone" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           class="form-control form-control-sm"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="+255 123 456 789">
                                </div>

                                {{-- Gender --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="users" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Gender
                                    </label>
                                    <select name="gender" class="form-select form-select-sm">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Account Information Section --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i data-feather="settings" style="width: 16px; height: 16px;" class="me-2"></i>
                                Account Settings
                            </h6>
                            
                            <div class="row g-3">
                                {{-- User Type --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="briefcase" style="width: 14px; height: 14px;" class="me-1"></i>
                                        User Type
                                    </label>
                                    <select name="user_type" id="user_type" class="form-select form-select-sm">
                                        <option value="student" {{ old('user_type', $user->user_type) == 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="staff" {{ old('user_type', $user->user_type) == 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="applicant" {{ old('user_type', $user->user_type) == 'applicant' ? 'selected' : '' }}>Applicant</option>
                                    </select>
                                </div>

                                {{-- Registration Number --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="hash" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Registration Number
                                    </label>
                                    <input type="text" 
                                           name="registration_number" 
                                           class="form-control form-control-sm"
                                           value="{{ old('registration_number', $user->registration_number) }}"
                                           placeholder="REG/2024/001">
                                </div>

                                {{-- Role --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-medium">
                                        <i data-feather="award" style="width: 14px; height: 14px;" class="me-1"></i>
                                        User Role
                                    </label>
                                    <select name="role" class="form-select form-select-sm">
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ ucwords($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="activity" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Account Status
                                    </label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>

                                {{-- Must Change Password --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="lock" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Password Reset
                                    </label>
                                    <select name="must_change_password" class="form-select form-select-sm">
                                        <option value="0" {{ old('must_change_password', $user->must_change_password) == 0 ? 'selected' : '' }}>No Reset Required</option>
                                        <option value="1" {{ old('must_change_password', $user->must_change_password) == 1 ? 'selected' : '' }}>Require Password Change</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Profile Photo Section --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i data-feather="image" style="width: 16px; height: 16px;" class="me-2"></i>
                                Profile Photo
                            </h6>
                            
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    @if($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                             alt="Profile Photo"
                                             class="rounded-circle border"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center mx-auto"
                                             style="width: 80px; height: 80px;">
                                            <i data-feather="user" style="width: 32px; height: 32px;" class="text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-9">
                                    <label class="form-label fw-medium">
                                        <i data-feather="upload" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Upload New Photo
                                    </label>
                                    <input type="file" 
                                           name="profile_photo" 
                                           class="form-control form-control-sm"
                                           accept="image/*"
                                           onchange="previewImage(event)">
                                    
                                    <small class="text-muted d-block mt-1">
                                        <i data-feather="info" style="width: 12px; height: 12px;" class="me-1"></i>
                                        Maximum file size: 2MB. Supported formats: JPG, PNG, GIF
                                    </small>
                                    
                                    <div class="mt-2 d-none" id="imagePreview">
                                        <small class="text-muted">Preview:</small>
                                        <img id="preview" class="img-thumbnail mt-1" style="max-width: 100px; max-height: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information Section --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i data-feather="info" style="width: 16px; height: 16px;" class="me-2"></i>
                                Additional Information
                            </h6>
                            
                            <div class="row g-3">
                                {{-- Middle Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="user" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Middle Name (Optional)
                                    </label>
                                    <input type="text" 
                                           name="middle_name" 
                                           class="form-control form-control-sm"
                                           value="{{ old('middle_name', $user->middle_name) }}"
                                           placeholder="Enter middle name">
                                </div>

                                {{-- Course/Program (generic) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        <i data-feather="book" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Course/Program (Optional)
                                    </label>
                                    <input type="text" 
                                           name="course" 
                                           class="form-control form-control-sm"
                                           value="{{ old('course', $user->course) }}"
                                           placeholder="e.g., Computer Science">
                                </div>
                            </div>
                        </div>

                        {{-- STUDENT SPECIFIC FIELDS (only visible if user is a student) --}}
                        <div id="studentFields" 
                             class="mb-4" 
                             style="{{ old('user_type', $user->user_type) == 'student' ? '' : 'display: none;' }}">
                            <h6 class="fw-bold mb-3 text-success">
                                <i data-feather="book-open" style="width: 16px; height: 16px;" class="me-2"></i>
                                Student Academic Information
                            </h6>
                            
                            <div class="row g-3">
                                {{-- Programme --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Programme</label>
                                    <select name="programme_id" class="form-select form-select-sm">
                                        <option value="">Select Programme</option>
                                        @foreach($programmes ?? [] as $programme)
                                            <option value="{{ $programme->id }}"
                                                {{ old('programme_id', optional($user->student)->programme_id) == $programme->id ? 'selected' : '' }}>
                                                {{ $programme->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Intake --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Intake</label>
                                    <select name="intake" class="form-select form-select-sm">
                                        <option value="March" {{ old('intake', optional($user->student)->intake) == 'March' ? 'selected' : '' }}>March</option>
                                        <option value="September" {{ old('intake', optional($user->student)->intake) == 'September' ? 'selected' : '' }}>September</option>
                                    </select>
                                </div>

                                {{-- Study Mode --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Study Mode</label>
                                    <select name="study_mode" class="form-select form-select-sm">
                                        <option value="full_time" {{ old('study_mode', optional($user->student)->study_mode) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('study_mode', optional($user->student)->study_mode) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="distance" {{ old('study_mode', optional($user->student)->study_mode) == 'distance' ? 'selected' : '' }}>Distance Learning</option>
                                    </select>
                                </div>

                                {{-- Current Level --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Current Level (Year)</label>
                                    <input type="number" name="current_level" class="form-control form-control-sm"
                                           value="{{ old('current_level', optional($user->student)->current_level) }}" min="1" max="6">
                                </div>

                                {{-- Current Semester --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Current Semester</label>
                                    <select name="current_semester" class="form-select form-select-sm">
                                        <option value="1" {{ old('current_semester', optional($user->student)->current_semester) == 1 ? 'selected' : '' }}>Semester 1</option>
                                        <option value="2" {{ old('current_semester', optional($user->student)->current_semester) == 2 ? 'selected' : '' }}>Semester 2</option>
                                    </select>
                                </div>

                                {{-- Academic Year --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Academic Year</label>
                                    <select name="academic_year_id" class="form-select form-select-sm">
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears ?? [] as $academicYear)
                                            <option value="{{ $academicYear->id }}"
                                                {{ old('academic_year_id', optional($user->student)->academic_year_id) == $academicYear->id ? 'selected' : '' }}>
                                                {{ $academicYear->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Guardian Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Guardian Name</label>
                                    <input type="text" name="guardian_name" class="form-control form-control-sm"
                                           value="{{ old('guardian_name', optional($user->student)->guardian_name) }}">
                                </div>

                                {{-- Guardian Phone --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Guardian Phone</label>
                                    <input type="text" name="guardian_phone" class="form-control form-control-sm"
                                           value="{{ old('guardian_phone', optional($user->student)->guardian_phone) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="border-top pt-4">
                            <div class="d-flex justify-content-between">
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="window.history.back()">
                                    <i data-feather="x" style="width: 14px; height: 14px;" class="me-1"></i>
                                    Cancel
                                </button>
                                
                                <div class="d-flex gap-2">
                                    <a href="{{ route('superadmin.users.show', $user->id) }}"
                                       class="btn btn-outline-info btn-sm">
                                        <i data-feather="eye" style="width: 14px; height: 14px;" class="me-1"></i>
                                        View User
                                    </a>
                                    
                                    <button type="submit" 
                                            class="btn btn-primary btn-sm px-4">
                                        <i data-feather="save" style="width: 14px; height: 14px;" class="me-1"></i>
                                        Update User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.form-label {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem !important;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('d-none');
        preview.src = '';
    }
}

// Toggle student fields when user type changes
document.getElementById('user_type').addEventListener('change', function() {
    const studentFields = document.getElementById('studentFields');
    if (this.value === 'student') {
        studentFields.style.display = 'block';
    } else {
        studentFields.style.display = 'none';
    }
});

// Form validation
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const email = this.querySelector('input[name="email"]').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endsection