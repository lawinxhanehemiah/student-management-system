@extends('layouts.admission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-user-plus me-2"></i> Register New Student
        </h1>
        <div>
            <a href="{{ route('admission.students.walkin') }}" class="btn btn-sm btn-success me-2">
                <i class="feather-user me-1"></i> Walk-in Registration
            </a>
            <a href="{{ route('admission.students.index') }}" class="btn btn-sm btn-secondary">
                <i class="feather-list me-1"></i> View All Students
            </a>
        </div>
    </div>

    <!-- Registration Type Tabs -->
    <ul class="nav nav-tabs mb-4" id="registrationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="from-application-tab" data-bs-toggle="tab" 
                    data-bs-target="#from-application" type="button" role="tab">
                <i class="feather-file-text me-1"></i> From Application
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="walkin-tab" data-bs-toggle="tab" 
                    data-bs-target="#walkin" type="button" role="tab">
                <i class="feather-user me-1"></i> Walk-in (No Application)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="registrationTabsContent">
        <!-- TAB 1: FROM APPLICATION -->
        <div class="tab-pane fade show active" id="from-application" role="tabpanel">
            <!-- Application Lookup Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="feather-search me-1"></i> Find Approved Application
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="feather-hash"></i>
                                </span>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="applicationSearch" 
                                       placeholder="Enter Application ID or Application Number...">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="feather-search me-1"></i> Search
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="feather-info me-1"></i>
                                You can register any applicant, but approved ones are recommended
                            </small>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="text-center my-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Fetching applicant details...</p>
                    </div>

                    <!-- Error Message -->
                    <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;"></div>

                    <!-- Warning Message -->
                    <div id="warningMessage" class="alert alert-warning mt-3" style="display: none;"></div>

                    <!-- Success/Info Message -->
                    <div id="infoMessage" class="alert alert-info mt-3" style="display: none;"></div>
                </div>
            </div>

            <!-- Registration Form (Initially Hidden) -->
            <div id="registrationForm" class="card shadow" style="display: none;">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="feather-user-check me-1"></i> Complete Student Registration
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admission.students.store') }}" id="studentRegistrationForm">
                        @csrf
                        <input type="hidden" name="registration_type" value="from_application">
                        <input type="hidden" name="application_id" id="application_id">

                        <!-- Applicant Preview Card -->
                        <div class="bg-light p-4 rounded mb-4 border-start border-success border-4">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="feather-check-circle me-1"></i> Applicant Details (Auto-filled)
                            </h6>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <small class="text-muted d-block">Full Name</small>
                                    <strong id="preview_name">-</strong>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <small class="text-muted d-block">Email</small>
                                    <strong id="preview_email">-</strong>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <small class="text-muted d-block">Phone</small>
                                    <strong id="preview_phone">-</strong>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <small class="text-muted d-block">Programme</small>
                                    <strong id="preview_programme">-</strong>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <small class="text-muted d-block">Application #</small>
                                    <strong id="preview_app_no">-</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form Fields -->
                        @include('admission.students._form_fields', [
                            'programmes' => $programmes,
                            
                            'academicYears' => $academicYears
                        ])

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                                <i class="feather-user-check me-2"></i> Register Student
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg px-4 ms-2">
                                <i class="feather-x me-1"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 2: WALK-IN REGISTRATION -->
        <div class="tab-pane fade" id="walkin" role="tabpanel">
            <div class="card shadow">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="feather-user me-1"></i> Walk-in Student Registration
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
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="optional">
                                <small class="text-muted">Optional, but must be unique if provided</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                <small class="text-muted">Must be unique</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Academic Information - Students Table -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="feather-book me-1"></i> Academic Information
                                </h6>
                            </div>

                            @include('admission.students._form_fields', [
                                'programmes' => $programmes,
                                
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
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedApplication = null;

    // Search button click
    $('#searchBtn').click(function() {
        const searchValue = $('#applicationSearch').val().trim();
        if (!searchValue) {
            alert('Please enter Application ID or Number');
            return;
        }
        searchApplication(searchValue);
    });

    // Enter key in search field
    $('#applicationSearch').keypress(function(e) {
        if (e.which === 13) {
            $('#searchBtn').click();
        }
    });

    function searchApplication(query) {
        // Show loading, hide other elements
        $('#loadingSpinner').show();
        $('#errorMessage').hide();
        $('#warningMessage').hide();
        $('#infoMessage').hide();
        $('#registrationForm').hide();

        $.ajax({
            url: '{{ route("admission.students.get-applicant") }}',
            type: 'GET',
            data: { application_id: query },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#loadingSpinner').hide();
                
                if (response.success) {
                    // Store application data
                    selectedApplication = response.application;
                    
                    // Update preview
                    updatePreview(response.application);
                    
                    // Fill form fields
                    $('#application_id').val(response.application.id);
                    $('#programme_id').val(response.application.programme_id);
                    
                    $('#intake').val(response.application.intake);
                    $('#study_mode').val(response.application.study_mode);
                    $('#academic_year_id').val(response.application.academic_year_id);
                    
                    // Auto-fill guardian if available
                    if (response.application.guardian_name) {
                        $('#guardian_name').val(response.application.guardian_name);
                    }
                    if (response.application.guardian_phone) {
                        $('#guardian_phone').val(response.application.guardian_phone);
                    }
                    
                    // Trigger course load if programme is selected
                    if ($('#programme_id').val()) {
                        $('#programme_id').trigger('change');
                    }
                    
                    // Show registration form
                    $('#registrationForm').slideDown();
                    
                    // Show warning if any
                    if (response.warning) {
                        $('#warningMessage').html(response.warning).show();
                    }
                    
                    // Show success message
                    showInfo('success', 'Applicant found! Complete the registration form below.');
                }
            },
            error: function(xhr) {
                $('#loadingSpinner').hide();
                
                let message = 'Error searching application';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                    
                    // Check if already registered
                    if (xhr.responseJSON.student) {
                        message += `<br>
                            <a href="{{ url('admission/students') }}/${xhr.responseJSON.student.id}" 
                               class="btn btn-sm btn-primary mt-2">
                                <i class="feather-eye me-1"></i> View Registered Student
                            </a>`;
                    }
                }
                
                $('#errorMessage').html(message).show();
                $('#registrationForm').hide();
            }
        });
    }

    function updatePreview(app) {
        $('#preview_name').text(app.first_name + ' ' + (app.middle_name || '') + ' ' + app.last_name);
        $('#preview_email').text(app.email || '-');
        $('#preview_phone').text(app.phone || '-');
        $('#preview_programme').text(app.programme_name || '-');
        $('#preview_app_no').text(app.application_number || '-');
    }

    function showInfo(type, message) {
        const className = type === 'success' ? 'alert-success' : 'alert-info';
        $('#infoMessage').removeClass('alert-success alert-info')
            .addClass(className)
            .html(message)
            .show();
    }

    // Form submission for application-based registration
    $('#studentRegistrationForm').submit(function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to register this student?')) {
            return;
        }
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#submitBtn').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2"></span> Registering...');
            },
            success: function(response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    window.location.href = '{{ route("admission.students.index") }}';
                }
            },
            error: function(xhr) {
                $('#submitBtn').prop('disabled', false)
                    .html('<i class="feather-user-check me-2"></i> Register Student');
                
                let errors = xhr.responseJSON?.message || 'Registration failed';
                alert('Error: ' + errors);
            }
        });
    });

    // Walk-in form submission (regular POST)
    $('#walkinForm').submit(function(e) {
        if (!confirm('Are you sure you want to register this walk-in student?')) {
            e.preventDefault();
        }
    });

    // Load courses when programme changes
    $('#programme_id').change(function() {
        var programmeId = $(this).val();
        if (programmeId) {
            $.ajax({
                url: '',
                type: 'GET',
                data: { programme_id: programmeId },
                success: function(data) {
                    $('#course_id').empty();
                    $('#course_id').append('<option value="">Select Course</option>');
                    $.each(data, function(key, value) {
                        $('#course_id').append('<option value="'+ value.id +'">'+ value.name +'</option>');
                    });
                    
                    // If we have an old value or selected application, set it
                    @if(old('course_id'))
                        $('#course_id').val('{{ old('course_id') }}');
                    @endif
                    
                    if (selectedApplication && selectedApplication.course_id) {
                        $('#course_id').val(selectedApplication.course_id);
                    }
                }
            });
        } else {
            $('#course_id').empty();
            $('#course_id').append('<option value="">Select Course</option>');
        }
    });

    // Trigger change if old value exists
    @if(old('programme_id'))
        $('#programme_id').val('{{ old('programme_id') }}').trigger('change');
    @endif
});
</script>
@endpush