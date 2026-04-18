@extends('layouts.app')

@section('title', 'Apply for Admission')

@section('content')
<div class="container-fluid py-2">
    <!-- Progress Bar - SIMPLIFIED for Mobile -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <!-- Mobile Progress -->
                    <div class="d-block d-md-none">
                        <div class="progress mb-1" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" id="progressBar" style="width: 0%"></div>
                        </div>
                        <div class="text-center mb-2">
                            <small><span id="mobileStepTitle">Step 1: Application Info</span></small>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="previousStep(currentStep)" id="mobilePrevBtn" disabled>
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary ms-1" onclick="saveStep(currentStep)" id="mobileNextBtn">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Desktop Progress -->
                    <div class="d-none d-md-block">
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" id="progressBarDesktop" style="width: 0%"></div>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap" style="gap: 5px;">
                            @for($i = 1; $i <= 7; $i++)
                            <button class="step-btn btn btn-sm {{ $i == 1 ? 'btn-primary' : 'btn-outline-secondary' }}" 
                                    data-step="{{ $i }}"
                                    style="min-width: 100px;">
                                <small>Step {{ $i }}</small>
                            </button>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Steps -->
    <div class="row">
        <!-- Main Form -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0" id="stepTitle">Step 1: Application Information</h5>
                </div>
                <div class="card-body p-2">
                    
                    <!-- Step 1: Application Meta - ULTRA SIMPLE FOR MOBILE -->
                    <form id="step1Form" class="step-form" style="display: block;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Entry Level *</label>
                            <select name="entry_level" class="form-select" required>
                                <option value="">Select Entry Level</option>
                                <option value="CSEE" {{ $application->entry_level == 'CSEE' ? 'selected' : '' }}>Form Four (CSEE)</option>
                                <option value="ACSEE" {{ $application->entry_level == 'ACSEE' ? 'selected' : '' }}>Form Six (ACSEE)</option>
                                <option value="DIPLOMA" {{ $application->entry_level == 'DIPLOMA' ? 'selected' : '' }}>Diploma</option>
                                <option value="DEGREE" {{ $application->entry_level == 'DEGREE' ? 'selected' : '' }}>Degree</option>
                            </select>
                            <div class="invalid-feedback small">Please select entry level</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Intake *</label>
                            <select name="intake" class="form-select" required>
                                <option value="">Select Intake</option>
                                <option value="March" {{ $application->intake == 'March' ? 'selected' : '' }}>March Intake</option>
                                <option value="September" {{ $application->intake == 'September' ? 'selected' : '' }}>September Intake</option>
                            </select>
                            <div class="invalid-feedback small">Please select intake</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Academic Year *</label>
                            @php
                                $academicYears = \DB::table('academic_years')->get();
                            @endphp
                            <select name="academic_year_id" class="form-select" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $application->academic_year_id == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback small">Please select academic year</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_free_application" value="1" 
                                       id="freeApplication" {{ $application->is_free_application ? 'checked' : '' }}>
                                <label class="form-check-label small" for="freeApplication">
                                    Apply for Fee Waiver
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="feeWaiverReason" style="display: {{ $application->is_free_application ? 'block' : 'none' }};">
                            <label class="form-label fw-bold small">Fee Waiver Reason</label>
                            <textarea name="fee_waiver_reason" class="form-control" rows="2">{{ $application->fee_waiver_reason }}</textarea>
                            <small class="text-muted">Please provide reason for requesting fee waiver</small>
                        </div>

                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" disabled>Previous</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(1)">Save & Continue</button>
                        </div>
                    </form>

                    <!-- Step 2: Personal Info - SIMPLE VERTICAL LAYOUT -->
                    <form id="step2Form" class="step-form" style="display: none;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">First Name *</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Lawi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Date of Birth *</label>
                            <input type="date" name="date_of_birth" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="Tanzanian">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Marital Status</label>
                            <select name="marital_status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>

                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(2)">Previous</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(2)">Save & Continue</button>
                        </div>
                    </form>

                    <!-- Step 3: Contact Info - SIMPLE VERTICAL -->
<form id="step3Form" class="step-form" style="display: none;">
    @csrf
    <input type="hidden" name="application_id" value="{{ $application->id }}">
    
    <div class="mb-3">
        <label class="form-label fw-bold small">Phone Number *</label>
        <input type="tel" name="phone" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label fw-bold small">Email Address</label>
        <input type="email" name="email" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold small">Region *</label>
        <select name="region" id="regionSelect" class="form-select" required>
            <option value="">Select Region</option>
            <!-- Regions will be populated by JavaScript -->
        </select>
        <div class="invalid-feedback">Please select a region</div>
    </div>
    
    <div class="mb-3">
        <label class="form-label fw-bold small">District *</label>
        <select name="district" id="districtSelect" class="form-select" required disabled>
            <option value="">First select a region</option>
        </select>
        <div class="invalid-feedback">Please select a district</div>
    </div>

    <!-- Desktop Only Buttons -->
    <div class="d-none d-md-flex justify-content-between mt-4">
        <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(3)">Previous</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(3)">Save & Continue</button>
    </div>
