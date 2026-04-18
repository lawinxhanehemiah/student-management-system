{{-- resources/views/admission/applications/create.blade.php --}}
@extends('layouts.admission')

@section('title', 'Create New Application')

@section('content')
<style>
    .form-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border-left: 4px solid #667eea;
    }
    .form-section h5 {
        color: #333;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .search-result-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
        color: white;
    }
    .btn-search {
        background: #667eea;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .btn-search:hover {
        background: #5a67d8;
        transform: translateY(-2px);
    }
    .btn-clear {
        background: #e2e8f0;
        border: none;
        color: #4a5568;
        padding: 10px 25px;
        border-radius: 8px;
    }
    .btn-clear:hover {
        background: #cbd5e0;
    }
    .required-star {
        color: #e53e3e;
        margin-left: 3px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .info-badge {
        background: #e2e8f0;
        color: #4a5568;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
        margin-left: 10px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                Create New Application
                            </h3>
                            <small class="text-muted">Fill in the details to create a new application</small>
                        </div>
                        <div>
                            <a href="{{ route('admission.officer.applications.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admission.officer.applications.store') }}" method="POST" id="applicationForm">
                        @csrf
                        
                        <!-- Search Applicant Section -->
                        <div class="form-section">
                            <h5>
                                <i class="fas fa-search text-primary me-2"></i> 
                                Find Existing Applicant
                                <span class="info-badge">Optional</span>
                            </h5>
                            <p class="text-muted small mb-3">Search by email or phone number to auto-fill applicant details</p>
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" id="applicant_search" class="form-control" 
                                               placeholder="Enter email address or phone number...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="searchApplicantBtn" class="btn btn-search text-white w-100">
                                            <i class="fas fa-search me-2"></i> Search
                                        </button>
                                        <button type="button" id="clearSearchBtn" class="btn btn-clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search Result -->
                            <div id="searchResult" style="display: none;" class="mt-3">
                                <div class="search-result-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span id="searchResultText"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light" onclick="clearSearch()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden Fields -->
                        <input type="hidden" name="user_id" id="selected_user_id">
                        <input type="hidden" name="is_new_applicant" id="is_new_applicant" value="1">
                        
                        <!-- Applicant Details Section -->
                        <div class="form-section">
                            <h5>
                                <i class="fas fa-user-circle text-primary me-2"></i> 
                                Applicant Details
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        First Name <span class="required-star">*</span>
                                    </label>
                                    <input type="text" name="first_name" id="first_name" 
                                           class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Last Name <span class="required-star">*</span>
                                    </label>
                                    <input type="text" name="last_name" id="last_name" 
                                           class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Email Address <span class="required-star">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control" required>
                                    <small class="text-muted">This will be used for login</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control" placeholder="e.g., 2557XXXXXXXX">
                                    <small class="text-muted">Format: 2557XXXXXXXX</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="password_field">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" id="password" 
                                           class="form-control" placeholder="Leave blank for auto-generated">
                                    <small class="text-muted">Only for new applicants</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Application Details Section -->
                        <div class="form-section">
                            <h5>
                                <i class="fas fa-file-alt text-primary me-2"></i> 
                                Application Details
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Academic Year <span class="required-star">*</span>
                                    </label>
                                    <select name="academic_year_id" class="form-select" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                                {{ $year->name }}
                                                @if($year->is_active)
                                                    <span class="text-success">(Active)</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Intake <span class="required-star">*</span>
                                    </label>
                                    <select name="intake" class="form-select" required>
                                        <option value="March">March Intake</option>
                                        <option value="September">September Intake</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Entry Level <span class="required-star">*</span>
                                    </label>
                                    <select name="entry_level" class="form-select" required>
                                        <option value="CSEE">CSEE (Form Four)</option>
                                        <option value="ACSEE">ACSEE (Form Six)</option>
                                        <option value="Diploma">Diploma</option>
                                        <option value="Degree">Degree</option>
                                        <option value="Mature">Mature Entry</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Study Mode</label>
                                    <select name="study_mode" class="form-select">
                                        <option value="Full Time">Full Time</option>
                                        <option value="Part Time">Part Time</option>
                                        <option value="Distance">Distance Learning</option>
                                        <option value="Evening">Evening</option>
                                        <option value="Weekend">Weekend</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <button type="reset" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary px-5" id="submitBtn">
                                <i class="fas fa-save me-2"></i> Create Application
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // Search for existing applicant
    $('#searchApplicantBtn').click(function() {
        let searchValue = $('#applicant_search').val().trim();
        
        if (!searchValue) {
            showAlert('warning', 'Please enter email or phone number to search');
            $('#applicant_search').focus();
            return;
        }
        
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Searching...');
        
        $.ajax({
            url: '{{ route("admission.officer.applicants.search") }}',
            method: 'GET',
            data: { q: searchValue },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-search me-2"></i> Search');
                
                if (response.found && response.applicant) {
                    let applicant = response.applicant;
                    
                    // Auto-fill form with existing applicant data
                    $('#selected_user_id').val(applicant.id);
                    $('#is_new_applicant').val('0');
                    $('#first_name').val(applicant.first_name).prop('readonly', true).addClass('bg-light');
                    $('#last_name').val(applicant.last_name).prop('readonly', true).addClass('bg-light');
                    $('#email').val(applicant.email).prop('readonly', true).addClass('bg-light');
                    $('#phone').val(applicant.phone || '').prop('readonly', true).addClass('bg-light');
                    $('#gender').val(applicant.gender || '').prop('readonly', true).addClass('bg-light');
                    $('#password_field').hide();
                    
                    let message = `<strong>${applicant.first_name} ${applicant.last_name}</strong> found! (${applicant.email})`;
                    
                    if (response.has_application) {
                        message += `<br><span class="text-warning mt-2 d-block">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            ⚠️ Already has an application for ${response.existing_academic_year}. Creating another will create a duplicate.
                        </span>`;
                    }
                    
                    $('#searchResultText').html(message);
                    $('#searchResult').fadeIn();
                    
                    showAlert('success', 'Applicant found! Details auto-filled.');
                    
                } else {
                    // No applicant found - prepare for new applicant
                    $('#selected_user_id').val('');
                    $('#is_new_applicant').val('1');
                    $('#first_name').val('').prop('readonly', false).removeClass('bg-light');
                    $('#last_name').val('').prop('readonly', false).removeClass('bg-light');
                    $('#email').val(searchValue.includes('@') ? searchValue : '').prop('readonly', false).removeClass('bg-light');
                    $('#phone').val(!searchValue.includes('@') ? searchValue : '').prop('readonly', false).removeClass('bg-light');
                    $('#gender').val('').prop('readonly', false).removeClass('bg-light');
                    $('#password_field').show();
                    
                    $('#searchResultText').html(`No existing applicant found with "<strong>${searchValue}</strong>". Fill in the details below to create a new applicant.`);
                    $('#searchResult').fadeIn();
                    
                    showAlert('info', 'No existing applicant found. Please fill in the details.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-search me-2"></i> Search');
                console.error('Search error:', xhr);
                showAlert('danger', 'Search failed. Please try again.');
            }
        });
    });
    
    // Clear search
    window.clearSearch = function() {
        $('#applicant_search').val('');
        $('#selected_user_id').val('');
        $('#is_new_applicant').val('1');
        $('#first_name').val('').prop('readonly', false).removeClass('bg-light');
        $('#last_name').val('').prop('readonly', false).removeClass('bg-light');
        $('#email').val('').prop('readonly', false).removeClass('bg-light');
        $('#phone').val('').prop('readonly', false).removeClass('bg-light');
        $('#gender').val('').prop('readonly', false).removeClass('bg-light');
        $('#password_field').show();
        $('#searchResult').fadeOut();
    };
    
    $('#clearSearchBtn').click(function() {
        clearSearch();
        showAlert('info', 'Form cleared. You can now enter new applicant details.');
    });
    
    // Form validation before submit
    $('#applicationForm').on('submit', function(e) {
        let isNew = $('#is_new_applicant').val() === '1';
        let userId = $('#selected_user_id').val();
        
        // Validate applicant selection or details
        if (!isNew && !userId) {
            e.preventDefault();
            showAlert('danger', 'Please search and select an existing applicant, or clear search to create a new one.');
            return false;
        }
        
        // Validate required fields
        if (!$('#first_name').val() || !$('#last_name').val() || !$('#email').val()) {
            e.preventDefault();
            showAlert('danger', 'Please fill in all required fields (First Name, Last Name, Email)');
            return false;
        }
        
        // Validate email format
        let email = $('#email').val();
        let emailRegex = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showAlert('danger', 'Please enter a valid email address');
            return false;
        }
        
        // Validate phone format if provided
        let phone = $('#phone').val();
        if (phone && !/^255[0-9]{9}$/.test(phone)) {
            e.preventDefault();
            showAlert('danger', 'Phone number must start with 255 and have 12 digits (e.g., 255712345678)');
            return false;
        }
        
        // Validate academic fields
        if (!$('select[name="academic_year_id"]').val()) {
            e.preventDefault();
            showAlert('danger', 'Please select an academic year');
            return false;
        }
        
        if (!$('select[name="intake"]').val()) {
            e.preventDefault();
            showAlert('danger', 'Please select an intake');
            return false;
        }
        
        if (!$('select[name="entry_level"]').val()) {
            e.preventDefault();
            showAlert('danger', 'Please select an entry level');
            return false;
        }
        
        // Disable submit button to prevent double submission
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Creating...');
        
        return true;
    });
    
    // Show alert function
    function showAlert(type, message) {
        // Remove existing alerts
        $('.custom-alert').remove();
        
        let alertClass = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-info'));
        let icon = type === 'success' ? 'fa-check-circle' : (type === 'danger' ? 'fa-exclamation-triangle' : (type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle'));
        
        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show custom-alert position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('body').append(alertHtml);
        
        setTimeout(function() {
            $('.custom-alert').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Reset form button handler
    $('button[type="reset"]').click(function(e) {
        e.preventDefault();
        clearSearch();
        $('#applicationForm')[0].reset();
        $('#is_new_applicant').val('1');
        $('#password_field').show();
        showAlert('info', 'Form has been reset');
    });
    
    // Phone number formatting
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.startsWith('0')) {
            value = '255' + value.substring(1);
        }
        if (value && !value.startsWith('255') && value.length > 0) {
            value = '255' + value;
        }
        $(this).val(value);
    });
});
</script>
@endsection