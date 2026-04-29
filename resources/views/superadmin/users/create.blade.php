@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <!-- Stepper Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Create New User</h4>
                    <div class="stepper-wrapper">
                        <div class="stepper-item active" id="step1-header">
                            <div class="step-counter">1</div>
                            <div class="step-name">Basic Info</div>
                        </div>
                        <div class="stepper-item" id="step2-header">
                            <div class="step-counter">2</div>
                            <div class="step-name">Role Details</div>
                        </div>
                        <div class="stepper-item" id="step3-header">
                            <div class="step-counter">3</div>
                            <div class="step-name">Review</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('superadmin.users.store') }}" id="userForm" enctype="multipart/form-data">
                @csrf

                <!-- STEP 1: BASIC INFO (users table) -->
                <div class="card border-0 shadow-sm mb-4 step-card" id="step1">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Step 1: Basic Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="First name" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Middle Name <span class="text-danger">*</span></label>
                                <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}" placeholder="Middle name" required>
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Last name" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                                <select name="role" id="roleSelect" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">-- Select Role --</option>
                                    @foreach($roles as $role)
                                        {{-- Show only Student and Staff roles, hide Applicant --}}
                                        @if($role->name !== 'Applicant')
                                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Hidden field for user_type (set by JS) -->
                            <input type="hidden" name="user_type" id="userTypeInput" value="{{ old('user_type') }}">
                        </div>
                    </div>
                    <div class="card-footer bg-light py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary next-step" data-step="1">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: ROLE SPECIFIC INFO -->
                <div class="card border-0 shadow-sm mb-4 step-card" id="step2" style="display:none;">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i> Step 2: Role Specific Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- EMAIL FIELD (dynamic) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Email Address <span class="text-danger" id="emailRequired">*</span></label>
                                <input type="email" name="email" id="emailInput" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter email address">
                                <small class="text-muted" id="emailHelp"></small>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- DEPARTMENT FIELD (for staff roles) -->