</form>

                    <!-- Step 4: Next of Kin - SIMPLE VERTICAL -->
                    <form id="step4Form" class="step-form" style="display: none;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <h6 class="border-bottom pb-2 mb-3">Guardian/Parent Information</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Guardian/Parent Name *</label>
                            <input type="text" name="guardian_name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Guardian Phone *</label>
                            <input type="tel" name="guardian_phone" class="form-control" required>
                            <div class="invalid-feedback small">Please enter guardian phone</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Relationship to Applicant</label>
                            <select name="relationship" class="form-select">
                                <option value="">Select Relationship</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Brother">Brother</option>
                                <option value="Sister">Sister</option>
                                <option value="Uncle">Uncle</option>
                                <option value="Aunt">Aunt</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(4)">Previous</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(4)">Save & Continue</button>
                        </div>
                    </form>

                    <!-- Step 5: Academics - SIMPLIFIED FOR MOBILE -->
                    <form id="step5Form" class="step-form" style="display: none;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <div class="alert alert-info mb-3 p-2">
                            <i class="fas fa-info-circle"></i> 
                            <small>Enter CSEE index number to fetch results from NECTA.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">CSEE Index Number *</label>
                            <div class="input-group">
                                <input type="text" 
                                       name="csee_index_number" 
                                       id="cseeIndexNumber" 
                                       class="form-control" 
                                       placeholder="S1234/0056/2020" 
                                       required>
                                <button type="button" 
                                        class="btn btn-primary" 
                                        onclick="fetchCSEEResults()" 
                                        id="fetchResultsBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback small">Please enter CSEE index number</div>
                            <small class="text-muted">Format: SXXXX/XXXX/YYYY</small>
                        </div>
                        
                        <!-- Loading Indicator -->
                        <div id="resultsLoading" class="text-center mb-3" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 small">Fetching results from NECTA...</p>
                        </div>
                        
                        <!-- Error Message -->
                        <div id="resultsError" class="alert alert-danger mb-3 p-2" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <span id="errorMessage" class="small"></span>
                        </div>
                        
                        <!-- Results Container -->
                        <div id="resultsContainer" style="display: none;">
                            <!-- Hidden fields -->
                            <input type="hidden" name="csee_school" id="hiddenSchoolName">
                            <input type="hidden" name="csee_division" id="hiddenDivision">
                            <input type="hidden" name="csee_points" id="hiddenPoints">
                            <input type="hidden" name="csee_year" id="hiddenYear">
                            <input type="hidden" name="student_name_from_necta" id="hiddenStudentName">
                            
                            <!-- Results Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-user-graduate"></i> Student Information</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row small">
                                        <div class="col-6">
                                            <strong>Full Name:</strong><br>
                                            <span id="resultStudentName">-</span>
                                        </div>
                                        <div class="col-6">
                                            <strong>School Name:</strong><br>
                                            <span id="resultSchoolName">-</span>
                                        </div>
                                    </div>
                                    <div class="row small mt-2">
                                        <div class="col-6">
                                            <strong>Exam Year:</strong><br>
                                            <span id="resultYear">-</span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Division:</strong><br>
                                            <span id="resultDivision">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Subjects -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-book"></i> Subject Grades</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="small">Subject</th>
                                                    <th class="small">Grade</th>
                                                    <th class="small">Points</th>
                                                </tr>
                                            </thead>
                                            <tbody id="subjectsTableBody">
                                                <!-- Subjects will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-success p-2">
                                <i class="fas fa-check-circle"></i> 
                                <small>Results fetched successfully.</small>
                            </div>
                        </div>
                        
                        <!-- Manual Input -->
                        <div id="manualInputContainer" class="mt-3" style="display: none;">
                            <div class="alert alert-warning p-2 mb-3">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <small>Unable to fetch results. Please enter manually.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">School Name *</label>
                                <input type="text" name="csee_school_manual" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Year *</label>
                                <select name="csee_year_manual" class="form-select">
                                    <option value="">Select Year</option>
                                    @for($year = date('Y'); $year >= 1980; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Division *</label>
                                <select name="csee_division_manual" class="form-select">
                                    <option value="">Select Division</option>
                                    <option value="I">Division I</option>
                                    <option value="II">Division II</option>
                                    <option value="III">Division III</option>
                                    <option value="IV">Division IV</option>
                                </select>
                            </div>
                            
                            <!-- Manual Subjects -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small">O-Level Subjects</label>
                                <div id="manualSubjectContainer">
                                    <!-- Subjects will be added here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addManualSubject()">
                                    <i class="fas fa-plus"></i> Add Subject
                                </button>
                            </div>
                        </div>
                        
                        <!-- School Address -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">School Address (Optional)</label>
                            <input type="text" name="csee_school_address" class="form-control" placeholder="Full school address">
                        </div>

                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(5)">Previous</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(5)" id="saveAcademicBtnDesktop">Save & Continue</button>
                        </div>
                    </form>

                    <!-- Step 6: Programmes - SIMPLE -->
                    <form id="step6Form" class="step-form" style="display: none;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <div class="alert alert-info mb-3 p-2">
                            <i class="fas fa-info-circle"></i> 
                            <small>Select preferred programmes in order of priority.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">First Choice Programme *</label>
                            <select name="first_choice_program_id" class="form-select" required>
                                <option value="">Select Programme</option>
                                @php
                                    $programmes = \DB::table('programmes')->where('is_active', true)->get();
                                @endphp
                                @foreach($programmes as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback small">Please select first choice programme</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Second Choice Programme</label>
                            <select name="second_choice_program_id" class="form-select">
                                <option value="">Select Programme</option>
                                @foreach($programmes as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Third Choice Programme</label>
                            <select name="third_choice_program_id" class="form-select">
                                <option value="">Select Programme</option>
                                @foreach($programmes as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                                  <div class="mb-3">
    <label class="form-label fw-bold small">How did you hear about us? (Optional)</label>
    <select name="information_source" class="form-select">
        <option value="">Select information source</option>
        <option value="Newspaper">Newspaper</option>
        <option value="Radio">Radio</option>
        <option value="TV">TV</option>
        <option value="Website">Website</option>
        <option value="Social Media">Social Media</option>
        <option value="Friend/Family">Friend/Family</option>
        <option value="School Visit">School Visit</option>
        <option value="Education Fair">Education Fair</option>
        <option value="Other">Other</option>
    </select>
</div>


                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(6)">Previous</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="saveStep(6)">Save & Continue</button>
                        </div>
                    </form>

                    <!-- Step 7: Declaration - SIMPLE -->
                    <form id="step7Form" class="step-form" style="display: none;">
                        @csrf
                        <input type="hidden" name="application_id" value="{{ $application->id }}">
                        
                        <div class="alert alert-warning mb-3 p-2">
                            <h6 class="mb-1"><i class="fas fa-exclamation-circle"></i> Important Declaration</h6>
                            <p class="small mb-0">Please read and confirm all statements:</p>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="confirm_information" value="1" id="confirmInfo" required>
                            <label class="form-check-label small" for="confirmInfo">
                                I confirm all information is true and accurate.
                            </label>
                            <div class="invalid-feedback small">You must confirm this</div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="accept_terms" value="1" id="acceptTerms" required>
                            <label class="form-check-label small" for="acceptTerms">
                                I accept the terms and conditions.
                            </label>
                            <div class="invalid-feedback small">You must accept terms</div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="confirm_documents" value="1" id="confirmDocs" required>
                            <label class="form-check-label small" for="confirmDocs">
                                I will provide original documents for verification.
                            </label>
                            <div class="invalid-feedback small">You must confirm</div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="allow_data_sharing" value="1" id="allowData" required>
                            <label class="form-check-label small" for="allowData">
                                I allow sharing information with authorities.
                            </label>
                            <div class="invalid-feedback small">You must allow sharing</div>
                        </div>

                        <!-- Desktop Only Buttons -->
                        <div class="d-none d-md-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="previousStep(7)">Previous</button>
                            <button type="button" class="btn btn-success btn-sm" onclick="submitApplication()">
                                <i class="fas fa-paper-plane"></i> Submit Application
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
let currentStep = 1;
const totalSteps = 7;
let manualSubjectCount = 0;

// Step titles for mobile and desktop
const stepTitles = {
    desktop: [
        'Step 1: Application Information',
        'Step 2: Personal Information',
        'Step 3: Contact Information',
        'Step 4: Next of Kin',
        'Step 5: Academic Information',
        'Step 6: Programme Selection',
        'Step 7: Declaration & Submission'
    ],
    mobile: [
        'Step 1: Application Info',
        'Step 2: Personal Info',
        'Step 3: Contact Info',
        'Step 4: Next of Kin',
        'Step 5: Academic Info',
        'Step 6: Programme Selection',
        'Step 7: Declaration'
    ]
};

// Function to show step
function showStep(step) {
    console.log('Showing step:', step);
    
    // Hide all forms
    document.querySelectorAll('.step-form').forEach(form => {
        form.style.display = 'none';
    });
    
    // Show selected step
    const currentForm = document.getElementById(`step${step}Form`);
    if (currentForm) {
        currentForm.style.display = 'block';
    }
    
    // Update progress bar
    const progress = ((step - 1) / (totalSteps - 1)) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('progressBarDesktop').style.width = `${progress}%`;
    
    // Update desktop step buttons
    document.querySelectorAll('.step-btn').forEach(btn => {
        const btnStep = parseInt(btn.dataset.step);
        if (btnStep === step) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary');
        } else if (btnStep < step) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-outline-primary');
        } else {
            btn.classList.remove('btn-primary', 'btn-outline-primary');
            btn.classList.add('btn-outline-secondary');
        }
    });
    
    // Update step titles
    document.getElementById('stepTitle').textContent = stepTitles.desktop[step - 1];
    document.getElementById('mobileStepTitle').textContent = stepTitles.mobile[step - 1];
    
    // Update mobile navigation buttons
    const mobilePrevBtn = document.getElementById('mobilePrevBtn');
    const mobileNextBtn = document.getElementById('mobileNextBtn');
    
    if (mobilePrevBtn) {
        if (step === 1) {
            mobilePrevBtn.disabled = true;
            mobilePrevBtn.classList.add('disabled');
        } else {
            mobilePrevBtn.disabled = false;
            mobilePrevBtn.classList.remove('disabled');
        }
        
        if (step === totalSteps) {
            mobileNextBtn.innerHTML = 'Submit <i class="fas fa-paper-plane"></i>';
            mobileNextBtn.classList.remove('btn-primary');
            mobileNextBtn.classList.add('btn-success');
        } else {
            mobileNextBtn.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
            mobileNextBtn.classList.remove('btn-success');
            mobileNextBtn.classList.add('btn-primary');
        }
    }
    
    currentStep = step;
    
    // Scroll to top when changing steps
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Function to save step
async function saveStep(step) {
    console.log('Saving step:', step);
    const form = document.getElementById(`step${step}Form`);
    
    // Special validation for step 5 (Academics)
    if (step === 5) {
        if (!validateStep5()) {
            return;
        }
    }
    
    // Validate form
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        
        // Find first invalid field and focus on it
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) {
            firstInvalid.focus();
        }
        
        showNotification('Please fill in all required fields correctly.', 'warning');
        return;
    }
    
    const formData = new FormData(form);
    
    // Define endpoints
    const endpoints = {
        1: '/application/save-step1',
        2: '/application/save-personal',
        3: '/application/save-contact',
        4: '/application/save-next-of-kin',
        5: '/application/save-academics',
        6: '/application/save-programs',
        7: '/application/submit'
    };
    
    // Show loading on button
    const saveBtn = getCurrentSaveButton(step);
    const originalText = saveBtn ? saveBtn.innerHTML : 'Save';
    
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        if (window.innerWidth >= 768) {
            saveBtn.innerHTML += ' Saving...';
        }
        saveBtn.disabled = true;
    }
    
    try {
        console.log('Sending data to:', endpoints[step]);
        const response = await fetch(endpoints[step], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        console.log('Response:', data);
        
        // Reset button
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
        
        if (!response.ok) {
            // Handle validation errors
            if (response.status === 422 && data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(`Validation failed:\n${errorMessages}`);
            }
            throw new Error(data.message || 'Failed to save');
        }
        
        if (data.success) {
            // Show success message
            showNotification('Information saved successfully!', 'success');
            
            // Update step completion status in UI
            updateStepStatus(step);
            
            // Move to next step if not last step
            if (step < totalSteps) {
                // Small delay for better UX
                setTimeout(() => {
                    showStep(step + 1);
                }, 500);
            } else if (step === 7 && data.submitted) {
                // If final submission, redirect
                showNotification('Application submitted successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '/application/dashboard';
                }, 1500);
            }
        } else {
            throw new Error(data.message || 'Save failed');
        }
        
    } catch (error) {
        console.error('Save error:', error);
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
        showNotification('Error: ' + error.message, 'error');
    }
}

