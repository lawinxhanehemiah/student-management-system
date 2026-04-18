{{-- resources/views/admission/applications/edit.blade.php --}}
@extends('layouts.admission')

@section('title', 'Editing Application: ' . ($application->application_number ?? 'New Application'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Editing Application: {{ $application->application_number ?? 'N/A' }}
                        <small class="text-muted">for {{ $application->applicant_name ?? 'Applicant' }}</small>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ ($application->status ?? 'draft') == 'draft' ? 'warning' : 'info' }} p-2">
                            Status: {{ ucfirst($application->status ?? 'Draft') }}
                        </span>
                        <a href="{{ route('admission.officer.applications.index') }}" class="btn btn-default btn-sm ml-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Hidden Fields -->
                    <input type="hidden" id="application_id" value="{{ $application->id }}">
                    <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                    
                    <!-- Progress Steps -->
                    <div class="steps mb-4">
                        <div class="step-progress"><div class="step-progress-bar" style="width: {{ (($currentStep ?? 1) - 1) / 6 * 100 }}%;"></div></div>
                        <div class="step-container">
                            <div class="step-item {{ ($currentStep ?? 1) >= 1 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 1 ? 'active' : '' }}" data-step="1">
                                <div class="step-icon"><span class="step-number">1</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 2 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 2 ? 'active' : '' }}" data-step="2">
                                <div class="step-icon"><span class="step-number">2</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Personal</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 3 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 3 ? 'active' : '' }}" data-step="3">
                                <div class="step-icon"><span class="step-number">3</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Contact</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 4 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 4 ? 'active' : '' }}" data-step="4">
                                <div class="step-icon"><span class="step-number">4</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Next of Kin</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 5 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 5 ? 'active' : '' }}" data-step="5">
                                <div class="step-icon"><span class="step-number">5</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Academics</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 6 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 6 ? 'active' : '' }}" data-step="6">
                                <div class="step-icon"><span class="step-number">6</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Programs</div>
                            </div>
                            <div class="step-item {{ ($currentStep ?? 1) >= 7 ? 'completed' : '' }} {{ ($currentStep ?? 1) == 7 ? 'active' : '' }}" data-step="7">
                                <div class="step-icon"><span class="step-number">7</span><i class="fas fa-check step-check"></i></div>
                                <div class="step-label">Submit</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 1: Basic Information -->
                    <div id="step-1" class="step-content" style="display: {{ ($currentStep ?? 1) == 1 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 1: Basic Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select class="form-select" id="academic_year_id">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ ($application->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Intake <span class="text-danger">*</span></label>
                                <select class="form-select" id="intake">
                                    <option value="March" {{ ($application->intake ?? '') == 'March' ? 'selected' : '' }}>March Intake</option>
                                    <option value="September" {{ ($application->intake ?? '') == 'September' ? 'selected' : '' }}>September Intake</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Entry Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="entry_level">
                                    <option value="CSEE" {{ ($application->entry_level ?? '') == 'CSEE' ? 'selected' : '' }}>CSEE (Form Four)</option>
                                    <option value="ACSEE" {{ ($application->entry_level ?? '') == 'ACSEE' ? 'selected' : '' }}>ACSEE (Form Six)</option>
                                    <option value="Diploma" {{ ($application->entry_level ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                    <option value="Degree" {{ ($application->entry_level ?? '') == 'Degree' ? 'selected' : '' }}>Degree</option>
                                    <option value="Mature" {{ ($application->entry_level ?? '') == 'Mature' ? 'selected' : '' }}>Mature Entry</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Study Mode</label>
                                <select class="form-select" id="study_mode">
                                    <option value="Full Time" {{ ($application->study_mode ?? '') == 'Full Time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="Part Time" {{ ($application->study_mode ?? '') == 'Part Time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="Distance" {{ ($application->study_mode ?? '') == 'Distance' ? 'selected' : '' }}>Distance Learning</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_free_application" value="1" {{ ($application->is_free_application ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label">Free Application (No fee required)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 2: Personal Information -->
                    <div id="step-2" class="step-content" style="display: {{ ($currentStep ?? 1) == 2 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 2: Personal Information</h4>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" value="{{ $personal->first_name ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" value="{{ $personal->middle_name ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" value="{{ $personal->last_name ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ ($personal->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ ($personal->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" value="{{ $personal->date_of_birth ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" id="nationality" value="{{ $personal->nationality ?? 'Tanzanian' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marital Status</label>
                                <select class="form-select" id="marital_status">
                                    <option value="">Select Status</option>
                                    <option value="single" {{ ($personal->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ ($personal->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="divorced" {{ ($personal->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="widowed" {{ ($personal->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 3: Contact Information -->
                    <div id="step-3" class="step-content" style="display: {{ ($currentStep ?? 1) == 3 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 3: Contact Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" value="{{ $contact->phone ?? '' }}" placeholder="2557XXXXXXXX">
                                <small class="text-muted">Format: 2557XXXXXXXX</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" value="{{ $contact->email ?? $application->applicant_email ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="region" value="{{ $contact->region ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">District <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="district" value="{{ $contact->district ?? '' }}">
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- STEP 4: Next of Kin -->
                    <div id="step-4" class="step-content" style="display: {{ ($currentStep ?? 1) == 4 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 4: Next of Kin</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guardian/Next of Kin Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="guardian_name" value="{{ $kin->guardian_name ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guardian Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="guardian_phone" value="{{ $kin->guardian_phone ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select" id="relationship">
                                    <option value="">Select Relationship</option>
                                    <option value="Father" {{ ($kin->relationship ?? '') == 'Father' ? 'selected' : '' }}>Father</option>
                                    <option value="Mother" {{ ($kin->relationship ?? '') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="Guardian" {{ ($kin->relationship ?? '') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                    <option value="Other" {{ ($kin->relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guardian Address</label>
                                <textarea class="form-control" id="guardian_address" rows="2">{{ $kin->guardian_address ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 5: Academic Information -->
                    <div id="step-5" class="step-content" style="display: {{ ($currentStep ?? 1) == 5 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 5: Academic Information</h4>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Enter CSEE Index Number and click "Fetch NECTA" to load results.
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">CSEE Index Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="csee_index_number" value="{{ $academic->csee_index_number ?? '' }}" placeholder="e.g., S0788/0061/2016">
                                    <button type="button" id="fetchNectaResults" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Fetch NECTA
                                    </button>
                                </div>
                                <div id="indexNumberStatus" class="small mt-1"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CSEE School Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="csee_school" value="{{ $academic->csee_school ?? '' }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">CSEE Year <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="csee_year" value="{{ $academic->csee_year ?? '' }}" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">CSEE Points <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="csee_points" value="{{ $academic->csee_points ?? '' }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CSEE Division <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="csee_division" value="{{ $academic->csee_division ?? '' }}" readonly>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Subjects & Grades</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr><th width="50">#</th><th>Subject Name</th><th width="150">Grade</th></tr>
                                </thead>
                                <tbody id="subjectsContainer">
                                    @if($subjects && $subjects->count() > 0)
                                        @foreach($subjects as $index => $subject)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><input type="text" class="form-control form-control-sm" value="{{ $subject->subject }}" readonly></td>
                                            <td><input type="text" class="form-control form-control-sm text-center" value="{{ $subject->grade }}" readonly style="width:100px;"></td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="3" class="text-center py-3"><i class="fas fa-info-circle me-1"></i> Click "Fetch NECTA" to load subjects</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <h5 class="mb-3">ACSEE (Form Six) - Optional</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Index Number</label>
                                <input type="text" class="form-control" id="acsee_index_number" value="{{ $academic->acsee_index_number ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">School Name</label>
                                <input type="text" class="form-control" id="acsee_school" value="{{ $academic->acsee_school ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Year</label>
                                <input type="text" class="form-control" id="acsee_year" value="{{ $academic->acsee_year ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 6: Program Selection -->
                    <div id="step-6" class="step-content" style="display: {{ ($currentStep ?? 1) == 6 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 6: Program Selection</h4>
                        
                        @php
                            $eligibleCount = $eligibleProgrammesList->count();
                            $totalProgrammes = $eligibleProgrammesList->count() + $nonEligibleProgrammesList->count();
                            $eligiblePercentage = $totalProgrammes > 0 ? round(($eligibleCount / $totalProgrammes) * 100) : 0;
                        @endphp
                        
                        @if($eligibleCount > 0)
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>{{ $eligibleCount }} programmes</strong> available based on your academic results.
                        </div>
                        @else
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please save academic information first</strong> to see eligible programmes.
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">First Choice Programme <span class="text-danger">*</span></label>
                                <select class="form-select" id="first_choice_program_id">
                                    <option value="">Select Programme</option>
                                    @foreach($eligibleProgrammesList as $program)
                                        <option value="{{ $program->id }}" {{ ($programChoice->first_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }} ({{ $program->code ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Second Choice Programme</label>
                                <select class="form-select" id="second_choice_program_id">
                                    <option value="">Select Programme</option>
                                    @foreach($eligibleProgrammesList as $program)
                                        <option value="{{ $program->id }}" {{ ($programChoice->second_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }} ({{ $program->code ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Third Choice Programme</label>
                                <select class="form-select" id="third_choice_program_id">
                                    <option value="">Select Programme</option>
                                    @foreach($eligibleProgrammesList as $program)
                                        <option value="{{ $program->id }}" {{ ($programChoice->third_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }} ({{ $program->code ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">How did you hear about us?</label>
                                <select class="form-select" id="information_source">
                                    <option value="">Select Source</option>
                                    <option value="Friend/Family" {{ ($programChoice->information_source ?? '') == 'Friend/Family' ? 'selected' : '' }}>Friend/Family</option>
                                    <option value="Social Media" {{ ($programChoice->information_source ?? '') == 'Social Media' ? 'selected' : '' }}>Social Media</option>
                                    <option value="Radio/TV" {{ ($programChoice->information_source ?? '') == 'Radio/TV' ? 'selected' : '' }}>Radio/TV</option>
                                    <option value="School Visit" {{ ($programChoice->information_source ?? '') == 'School Visit' ? 'selected' : '' }}>School Visit</option>
                                    <option value="Website" {{ ($programChoice->information_source ?? '') == 'Website' ? 'selected' : '' }}>Website</option>
                                    <option value="Other" {{ ($programChoice->information_source ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STEP 7: Review & Submit -->
                    <div id="step-7" class="step-content" style="display: {{ ($currentStep ?? 1) == 7 ? 'block' : 'none' }};">
                        <h4 class="mb-4">Step 7: Review & Declaration</h4>
                        
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Ready to submit!</strong> Please review all information before submitting.
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Declaration</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm_information" required>
                                    <label class="form-check-label">
                                        I confirm that all information provided is true and correct.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="accept_terms" required>
                                    <label class="form-check-label">
                                        I accept the terms and conditions of admission.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm_documents" required>
                                    <label class="form-check-label">
                                        I confirm that all required documents have been uploaded.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="allow_data_sharing">
                                    <label class="form-check-label">
                                        I allow my data to be shared for verification purposes.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="row mt-4">
                        <div class="col-6">
                            <button type="button" id="prevBtn" class="btn btn-secondary" style="display: {{ ($currentStep ?? 1) > 1 ? 'inline-block' : 'none' }};">
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" id="nextBtn" class="btn btn-primary" style="display: {{ ($currentStep ?? 1) < 7 ? 'inline-block' : 'none' }};">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="button" id="submitBtn" class="btn btn-success" style="display: {{ ($currentStep ?? 1) == 7 ? 'inline-block' : 'none' }};">
                                <i class="fas fa-paper-plane me-2"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for NECTA Results -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title"><i class="fas fa-clipboard-check me-2"></i> Confirm Academic Results</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Processing your results...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmModalBtn">Confirm & Continue</button>
            </div>
        </div>
    </div>
</div>

<style>
.step-progress { height: 4px; background-color: #e9ecef; border-radius: 2px; margin-bottom: 20px; position: relative; }
.step-progress-bar { position: absolute; height: 100%; background-color: #3498db; border-radius: 2px; transition: width 0.3s ease; }
.step-container { display: flex; justify-content: space-between; }
.step-item { text-align: center; flex: 1; cursor: pointer; }
.step-icon { width: 36px; height: 36px; border-radius: 50%; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; border: 2px solid #e9ecef; }
.step-number { font-weight: 600; font-size: 14px; }
.step-check { display: none; font-size: 14px; }
.step-label { font-size: 12px; color: #6c757d; }
.step-item.active .step-icon { background-color: #3498db; border-color: #3498db; color: white; }
.step-item.active .step-label { color: #3498db; font-weight: 600; }
.step-item.completed .step-icon { background-color: #28a745; border-color: #28a745; }
.step-item.completed .step-number { display: none; }
.step-item.completed .step-check { display: block; color: white; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let currentStep = {{ $currentStep ?? 1 }};
    let fetchedData = null;
    let pendingSave = false;
    
    function showStep(step) {
        $('.step-content').hide();
        $(`#step-${step}`).show();
        
        $('.step-item').removeClass('active');
        for(let i = 1; i <= 7; i++) {
            if(i < step) $(`.step-item[data-step="${i}"]`).addClass('completed');
            else $(`.step-item[data-step="${i}"]`).removeClass('completed');
        }
        $(`.step-item[data-step="${step}"]`).addClass('active');
        
        $('.step-progress-bar').css('width', ((step - 1) / 6 * 100) + '%');
        
        if(step === 1) $('#prevBtn').hide();
        else $('#prevBtn').show();
        
        if(step === 7) { $('#nextBtn').hide(); $('#submitBtn').show(); }
        else { $('#nextBtn').show(); $('#submitBtn').hide(); }
        
        currentStep = step;
    }
    
    function saveStep(step) {
        return new Promise((resolve, reject) => {
            let data = { application_id: $('#application_id').val(), _token: $('#csrf_token').val() };
            
            if(step === 1) {
                data.academic_year_id = $('#academic_year_id').val();
                data.intake = $('#intake').val();
                data.entry_level = $('#entry_level').val();
                data.study_mode = $('#study_mode').val();
                data.is_free_application = $('#is_free_application').is(':checked') ? 1 : 0;
            } else if(step === 2) {
                data.first_name = $('#first_name').val();
                data.middle_name = $('#middle_name').val();
                data.last_name = $('#last_name').val();
                data.gender = $('#gender').val();
                data.date_of_birth = $('#date_of_birth').val();
                data.nationality = $('#nationality').val();
                data.marital_status = $('#marital_status').val();
            } else if(step === 3) {
                data.phone = $('#phone').val();
                data.email = $('#email').val();
                data.region = $('#region').val();
                data.district = $('#district').val();
              
            } else if(step === 4) {
                data.guardian_name = $('#guardian_name').val();
                data.guardian_phone = $('#guardian_phone').val();
                data.relationship = $('#relationship').val();
                data.guardian_address = $('#guardian_address').val();
            } else if(step === 6) {
                data.first_choice_program_id = $('#first_choice_program_id').val();
                data.second_choice_program_id = $('#second_choice_program_id').val();
                data.third_choice_program_id = $('#third_choice_program_id').val();
                data.information_source = $('#information_source').val();
            }
            
            let urls = {1: "{{ route('admission.officer.applications.save-step') }}",
                        2: "{{ route('admission.officer.applications.save-step') }}",
                        3: "{{ route('admission.officer.applications.save-step') }}",
                        4: "{{ route('admission.officer.applications.save-step') }}",
                        6: "{{ route('admission.officer.applications.save-step') }}"};
            
            $.ajax({
                url: urls[step],
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('#csrf_token').val() },
                data: { ...data, step: step },
                success: res => { if(res.success) resolve(res); else reject(res.message); },
                error: () => reject('Failed to save')
            });
        });
    }
    
    $('#nextBtn').click(async function() {
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
        try {
            await saveStep(currentStep);
            showStep(currentStep + 1);
        } catch(e) { alert(e); }
        finally { btn.prop('disabled', false).html('Next <i class="fas fa-arrow-right ms-2"></i>'); }
    });
    
    $('#prevBtn').click(() => showStep(currentStep - 1));
    
    $('#fetchNectaResults').click(function() {
        let indexNumber = $('#csee_index_number').val().trim();
        if(!indexNumber) { alert('Please enter CSEE Index Number'); return; }
        
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Fetching...');
        
        $.ajax({
            url: "{{ route('admission.officer.applications.fetch-necta') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('#csrf_token').val() },
            data: { index_number: indexNumber },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Fetch NECTA');
                if(res.success && res.data) {
                    fetchedData = res.data;
                    $('#csee_year').val(fetchedData.year || '');
                    $('#csee_school').val(fetchedData.school_name || '');
                    $('#csee_division').val(fetchedData.division || '');
                    $('#csee_points').val(fetchedData.points || '');
                    
                    let subjectsHtml = '';
                    if(fetchedData.subjects && fetchedData.subjects.length) {
                        fetchedData.subjects.forEach((sub, idx) => {
                            subjectsHtml += `<tr><td class="text-center">${idx+1}</td>
                                <td><input type="text" class="form-control form-control-sm" value="${sub.name}" readonly></td>
                                <td><input type="text" class="form-control form-control-sm text-center" value="${sub.grade}" readonly style="width:100px;"></td></tr>`;
                        });
                    } else { subjectsHtml = '<tr><td colspan="3" class="text-center py-3">No subjects found</td></tr>'; }
                    $('#subjectsContainer').html(subjectsHtml);
                    
                    $('#confirmModal').modal('show');
                    $('#confirmModalBody').html(`
                        <div class="text-center"><i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6>Results Loaded!</h6><div class="text-start mt-3">
                        <p><strong>School:</strong> ${fetchedData.school_name || 'N/A'}</p>
                        <p><strong>Year:</strong> ${fetchedData.year || 'N/A'}</p>
                        <p><strong>Division:</strong> ${fetchedData.division || 'N/A'}</p>
                        <p><strong>Points:</strong> ${fetchedData.points || 'N/A'}</p>
                        <p><strong>Subjects:</strong> ${fetchedData.subjects ? fetchedData.subjects.length : 0}</p></div>
                        <hr><p class="text-muted">Confirm the information is correct before proceeding.</p></div>
                    `);
                    pendingSave = false;
                } else { alert(res.message || 'No results found'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Fetch NECTA'); alert('Failed to fetch results'); }
        });
    });
    
    $('#confirmModalBtn').click(function() {
        if(!fetchedData) { $('#confirmModal').modal('hide'); return; }
        
        let subjects = [];
        $('#subjectsContainer tr').each(function() {
            let name = $(this).find('td:eq(1) input').val();
            let grade = $(this).find('td:eq(2) input').val();
            if(name && grade) subjects.push({ name: name, grade: grade });
        });
        
        $('#confirmModalBody').html('<div class="text-center py-3"><div class="spinner-border text-primary"></div><p>Saving...</p></div>');
        
        $.ajax({
            url: "{{ route('admission.officer.applications.save-step') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('#csrf_token').val() },
            data: {
                step: 5,
                application_id: $('#application_id').val(),
                entry_level: $('#entry_level').val(),
                csee_index_number: $('#csee_index_number').val(),
                csee_school: $('#csee_school').val(),
                csee_year: $('#csee_year').val(),
                csee_division: $('#csee_division').val(),
                csee_points: $('#csee_points').val(),
                acsee_index_number: $('#acsee_index_number').val(),
                acsee_school: $('#acsee_school').val(),
                acsee_year: $('#acsee_year').val(),
                subjects: JSON.stringify(subjects)
            },
            success: function(res) {
                if(res.success) {
                    $('#confirmModal').modal('hide');
                    showStep(6);
                    location.reload();
                } else { alert(res.message); }
            },
            error: function() { alert('Failed to save'); }
        });
    });
    
    $('#submitBtn').click(function() {
        if(!$('#confirm_information').is(':checked')) { alert('Please confirm information is correct'); return; }
        if(!$('#accept_terms').is(':checked')) { alert('Please accept terms and conditions'); return; }
        if(!$('#confirm_documents').is(':checked')) { alert('Please confirm documents'); return; }
        
        if(!confirm('Submit application? No changes after submission.')) return;
        
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');
        
        $.ajax({
            url: "{{ route('admission.officer.applications.submit', $application->id) }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('#csrf_token').val() },
            data: {
                confirm_information: $('#confirm_information').is(':checked') ? 1 : 0,
                accept_terms: $('#accept_terms').is(':checked') ? 1 : 0,
                confirm_documents: $('#confirm_documents').is(':checked') ? 1 : 0,
                allow_data_sharing: $('#allow_data_sharing').is(':checked') ? 1 : 0
            },
            success: function(res) {
                if(res.success) {
                    alert('Application submitted successfully!');
                    window.location.href = "{{ route('admission.officer.applications.index') }}";
                } else { alert(res.message); btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Application'); }
            },
            error: function() { alert('Submission failed'); btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Application'); }
        });
    });
    
    showStep(currentStep);
});
</script>
@endsection