<div id="departmentField" style="display: none;" class="mb-4">
    <div class="row">
        <div class="col-md-12">
            <label class="form-label fw-bold">Department <span class="text-danger" id="departmentRequired">*</span></label>
            <select name="department_id" id="departmentSelect" class="form-select @error('department_id') is-invalid @enderror">
                <option value="">-- Select Department --</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }} ({{ $department->code }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Required for Head of Department</small>
            @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
                        <!-- STUDENT FIELDS - These match controller validation -->
                        <div id="studentFields" style="display: {{ old('role') == 'Student' ? 'block' : 'none' }};">
                            <h6 class="mb-3 text-success"><i class="fas fa-graduation-cap me-2"></i> Student Information</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Intake <span class="text-danger">*</span></label>
                                    <select name="intake" class="form-control @error('intake') is-invalid @enderror">
                                        <option value="">-- Select Intake --</option>
                                        <option value="March" {{ old('intake') == 'March' ? 'selected' : '' }}>March</option>
                                        <option value="September" {{ old('intake') == 'September' ? 'selected' : '' }}>September</option>
                                    </select>
                                    @error('intake')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Programme <span class="text-danger">*</span></label>
                                    <select name="programme_id" class="form-control @error('programme_id') is-invalid @enderror">
                                        <option value="">-- Select Programme --</option>
                                        @foreach($programmes as $programme)
                                            <option value="{{ $programme->id }}" {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                                                {{ $programme->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('programme_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Study Mode <span class="text-danger">*</span></label>
                                    <select name="study_mode" class="form-control @error('study_mode') is-invalid @enderror">
                                        <option value="">-- Select Study Mode --</option>
                                        <option value="full_time" {{ old('study_mode') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('study_mode') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="distance" {{ old('study_mode') == 'distance' ? 'selected' : '' }}>Distance</option>
                                    </select>
                                    @error('study_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Current Level <span class="text-danger">*</span></label>
                                    <select name="current_level" class="form-control @error('current_level') is-invalid @enderror">
                                        <option value="">-- Select Level --</option>
                                        @for($i = 1; $i <= 6; $i++)
                                            <option value="{{ $i }}" {{ old('current_level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('current_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Current Semester <span class="text-danger">*</span></label>
                                    <select name="current_semester" class="form-control @error('current_semester') is-invalid @enderror">
                                        <option value="">-- Select Semester --</option>
                                        <option value="1" {{ old('current_semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                                        <option value="2" {{ old('current_semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                                    </select>
                                    @error('current_semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror">
                                        <option value="">-- Select Academic Year --</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nacte Registration <span class="text-danger">*</span></label>
                                    <input type="text" name="nacte_reg_number" class="form-control @error('nacte_reg_number') is-invalid @enderror" value="{{ old('nacte_reg_number') }}" placeholder="Nacte regsration" required>
                                    @error('nacte_reg_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Guardian Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror" value="{{ old('guardian_name') }}" placeholder="Guardian full name">
                                    @error('guardian_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Guardian Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_phone" class="form-control @error('guardian_phone') is-invalid @enderror" value="{{ old('guardian_phone') }}" placeholder="Guardian phone number">
                                    @error('guardian_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Registration number will be auto-generated. Annual tuition invoice will be created.
                            </div>
                        </div>

                        <!-- STAFF FIELDS (including department) -->
                        <div id="staffFields" style="display: {{ old('role') && old('role') !== 'Student' ? 'block' : 'none' }};">
                            <h6 class="mb-3 text-primary"><i class="fas fa-briefcase me-2"></i> Staff Information</h6>
                            
                            <!-- Department already shown above -->
                            
                            <div class="alert alert-light border mt-3">
                                <i class="fas fa-info-circle me-2 text-info"></i>
                                <strong>Note:</strong> For <strong>Head of Department</strong>, department selection is required.
                                <br><small>Other staff may be assigned to departments as needed.</small>
                            </div>
                        </div>

                        <!-- COMMON FIELDS (For ALL roles) -->
                        <hr class="my-4">
                        <h6 class="mb-3"><i class="fas fa-user-circle me-2"></i> Additional Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">-- Select Gender --</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">Max: 2MB (JPG, PNG, GIF)</small>
                                @error('profile_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary prev-step" data-step="2">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary next-step" data-step="2">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: REVIEW -->
                <div class="card border-0 shadow-sm mb-4 step-card" id="step3" style="display:none;">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i> Step 3: Review & Create</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info" id="credentialPreview">
                            <i class="fas fa-info-circle me-2"></i>
                            Select role to see login credentials
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>User Account Info</h6>
                                <table class="table table-sm">
                                    <tr><th>Name:</th> <td id="reviewName"></td></tr>
                                    <tr><th>Email:</th> <td id="reviewEmail"></td></tr>
                                    <tr><th>Phone:</th> <td id="reviewPhone"></td></tr>
                                    <tr><th>Gender:</th> <td id="reviewGender"></td></tr>
                                    <tr><th>Role:</th> <td id="reviewRole"></td></tr>
                                    <tr><th>Department:</th> <td id="reviewDepartment"></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Login Credentials</h6>
                                <table class="table table-sm">
                                    <tr><th>Username:</th> <td id="reviewUsername"></td></tr>
                                    <tr><th>Password:</th> <td id="reviewPassword"></td></tr>
                                    <tr><th>Change on Login:</th> <td id="reviewMustChange">Yes</td></tr>
                                </table>
                            </div>
                        </div>

                        <div id="reviewDetails" class="mt-3 p-3 bg-light rounded">
                            <!-- Dynamic content based on role -->
                        </div>

                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="confirmCreate" required>
                            <label class="form-check-label fw-bold" for="confirmCreate">
                                I confirm that all information is correct
                            </label>
                        </div>
                    </div>
                    <div class="card-footer bg-light py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary prev-step" data-step="3">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-user-plus me-2"></i> Create User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stepper-wrapper {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
}

.stepper-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    text-align: center;
}

.stepper-item::before,
.stepper-item::after {
    content: '';
    position: absolute;
    top: 20px;
    height: 2px;
    background: #dee2e6;
    width: 50%;
}

.stepper-item::before {
    left: 0;
}

.stepper-item::after {
    right: 0;
}

.stepper-item:first-child::before {
    display: none;
}

.stepper-item:last-child::after {
    display: none;
}

.step-counter {
    position: relative;
    z-index: 5;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    font-weight: bold;
    margin-bottom: 8px;
    border: 2px solid #dee2e6;
}

.stepper-item.active .step-counter {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.stepper-item.completed .step-counter {
    background: #198754;
    color: white;
    border-color: #198754;
}

.stepper-item.active .step-name {
    color: #0d6efd;
    font-weight: 600;
}
</style>

<script>
let currentStep = 1;

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    
    // Initialize if old value exists
    if (roleSelect.value) {
        handleRoleChange();
        // Check if we have errors in step 2
        const hasStep2Errors = document.querySelectorAll('#step2 .is-invalid').length > 0;
        if (hasStep2Errors) {
            showStep(2);
        }
    }

    roleSelect.addEventListener('change', handleRoleChange);

    // Next/Prev buttons
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.dataset.step);
            if (validateStep(step)) {
                if (step === 2) updateReview();
                showStep(step + 1);
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.dataset.step);
            showStep(step - 1);
        });
    });

    // Form submit
    document.getElementById('userForm').addEventListener('submit', function(e) {
        if (!document.getElementById('confirmCreate').checked) {
            e.preventDefault();
            alert('Please confirm that all information is correct');
            return false;
        }
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating...';
    });
});

function handleRoleChange() {
    const role = document.getElementById('roleSelect').value;
    const userTypeInput = document.getElementById('userTypeInput');
    const emailRequired = document.getElementById('emailRequired');
    const emailHelp = document.getElementById('emailHelp');
    const studentFields = document.getElementById('studentFields');
    const staffFields = document.getElementById('staffFields');
    const departmentField = document.getElementById('departmentField');
    const departmentSelect = document.getElementById('departmentSelect');
    const departmentRequired = document.getElementById('departmentRequired');
    
    // Hide all sections first
    studentFields.style.display = 'none';
    staffFields.style.display = 'none';
    departmentField.style.display = 'none';
    
    // Set user type and show relevant fields
    if (role === 'Student') {
        userTypeInput.value = 'student';
        studentFields.style.display = 'block';
        emailRequired.style.display = 'none';
        emailHelp.textContent = 'Email is optional for students';
        departmentField.style.display = 'none';
        departmentSelect.removeAttribute('required');
    } else if (role) {
        userTypeInput.value = 'staff';
        staffFields.style.display = 'block';
        emailRequired.style.display = 'inline';
        emailHelp.textContent = 'Email is required for staff';
        
        // Show department field for all staff, but make it required only for HOD
        departmentField.style.display = 'block';
        
        if (role === 'Head_of_Department') {
            departmentRequired.style.display = 'inline';
            departmentSelect.setAttribute('required', 'required');
            departmentField.querySelector('small').textContent = 'Department is REQUIRED for Head of Department';
        } else {
            departmentRequired.style.display = 'none';
            departmentSelect.removeAttribute('required');
            departmentField.querySelector('small').textContent = 'Department is optional for other staff';
        }
    }
    
    updateCredentialPreview();
}

function validateStep(step) {
    if (step === 1) {
        const firstName = document.querySelector('[name="first_name"]').value;
        const middleName = document.querySelector('[name="middle_name"]').value;
        const lastName = document.querySelector('[name="last_name"]').value;
        const role = document.getElementById('roleSelect').value;
        
        if (!firstName || !middleName || !lastName || !role) {
            alert('Please fill in all required fields in Step 1');
            return false;
        }
        return true;
    }
    
    if (step === 2) {
        const role = document.getElementById('roleSelect').value;
        
        // Email validation for staff
        if (role !== 'Student') {
            const email = document.getElementById('emailInput').value;
            if (!email || !email.includes('@')) {
                alert('Valid email is required for staff');
                document.getElementById('emailInput').focus();
                return false;
            }
        }
        
        // Department validation for Head of Department
        if (role === 'Head_of_Department') {
            const department = document.getElementById('departmentSelect').value;
            if (!department) {
                alert('Department is required for Head of Department');
                document.getElementById('departmentSelect').focus();
                return false;
            }
        }
        
        // Student validation
        if (role === 'Student') {
            const required = ['intake', 'programme_id', 'study_mode', 'current_level', 'current_semester', 'academic_year_id', 'guardian_name', 'guardian_phone'];
            for (let field of required) {
                const element = document.querySelector(`[name="${field}"]`);
                if (element && !element.value) {
                    alert(`${field.replace('_', ' ')} is required`);
                    element.focus();
                    return false;
                }
            }
        }
        
        return true;
    }
    
    return true;
}

function showStep(step) {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    
    document.getElementById('step1-header').classList.remove('active', 'completed');
    document.getElementById('step2-header').classList.remove('active', 'completed');
    document.getElementById('step3-header').classList.remove('active', 'completed');
    
    document.getElementById(`step${step}`).style.display = 'block';
    
    for (let i = 1; i < step; i++) {
        document.getElementById(`step${i}-header`).classList.add('completed');
    }
    document.getElementById(`step${step}-header`).classList.add('active');
    
    currentStep = step;
    window.scrollTo(0, 0);
}

function updateReview() {
    const role = document.getElementById('roleSelect').value;
    const departmentSelect = document.getElementById('departmentSelect');
    
    document.getElementById('reviewName').textContent = 
        `${document.querySelector('[name="first_name"]').value} ${document.querySelector('[name="middle_name"]').value} ${document.querySelector('[name="last_name"]').value}`;
    document.getElementById('reviewEmail').textContent = document.getElementById('emailInput').value || 'N/A';
    document.getElementById('reviewPhone').textContent = document.querySelector('[name="phone"]').value || 'N/A';
    document.getElementById('reviewGender').textContent = document.querySelector('[name="gender"] option:checked')?.text || 'N/A';
    document.getElementById('reviewRole').textContent = role;
    document.getElementById('reviewDepartment').textContent = departmentSelect.options[departmentSelect.selectedIndex]?.text || 'Not assigned';
    
    let username, password;
    if (role === 'Student') {
        username = 'Auto-generated registration number';
        password = 'Registration number';
    } else {
        username = document.getElementById('emailInput').value;
        password = 'Email address';
    }
    
    document.getElementById('reviewUsername').textContent = username;
    document.getElementById('reviewPassword').textContent = password;
    
    let details = '<h6 class="fw-bold mb-3">Role Details:</h6>';
    
    if (role === 'Student') {
        details += `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Intake:</strong> ${document.querySelector('[name="intake"]')?.value || '-'}</p>
                    <p><strong>Programme:</strong> ${document.querySelector('[name="programme_id"] option:checked')?.text || '-'}</p>
                    <p><strong>Study Mode:</strong> ${document.querySelector('[name="study_mode"] option:checked')?.text || '-'}</p>
                    <p><strong>Level:</strong> ${document.querySelector('[name="current_level"]')?.value || '-'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Semester:</strong> ${document.querySelector('[name="current_semester"]')?.value || '-'}</p>
                    <p><strong>Academic Year:</strong> ${document.querySelector('[name="academic_year_id"] option:checked')?.text || '-'}</p>
                    <p><strong>Guardian:</strong> ${document.querySelector('[name="guardian_name"]')?.value || '-'}</p>
                    <p><strong>Guardian Phone:</strong> ${document.querySelector('[name="guardian_phone"]')?.value || '-'}</p>
                </div>
            </div>
        `;
    } else {
        details += `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Department:</strong> ${departmentSelect.options[departmentSelect.selectedIndex]?.text || 'Not assigned'}</p>
                    <p><strong>Staff Type:</strong> ${role}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> ${document.getElementById('emailInput').value || 'N/A'}</p>
                </div>
            </div>
        `;
    }
    
    document.getElementById('reviewDetails').innerHTML = details;
}

function updateCredentialPreview() {
    const role = document.getElementById('roleSelect').value;
    const preview = document.getElementById('credentialPreview');
    
    if (role === 'Student') {
        preview.innerHTML = '<i class="fas fa-info-circle me-2"></i>Student: Registration number will be generated and used as password';
    } else if (role === 'Head_of_Department') {
        preview.innerHTML = '<i class="fas fa-info-circle me-2"></i>Head of Department: Email as username and password. Department selection is required.';
    } else if (role) {
        preview.innerHTML = '<i class="fas fa-info-circle me-2"></i>Staff: Email as username and password. Department is optional.';
    } else {
        preview.innerHTML = '<i class="fas fa-info-circle me-2"></i>Select role to see login credentials';
    }
}
</script>
@endsection