// Validate Step 5 (Academics)
function validateStep5() {
    const indexNumber = document.getElementById('cseeIndexNumber').value;
    const resultsContainer = document.getElementById('resultsContainer');
    const manualContainer = document.getElementById('manualInputContainer');
    
    if (!indexNumber) {
        showNotification('Please enter CSEE index number', 'warning');
        document.getElementById('cseeIndexNumber').focus();
        return false;
    }
    
    if (resultsContainer.style.display === 'none' && 
        manualContainer.style.display === 'none') {
        showNotification('Please fetch results first by clicking the search button', 'warning');
        return false;
    }
    
    // If manual input, validate required fields
    if (manualContainer.style.display === 'block') {
        const schoolName = document.querySelector('input[name="csee_school_manual"]');
        const year = document.querySelector('select[name="csee_year_manual"]');
        const division = document.querySelector('select[name="csee_division_manual"]');
        
        if (!schoolName.value || !year.value || !division.value) {
            showNotification('Please fill in all required manual fields', 'warning');
            return false;
        }
    }
    
    return true;
}

// Get current save button based on screen size
function getCurrentSaveButton(step) {
    if (window.innerWidth < 768) {
        return document.getElementById('mobileNextBtn');
    } else {
        const form = document.getElementById(`step${step}Form`);
        return form.querySelector('button[onclick*="saveStep"]') || 
               form.querySelector('.btn-primary');
    }
}

