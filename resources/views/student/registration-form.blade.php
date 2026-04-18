@extends('layouts.students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Modern Header with Stats -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2 class="mb-1">Student Registration Form</h2>
                    <p class="text-muted">Please! Preview your information, correct if needed, otherwise just click "Save Changes" button to continue.</p>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-end gap-2">
                        <div class="badge bg-primary p-2">
                            <i class="feather-calendar me-1"></i> {{ $student->academicYear->name ?? 'N/A' }}
                        </div>
                        <div class="badge bg-info p-2">
                            <i class="feather-log-in me-1"></i> {{ $lastLogin ?? 'N/A' }}
                        </div>
                        <div class="badge bg-success p-2">
                            <i class="feather-sun me-1"></i> {{ now()->format('d M, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Start -->
            <form action="{{ route('student.profile.update') }}" method="POST" id="registrationForm">
                @csrf
                @method('PUT')
                
                <!-- ==================== STUDY PROGRAMME INFORMATION ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-book-open me-2"></i> Study Programme Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Year of admission: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $student->academicYear->name ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Campus: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $student->campus ?? 'Ipuli Cumpus' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Program Registered: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $student->programme->code ?? $student->programme->name ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Class Stream:</label>
                                <input type="text" name="stream" class="form-control" 
                                       placeholder="e.g., A, B, C" 
                                       value="{{ old('stream', $student->stream) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Level of Study: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="@php
                                           $level = $student->current_level ?? 1;
                                           if ($level <= 4) echo 'Certificate';
                                           elseif ($level <= 6) echo 'Diploma';
                                           else echo 'Bachelor';
                                       @endphp" readonly>
                                <small class="text-muted">NTA Level {{ $student->current_level ?? 1 }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Admission No: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $student->registration_number }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Registration No: <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $student->registration_number }}" readonly>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sponsorship:</label>
                                <select name="sponsorship_type" class="form-select">
                                    <option value="Private Institution/Company" {{ ($application->sponsorship_type ?? $student->sponsorship_type ?? '') == 'Private Institution/Company' ? 'selected' : '' }}>Private Institution/Company</option>
                                    <option value="Government" {{ ($application->sponsorship_type ?? $student->sponsorship_type ?? '') == 'Government' ? 'selected' : '' }}>Government</option>
                                    <option value="Self" {{ ($application->sponsorship_type ?? $student->sponsorship_type ?? '') == 'Self' ? 'selected' : '' }}>Self</option>
                                    <option value="Scholarship" {{ ($application->sponsorship_type ?? $student->sponsorship_type ?? '') == 'Scholarship' ? 'selected' : '' }}>Scholarship</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Manner of Entry:</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $application->entry_level ?? $application->entry_mode ?? 'Direct' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== PERSONAL INFORMATION ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-user me-2"></i> Personal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Surname: <span class=""></span>
                                </label>
                                <input type="text" name="last_name" class="form-control bg-light" 
                                       value="{{ old('last_name', $user->last_name) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Middle Name: <span class="text-danger"></span>
                                </label>
                                <input type="text" name="middle_name" class="form-control bg-light" 
                                       value="{{ old('middle_name', $user->middle_name) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    First Name: <span class="text-danger"></span>
                                </label>
                                <input type="text" name="first_name" class="form-control bg-light" 
                                       value="{{ old('first_name', $user->first_name) }}" readonly>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Gender: <span class="text-danger">*</span>
                                </label>
                                <select name="gender" class="form-select" required>
                                    <option value="male" {{ ($user->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ ($user->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ ($user->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Date of Birth: <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date_of_birth" class="form-control" 
                                       value="{{ old('date_of_birth', $user->date_of_birth) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Nationality: <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nationality" class="form-control" 
                                       value="{{ old('nationality', $application->nationality ?? 'Tanzanian') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Marital Status: <span class="text-danger">*</span>
                                </label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="Single" {{ ($application->marital_status ?? 'Single') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ ($application->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Divorced" {{ ($application->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="Widowed" {{ ($application->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    National ID (NIDA):
                                </label>
                                <input type="text" name="national_id" class="form-control" 
                                       value="{{ old('national_id', $user->national_id) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Phone: <span class="text-danger">*</span>
                                </label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="{{ old('phone', $user->phone) }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">
                                    Email: <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Region of Birth:</label>
                                <input type="text" name="region_of_birth" class="form-control" 
                                       value="{{ old('region_of_birth', $user->region_of_birth) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">District of Birth:</label>
                                <input type="text" name="district_of_birth" class="form-control" 
                                       value="{{ old('district_of_birth', $user->district_of_birth) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Permanent Address:</label>
                                <textarea name="permanent_address" class="form-control" rows="2" 
                                          placeholder="Your permanent home address">{{ old('permanent_address', $user->permanent_address) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Current Address:</label>
                                <textarea name="current_address" class="form-control" rows="2" 
                                          placeholder="Your current residential address">{{ old('current_address', $user->current_address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== NEXT OF KIN INFORMATION ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-users me-2"></i> Next of Kin Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name of Next of Kin:</label>
                                <input type="text" name="guardian_name" class="form-control" 
                                       value="{{ old('guardian_name', $student->guardian_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Next of Kin Occupation:</label>
                                <input type="text" name="guardian_occupation" class="form-control" 
                                       value="{{ old('guardian_occupation', $student->guardian_occupation) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Next of Kin Phone:</label>
                                <input type="tel" name="guardian_phone" class="form-control" 
                                       value="{{ old('guardian_phone', $student->guardian_phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Next of Kin Address:</label>
                                <textarea name="guardian_address" class="form-control" rows="2" 
                                          placeholder="Address of next of kin">{{ old('guardian_address', $student->guardian_address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== SPONSOR INFORMATION ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-briefcase me-2"></i> Sponsor Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name of Sponsor:</label>
                                <input type="text" name="sponsor_name" class="form-control" 
                                       value="{{ old('sponsor_name', $student->sponsor_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sponsor Occupation:</label>
                                <input type="text" name="sponsor_occupation" class="form-control" 
                                       value="{{ old('sponsor_occupation', $student->sponsor_occupation) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sponsor Phone:</label>
                                <input type="tel" name="sponsor_phone" class="form-control" 
                                       value="{{ old('sponsor_phone', $student->sponsor_phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sponsor Address:</label>
                                <textarea name="sponsor_address" class="form-control" rows="2" 
                                          placeholder="Address of sponsor">{{ old('sponsor_address', $student->sponsor_address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== ENTRY QUALIFICATIONS ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-award me-2"></i> Entry Qualifications Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Form IV School Name:</label>
                                <input type="text" name="secondary_school" class="form-control" 
                                       value="{{ old('secondary_school', $application->secondary_school ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Form IV NECTA No:</label>
                                <input type="text" name="necta_number" class="form-control" 
                                       value="{{ old('necta_number', $application->necta_number ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== BANK INFORMATION ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-credit-card me-2"></i> Bank Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Name of Bank:</label>
                                <input type="text" name="bank_name" class="form-control" 
                                       value="{{ old('bank_name', $student->bank_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Name of Branch:</label>
                                <input type="text" name="bank_branch" class="form-control" 
                                       value="{{ old('bank_branch', $student->bank_branch) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Account Number:</label>
                                <input type="text" name="account_number" class="form-control" 
                                       value="{{ old('account_number', $student->account_number) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== PROFILE PICTURE ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-image me-2"></i> Profile Picture
                        </h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                     class="img-fluid rounded-circle border shadow-sm"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                     style="width: 120px; height: 120px;">
                                    <i class="feather-user fa-3x text-white"></i>
                                </div>
                            @endif
                        </div>
                        <p class="text-muted mb-0">
                            <i class="feather-info me-1"></i> Profile picture is mandatory.
                        </p>
                        
                    </div>
                </div>

                <!-- ==================== ACTION BUTTONS ==================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5 me-3">
                            <i class="feather-save me-2"></i> Save Changes
                        </button>
                        <a href="{{ route('student.profile.index') }}" class="btn btn-secondary btn-lg px-5">
                            <i class="feather-x me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Upload Photo -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('student.profile.upload-photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="feather-camera me-2"></i> Upload Profile Picture
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div id="imagePreview" class="mb-3">
                            <i class="feather-image fa-4x text-muted"></i>
                        </div>
                    </div>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*" required 
                           onchange="previewImage(this)">
                    <small class="text-muted mt-2 d-block">
                        <i class="feather-info"></i> Accepted formats: JPG, PNG, GIF. Max size: 2MB
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #ddd;">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
<style>
    /* Modern Form Styles */
.card {
    border-radius: 12px;
    border: none;
}

.card-header {
    border-bottom: 2px solid #e9ecef;
    border-radius: 12px 12px 0 0 !important;
}

.form-label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    color: #495057;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.form-control.bg-light {
    background-color: #f8f9fa !important;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1rem;
}

.badge {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>
@endsection