// Update step status in UI
function updateStepStatus(step) {
    const statusBadge = document.getElementById(`step${step}Status`);
    if (statusBadge) {
        statusBadge.innerHTML = '<i class="fas fa-check"></i>';
        statusBadge.className = 'badge bg-success';
    }
}

// Function to go to previous step
function previousStep(step) {
    if (step > 1) {
        showStep(step - 1);
    }
}

// Function to submit application
function submitApplication() {
    if (confirm('Are you sure you want to submit your application?\n\nYou will not be able to edit it after submission.')) {
        saveStep(7);
    }
}

// NECTA API Functions - FIXED VERSION
// FIXED VERSION - Handles hidden elements
// 7. Fetch Results Function (with fixed displayCSEEResults call)
async function fetchCSEEResults() {
    console.log('🔍 Starting fetchCSEEResults...');
    
    // Ensure step 5 is visible
    ensureStep5Visible();
    
    // Get index number
    const indexInput = document.getElementById('cseeIndexNumber');
    if (!indexInput || !indexInput.value.trim()) {
        showNotification('Please enter CSEE index number', 'warning');
        return;
    }
    
    const indexNumber = indexInput.value.trim();
    console.log('Index:', indexNumber);
    
    // Get UI elements
    const elements = {
        btn: document.getElementById('fetchResultsBtn'),
        loading: document.getElementById('resultsLoading'),
        container: document.getElementById('resultsContainer'),
        error: document.getElementById('resultsError'),
        manual: document.getElementById('manualInputContainer')
    };
    
    // Show loading state
    if (elements.btn) {
        elements.btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        elements.btn.disabled = true;
    }
    
    if (elements.loading) elements.loading.style.display = 'block';
    if (elements.container) elements.container.style.display = 'none';
    if (elements.error) elements.error.style.display = 'none';
    if (elements.manual) elements.manual.style.display = 'none';
    
    try {
        // Call API - Updated to /test/necta
        console.log('📡 Calling API...');
        const response = await fetch(`/test/necta?index=${encodeURIComponent(indexNumber)}`);
        
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📦 API Response:', data);
        
        if (data.success && data.data) {
            // ✅ SUCCESS: Display the results
            if (typeof displayCSEEResults === 'function') {
                displayCSEEResults(data.data);
                
                // Update UI
                if (elements.loading) elements.loading.style.display = 'none';
                if (elements.container) elements.container.style.display = 'block';
                
                showNotification('✅ Results fetched successfully!', 'success');
            } else {
                throw new Error('displayCSEEResults function not found!');
            }
        } else {
            throw new Error(data.message || 'No results found');
        }
        
    } catch (error) {
        console.error('❌ Fetch error:', error);
        
        // Show error state
        if (elements.loading) elements.loading.style.display = 'none';
        if (elements.error) elements.error.style.display = 'block';
        
        // Set error message
        const errorMsg = document.getElementById('errorMessage');
        if (errorMsg) errorMsg.textContent = error.message;
        
        // Offer manual input
        setTimeout(() => {
            if (confirm('Unable to fetch results. Would you like to enter them manually?')) {
                if (elements.error) elements.error.style.display = 'none';
                if (elements.manual) elements.manual.style.display = 'block';
            }
        }, 500);
        
    } finally {
        // Reset button
        if (elements.btn) {
            elements.btn.innerHTML = '<i class="fas fa-search"></i>';
            elements.btn.disabled = false;
        }
    }
}

// NEW FUNCTION: Ensure step 5 elements are properly rendered
function ensureStep5Visible() {
    console.log('Ensuring step 5 is visible...');
    
    // Force the form to be visible temporarily
    const step5Form = document.getElementById('step5Form');
    if (step5Form) {
        // Save current state
        const wasHidden = step5Form.style.display === 'none';
        
        // If hidden, show it temporarily
        if (wasHidden) {
            console.log('Step 5 was hidden, making visible temporarily...');
            step5Form.style.display = 'block';
            
            // Force browser to render
            void step5Form.offsetHeight; // Trigger reflow
            
            // Hide it again after rendering
            setTimeout(() => {
                step5Form.style.display = 'none';
            }, 100);
        }
    }
    
    // Check which elements exist now
    const elementIds = [
        'cseeIndexNumber', 'fetchResultsBtn', 'resultsLoading',
        'resultsContainer', 'resultsError', 'manualInputContainer',
        'resultStudentName', 'resultSchoolName', 'resultYear',
        'resultDivision', 'subjectsTableBody'
    ];
    
    console.log('Step 5 elements check:');
    elementIds.forEach(id => {
        const el = document.getElementById(id);
        console.log(`  ${id}: ${el ? '✓ Found' : '✗ Missing'}`);
    });
}
// ==================== STEP 5 FUNCTIONS ====================

// 1. Display Results Function
function displayCSEEResults(results) {
    console.log('🎯 Displaying NECTA results...');
    
    try {
        // Update visible elements
        updateResultElements(results);
        
        // Update hidden fields
        updateHiddenFields(results);
        
        // Update subjects table
        updateSubjectsTable(results);
        
        console.log('✅ Results displayed successfully');
        
    } catch (error) {
        console.error('❌ Error displaying results:', error);
        showNotification('Error displaying results: ' + error.message, 'error');
    }
}

// 2. Update visible result elements
function updateResultElements(results) {
    const elements = {
        'resultStudentName': results.candidate_name,
        'resultSchoolName': results.school_name,
        'resultYear': results.exam_year,
        'resultDivision': results.division
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    });
}

// 3. Update hidden form fields
function updateHiddenFields(results) {
    const fields = {
        'hiddenSchoolName': results.school_name,
        'hiddenDivision': results.division,
        'hiddenPoints': results.points,
        'hiddenYear': results.exam_year,
        'hiddenStudentName': results.candidate_name
    };
    
    Object.entries(fields).forEach(([id, value]) => {
        const field = document.getElementById(id);
        if (field) {
            field.value = value || '';
        }
    });
}

// 4. Update subjects table
function updateSubjectsTable(results) {
    const tbody = document.getElementById('subjectsTableBody');
    if (!tbody) {
        console.warn('Subjects table body not found');
        return;
    }
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    if (!results.subjects || results.subjects.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    <i class="fas fa-exclamation-circle"></i> No subject data available
                </td>
            </tr>
        `;
        return;
    }
    
    // Clear old hidden subject inputs
    const form = document.getElementById('step5Form');
    if (form) {
        form.querySelectorAll('[name^="olevel_subjects"]').forEach(el => el.remove());
    }
    
    let totalPoints = 0;
    
    // Add subject rows
    results.subjects.forEach((subject, index) => {
        // Add to table
        const row = tbody.insertRow();
        const gradeClass = getGradeBadgeClass(subject.grade);
        
        row.innerHTML = `
            <td class="small">${subject.subject || 'N/A'}</td>
            <td class="small">
                <span class="badge ${gradeClass}">${subject.grade || 'N/A'}</span>
            </td>
            <td class="small">${subject.points || '0'}</td>
        `;
        
        totalPoints += parseInt(subject.points) || 0;
        
        // Add hidden inputs
        addSubjectHiddenInput(index, 'subject', subject.subject);
        addSubjectHiddenInput(index, 'grade', subject.grade);
        addSubjectHiddenInput(index, 'points', subject.points);
    });
    
    // Add total row
    const totalRow = tbody.insertRow();
    totalRow.className = 'table-active fw-bold';
    totalRow.innerHTML = `
        <td colspan="2" class="text-end"><strong>Total Points:</strong></td>
        <td><strong>${totalPoints}</strong></td>
    `;
}

// 5. Helper to add subject hidden inputs
function addSubjectHiddenInput(index, field, value) {
    const form = document.getElementById('step5Form');
    if (!form) return;
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `olevel_subjects[${index}][${field}]`;
    input.value = value || '';
    form.appendChild(input);
}

// 6. Grade badge styling
function getGradeBadgeClass(grade) {
    const gradeUpper = String(grade).toUpperCase();
    
    const classMap = {
        'A': 'bg-success',
        'B': 'bg-info', 
        'C': 'bg-primary',
        'D': 'bg-warning',
        'E': 'bg-warning text-dark',
        'F': 'bg-danger'
    };
    
    return classMap[gradeUpper] || 'bg-secondary';
}

// 7. Fetch Results Function (with fixed displayCSEEResults call)
async function fetchCSEEResults() {
    console.log('🔍 Starting fetchCSEEResults...');
    
    // Ensure step 5 is visible
    ensureStep5Visible();
    
    // Get index number
    const indexInput = document.getElementById('cseeIndexNumber');
    if (!indexInput || !indexInput.value.trim()) {
        showNotification('Please enter CSEE index number', 'warning');
        return;
    }
    
    const indexNumber = indexInput.value.trim();
    console.log('Index:', indexNumber);
    
    // Get UI elements
    const elements = {
        btn: document.getElementById('fetchResultsBtn'),
        loading: document.getElementById('resultsLoading'),
        container: document.getElementById('resultsContainer'),
        error: document.getElementById('resultsError'),
        manual: document.getElementById('manualInputContainer')
    };
    
    // Show loading state
    if (elements.btn) {
        elements.btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        elements.btn.disabled = true;
    }
    
    if (elements.loading) elements.loading.style.display = 'block';
    if (elements.container) elements.container.style.display = 'none';
    if (elements.error) elements.error.style.display = 'none';
    if (elements.manual) elements.manual.style.display = 'none';
    
    try {
        // Call API
        console.log('📡 Calling API...');
        const response = await fetch(`/test/necta?index=${encodeURIComponent(indexNumber)}`);

        
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📦 API Response:', data);
        
        if (data.success && data.data) {
            //  SUCCESS: Display the results
            if (typeof displayCSEEResults === 'function') {
                displayCSEEResults(data.data);
                
                // Update UI
                if (elements.loading) elements.loading.style.display = 'none';
                if (elements.container) elements.container.style.display = 'block';
                
                showNotification('✅ Results fetched successfully!', 'success');
            } else {
                throw new Error('displayCSEEResults function not found!');
            }
        } else {
            throw new Error(data.message || 'No results found');
        }
        
    } catch (error) {
        console.error('❌ Fetch error:', error);
        
        // Show error state
        if (elements.loading) elements.loading.style.display = 'none';
        if (elements.error) elements.error.style.display = 'block';
        
        // Set error message
        const errorMsg = document.getElementById('errorMessage');
        if (errorMsg) errorMsg.textContent = error.message;
        
        // Offer manual input
        setTimeout(() => {
            if (confirm('Unable to fetch results. Would you like to enter them manually?')) {
                if (elements.error) elements.error.style.display = 'none';
                if (elements.manual) elements.manual.style.display = 'block';
            }
        }, 500);
        
    } finally {
        // Reset button
        if (elements.btn) {
            elements.btn.innerHTML = '<i class="fas fa-search"></i>';
            elements.btn.disabled = false;
        }
    }
}

// 8. Ensure step 5 elements are ready
function ensureStep5Visible() {
    console.log('👁️ Ensuring step 5 visibility...');
    
    const form = document.getElementById('step5Form');
    if (form && form.style.display === 'none') {
        // Temporarily show for rendering
        form.style.display = 'block';
        void form.offsetHeight; // Force reflow
        form.style.display = 'none';
    }
}
// Helper function to add hidden inputs
function addHiddenSubjectInput(index, field, value) {
    const form = document.getElementById('step5Form');
    if (!form) return;
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `olevel_subjects[${index}][${field}]`;
    input.value = value;
    form.appendChild(input);
}

function getGradeBadgeClass(grade) {
    switch(String(grade).toUpperCase()) {
        case 'A': return 'bg-success';
        case 'B': return 'bg-info';
        case 'C': return 'bg-primary';
        case 'D': return 'bg-warning';
        case 'E': return 'bg-warning text-dark';
        case 'F': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function showManualInput() {
    document.getElementById('manualInputContainer').style.display = 'block';
    document.getElementById('resultsContainer').style.display = 'none';
    document.getElementById('resultsError').style.display = 'none';
    
    // Add initial manual subject
    addManualSubject();
}

function addManualSubject() {
    manualSubjectCount++;
    const container = document.getElementById('manualSubjectContainer');
    if (!container) return;
    
    const subjectDiv = document.createElement('div');
    subjectDiv.className = 'row mb-2 align-items-center';
    subjectDiv.id = `manual-subject-${manualSubjectCount}`;
    subjectDiv.innerHTML = `
        <div class="col-5">
            <input type="text" 
                   name="manual_subjects[${manualSubjectCount}][subject]" 
                   class="form-control form-control-sm" 
                   placeholder="Subject name" 
                   required>
        </div>
        <div class="col-4">
            <select name="manual_subjects[${manualSubjectCount}][grade]" 
                    class="form-control form-control-sm" 
                    required>
                <option value="">Select Grade</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="E">E</option>
                <option value="S">S</option>
                <option value="F">F</option>
            </select>
        </div>
        <div class="col-2">
            <input type="text" 
                   name="manual_subjects[${manualSubjectCount}][points]" 
                   class="form-control form-control-sm" 
                   placeholder="Points" 
                   readonly>
        </div>
        <div class="col-1">
            <button type="button" 
                    class="btn btn-danger btn-sm w-100" 
                    onclick="removeManualSubject(${manualSubjectCount})"
                    title="Remove subject">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(subjectDiv);
    
    // Add event listener to calculate points when grade changes
    const gradeSelect = subjectDiv.querySelector('select[name^="manual_subjects"]');
    const pointsInput = subjectDiv.querySelector('input[name$="[points]"]');
    
    if (gradeSelect && pointsInput) {
        gradeSelect.addEventListener('change', function() {
            const points = calculateGradePoints(this.value);
            pointsInput.value = points;
        });
    }
}

function removeManualSubject(id) {
    const element = document.getElementById(`manual-subject-${id}`);
    if (element) {
        element.remove();
    }
}

function calculateGradePoints(grade) {
    const pointsMap = {
        'A': 1, 'B': 2, 'C': 3, 'D': 4,
        'E': 5, 'S': 6, 'F': 7
    };
    return pointsMap[String(grade).toUpperCase()] || 7;
}

// Notification system
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    // Remove existing notifications
    document.querySelectorAll('.custom-notification').forEach(el => el.remove());
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `custom-notification alert ${alertClass} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                            type === 'error' ? 'fa-exclamation-circle' : 
                            type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'} 
                me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Application form initialized');
    
    // Add click handlers to step buttons
    document.querySelectorAll('.step-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.dataset.step);
            showStep(step);
        });
    });
    
    // Mobile navigation button handlers
    const mobilePrevBtn = document.getElementById('mobilePrevBtn');
    const mobileNextBtn = document.getElementById('mobileNextBtn');
    
    if (mobilePrevBtn) {
        mobilePrevBtn.addEventListener('click', function() {
            if (currentStep > 1) {
                previousStep(currentStep);
            }
        });
    }
    
    if (mobileNextBtn) {
        mobileNextBtn.addEventListener('click', function() {
            if (currentStep === totalSteps) {
                submitApplication();
            } else {
                saveStep(currentStep);
            }
        });
    }
    
    // Show first step
    showStep(1);
    
    // Toggle fee waiver reason
    const freeAppCheckbox = document.getElementById('freeApplication');
    if (freeAppCheckbox) {
        freeAppCheckbox.addEventListener('change', function() {
            document.getElementById('feeWaiverReason').style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Auto-fetch results when user stops typing (debounced)
    let typingTimer;
    const indexInput = document.getElementById('cseeIndexNumber');
    if (indexInput) {
        indexInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            if (this.value.length >= 10) {
                typingTimer = setTimeout(fetchCSEEResults, 800);
            }
        });
        
        // Also fetch on Enter key
        indexInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                fetchCSEEResults();
            }
        });
    }
    
    // Prevent form submission (we're using AJAX)
    document.querySelectorAll('.step-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    });
    
    // Add real-time validation
    document.querySelectorAll('input[required], select[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value && this.hasAttribute('required')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Auto-fill current year in index number if not specified
    const currentYear = new Date().getFullYear();
    if (indexInput && !indexInput.value.includes(currentYear.toString())) {
        indexInput.placeholder = `S1234/0056/${currentYear}`;
    }
    
    // Test if elements exist
    console.log('Test elements:');
    console.log('#resultStudentName:', document.getElementById('resultStudentName'));
    console.log('#resultsContainer:', document.getElementById('resultsContainer'));
    console.log('#subjectsTableBody:', document.getElementById('subjectsTableBody'));
});


// ==================== TANZANIA REGIONS & DISTRICTS ====================

// Tanzania Regions and Districts Data (Static) - COMPLETE LIST
const tanzaniaData = {
    regions: [
        { 
            id: 1, 
            name: 'Arusha', 
            districts: ['Arusha City', 'Arusha District', 'Karatu', 'Longido', 'Meru', 'Monduli', 'Ngorongoro'] 
        },
        { 
            id: 2, 
            name: 'Dar es Salaam', 
            districts: ['Ilala', 'Kinondoni', 'Temeke', 'Kigamboni', 'Ubungo'] 
        },
        { 
            id: 3, 
            name: 'Dodoma', 
            districts: ['Dodoma City', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'] 
        },
        { 
            id: 4, 
            name: 'Geita', 
            districts: ['Bukombe', 'Chato', 'Geita Town', 'Mbogwe', 'Nyang\'hwale'] 
        },
        { 
            id: 5, 
            name: 'Iringa', 
            districts: ['Iringa Urban', 'Iringa Rural', 'Kilolo', 'Mafinga', 'Mufindi'] 
        },
        { 
            id: 6, 
            name: 'Kagera', 
            districts: ['Biharamulo', 'Bukoba Rural', 'Bukoba Urban', 'Karagwe', 'Kyerwa', 'Missenyi', 'Muleba', 'Ngara'] 
        },
        { 
            id: 7, 
            name: 'Katavi', 
            districts: ['Mlele', 'Mpanda Town', 'Mpanda District'] 
        },
        { 
            id: 8, 
            name: 'Kigoma', 
            districts: ['Buhigwe', 'Kakonko', 'Kasulu Town', 'Kasulu District', 'Kibondo', 'Kigoma Town', 'Kigoma District', 'Uvinza'] 
        },
        { 
            id: 9, 
            name: 'Kilimanjaro', 
            districts: ['Hai', 'Moshi Rural', 'Moshi Urban', 'Mwanga', 'Rombo', 'Same', 'Siha'] 
        },
        { 
            id: 10, 
            name: 'Lindi', 
            districts: ['Kilwa', 'Lindi Rural', 'Lindi Urban', 'Liwale', 'Nachingwea', 'Ruangwa'] 
        },
        { 
            id: 11, 
            name: 'Manyara', 
            districts: ['Babati Town', 'Babati Rural', 'Hanang', 'Kiteto', 'Mbulu', 'Simanjiro'] 
        },
        { 
            id: 12, 
            name: 'Mara', 
            districts: ['Bunda', 'Butiama', 'Musoma Rural', 'Musoma Urban', 'Rorya', 'Serengeti', 'Tarime'] 
        },
        { 
            id: 13, 
            name: 'Mbeya', 
            districts: ['Chunya', 'Kyela', 'Mbarali', 'Mbeya City', 'Mbeya District', 'Rungwe'] 
        },
        { 
            id: 14, 
            name: 'Morogoro', 
            districts: ['Gairo', 'Kilombero', 'Kilosa', 'Morogoro Rural', 'Morogoro Urban', 'Mvomero', 'Ulanga'] 
        },
        { 
            id: 15, 
            name: 'Mtwara', 
            districts: ['Masasi', 'Mtwara Rural', 'Mtwara Urban', 'Nanyumbu', 'Newala', 'Tandahimba'] 
        },
        { 
            id: 16, 
            name: 'Mwanza', 
            districts: ['Ilemela', 'Kwimba', 'Magu', 'Misungwi', 'Nyamagana', 'Sengerema', 'Ukerewe'] 
        },
        { 
            id: 17, 
            name: 'Njombe', 
            districts: ['Ludewa', 'Makambako', 'Makete', 'Njombe Town', 'Njombe District', 'Wanging\'ombe'] 
        },
        { 
            id: 18, 
            name: 'Pemba North', 
            districts: ['Micheweni', 'Wete'] 
        },
        { 
            id: 19, 
            name: 'Pemba South', 
            districts: ['Chake Chake', 'Mkoani'] 
        },
        { 
            id: 20, 
            name: 'Pwani', 
            districts: ['Bagamoyo', 'Kibaha Town', 'Kibaha District', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji'] 
        },
        { 
            id: 21, 
            name: 'Rukwa', 
            districts: ['Kalambo', 'Nkasi', 'Sumbawanga Rural', 'Sumbawanga Urban'] 
        },
        { 
            id: 22, 
            name: 'Ruvuma', 
            districts: ['Mbinga', 'Songea Rural', 'Songea Urban', 'Tunduru', 'Namtumbo', 'Nyasa'] 
        },
        { 
            id: 23, 
            name: 'Shinyanga', 
            districts: ['Kahama Town', 'Kahama District', 'Kishapu', 'Shinyanga Rural', 'Shinyanga Urban'] 
        },
        { 
            id: 24, 
            name: 'Simiyu', 
            districts: ['Bariadi', 'Busega', 'Itilima', 'Maswa', 'Meatu'] 
        },
        { 
            id: 25, 
            name: 'Singida', 
            districts: ['Ikungi', 'Iramba', 'Manyoni', 'Mkalama', 'Singida Urban', 'Singida Rural'] 
        },
        { 
            id: 26, 
            name: 'Songwe', 
            districts: ['Ileje', 'Mbozi', 'Momba', 'Songwe District', 'Tunduma'] 
        },
        { 
            id: 27, 
            name: 'Tabora', 
            districts: ['Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Tabora Urban', 'Urambo', 'Uyui'] 
        },
        { 
            id: 28, 
            name: 'Tanga', 
            districts: ['Handeni', 'Kilindi', 'Korogwe Town', 'Korogwe District', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani', 'Tanga City'] 
        },
        { 
            id: 29, 
            name: 'Zanzibar North', 
            districts: ['Kaskazini A', 'Kaskazini B'] 
        },
        { 
            id: 30, 
            name: 'Zanzibar South', 
            districts: ['Kati', 'Kusini'] 
        },
        { 
            id: 31, 
            name: 'Zanzibar West', 
            districts: ['Magharibi', 'Mji Mkongwe'] 
        }
    ]
};

// Function to populate regions dropdown
function populateRegions() {
    const regionSelect = document.getElementById('regionSelect');
    if (!regionSelect) return;
    
    regionSelect.innerHTML = '<option value="">Select Region</option>';
    
    tanzaniaData.regions.forEach(region => {
        const option = document.createElement('option');
        option.value = region.id;
        option.textContent = region.name;
        regionSelect.appendChild(option);
    });
}

// Function to update districts based on selected region
function updateDistrictsStatic(regionId) {
    const districtSelect = document.getElementById('districtSelect');
    if (!districtSelect) return;
    
    // Clear current options
    districtSelect.innerHTML = '<option value="">Select District</option>';
    districtSelect.disabled = true;
    
    if (!regionId) return;
    
    // Find selected region
    const selectedRegion = tanzaniaData.regions.find(region => region.id == regionId);
    
    if (selectedRegion && selectedRegion.districts) {
        // Populate districts
        selectedRegion.districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
        
        districtSelect.disabled = false;
    }
}

// Function to initialize region/district when step 3 is shown
function initRegionDistrict() {
    const regionSelect = document.getElementById('regionSelect');
    const districtSelect = document.getElementById('districtSelect');
    
    if (!regionSelect || !districtSelect) return;
    
    // Populate regions if empty
    if (regionSelect.options.length <= 1) {
        populateRegions();
    }
    
    // Set up event listener
    regionSelect.addEventListener('change', function() {
        updateDistrictsStatic(this.value);
    });
    
    // If region already has value (from previous save), load its districts
    if (regionSelect.value) {
        updateDistrictsStatic(regionSelect.value);
    }
}

// ==================== INTEGRATE WITH EXISTING STEP SYSTEM ====================

// Modify the showStep function to initialize region/district when step 3 is shown
const originalShowStep = window.showStep;
window.showStep = function(step) {
    originalShowStep(step);
    
    // Initialize region/district system when step 3 is shown
    if (step === 3) {
        setTimeout(() => {
            initRegionDistrict();
        }, 100);
    }
};

// Also initialize on page load (in case step 3 is already visible)
document.addEventListener('DOMContentLoaded', function() {
    // If step 3 is currently visible, initialize
    if (currentStep === 3) {
        setTimeout(() => {
            initRegionDistrict();
        }, 500);
    }
});

// ==================== ENHANCEMENT: ADD SEARCH/ FILTER ====================

// Optional: Add search functionality to region dropdown
function addRegionSearch() {
    const regionSelect = document.getElementById('regionSelect');
    if (!regionSelect) return;
    
    // Convert select to searchable (optional enhancement)
    // You can use a library like Select2 or create custom search
}

// Optional: Add search functionality to district dropdown
function addDistrictSearch() {
    const districtSelect = document.getElementById('districtSelect');
    if (!districtSelect) return;
    
    // Convert select to searchable (optional enhancement)
}
</script>
@endpush

@push('styles')
<style>
/* Mobile-first responsive styles */
@media (max-width: 767.98px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card {
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-header {
        padding: 0.75rem 1rem;
    }
    
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        display: block;
    }
    
    .form-control, .form-select {
        font-size: 1rem;
        padding: 0.75rem;
        height: auto;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        min-height: 48px;
    }
    
    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .alert {
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
    }
    
    .small {
        font-size: 0.85rem;
    }
    
    .table th, .table td {
        padding: 0.5rem;
        font-size: 0.85rem;
    }
    
    .input-group .btn {
        min-height: auto;
    }
    
    /* Improve spacing */
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    
    /* Make checkboxes larger */
    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.2em;
    }
    
    .form-check-label {
        padding-left: 0.5rem;
        font-size: 0.9rem;
    }
    
    /* Better progress bar */
    .progress {
        height: 6px;
        border-radius: 3px;
        background-color: #e9ecef;
    }
    
    .progress-bar {
        background-color: #0d6efd;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
}

/* Desktop styles */
@media (min-width: 768px) {
    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .card-header {
        padding: 1rem 1.5rem;
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
    
    .form-label {
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .form-control, .form-select {
        font-size: 1rem;
        padding: 0.75rem;
        border-radius: 0.5rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 0.5rem;
    }
    
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
    }
}

/* General improvements */
.step-btn {
    transition: all 0.3s ease;
}

.step-form {
    transition: all 0.3s ease;
}

.invalid-feedback {
    display: block;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.was-validated .form-control:invalid,
.was-validated .form-select:invalid {
    border-color: #dc3545;
}

.was-validated .form-check-input:invalid {
    border-color: #dc3545;
}

/* Make tables more responsive */
.table-responsive {
    -webkit-overflow-scrolling: touch;
    overflow-x: auto;
}

/* Improve textarea */
textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* Better focus states */
*:focus {
    outline: none;
}

.form-control:focus, 
.form-select:focus,
.btn:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

/* Custom notification */
.custom-notification {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endpush