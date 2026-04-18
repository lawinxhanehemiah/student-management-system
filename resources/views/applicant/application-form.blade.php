@extends('layouts.app')

@section('title', 'Create New Application')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-2 text-dark">Create New Application</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('applicant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Create Application</li>
                    </ol>
                </nav>
            </div>
            <div class="badge bg-info text-white fs-6 p-3">
                <i class="fas fa-file-alt me-2"></i>
                Application: <strong>{{ $application->application_number ?? 'N/A' }}</strong>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <!-- Progress Steps -->
                    <div class="steps mb-5">
                        <div class="step-progress"><div class="step-progress-bar" style="width: 0%;"></div></div>
                        <div class="step-container">
                            @php $stepLabels = [1 => 'Basic Info', 2 => 'Personal', 3 => 'Contact', 4 => 'Next of Kin', 5 => 'Academics', 6 => 'Programs', 7 => 'Review']; @endphp
                            @foreach($stepLabels as $num => $label)
                                <div class="step-item" data-step="{{ $num }}">
                                    <div class="step-icon"><span class="step-number">{{ $num }}</span><i class="fas fa-check step-check"></i></div>
                                    <div class="step-label">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="applicationForm">
                        <input type="hidden" id="application_id" value="{{ $application->id }}">
                        <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                        <!-- STEP 1: Basic Information -->
                        <div class="step-content" id="step-1">
                            <h4 class="mb-4">Step 1: Basic Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select class="form-select" id="academic_year_id" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ ($application->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intake <span class="text-danger">*</span></label>
                                    <select class="form-select" id="intake" required>
                                        <option value="">Select Intake</option>
                                        <option value="March" {{ ($application->intake ?? '') == 'March' ? 'selected' : '' }}>March</option>
                                        <option value="September" {{ ($application->intake ?? '') == 'September' ? 'selected' : '' }}>September</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Entry Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="entry_level" required>
                                        <option value="">Select Entry Level</option>
                                        <option value="CSEE" {{ ($application->entry_level ?? '') == 'CSEE' ? 'selected' : '' }}>CSEE (Form 4)</option>
                                        <option value="ACSEE" {{ ($application->entry_level ?? '') == 'ACSEE' ? 'selected' : '' }}>ACSEE (Form 6)</option>
                                        <option value="Diploma" {{ ($application->entry_level ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                        <option value="Degree" {{ ($application->entry_level ?? '') == 'Degree' ? 'selected' : '' }}>Degree</option>
                                        <option value="Mature" {{ ($application->entry_level ?? '') == 'Mature' ? 'selected' : '' }}>Mature Entry</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Study Mode</label>
                                    <select class="form-select" id="study_mode">
                                        <option value="">Select Mode</option>
                                        <option value="Full-time" {{ ($application->study_mode ?? '') == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                        <option value="Part-time" {{ ($application->study_mode ?? '') == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                        <option value="Evening" {{ ($application->study_mode ?? '') == 'Evening' ? 'selected' : '' }}>Evening</option>
                                        <option value="Weekend" {{ ($application->study_mode ?? '') == 'Weekend' ? 'selected' : '' }}>Weekend</option>
                                        <option value="Online" {{ ($application->study_mode ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_free_application" value="1" {{ ($application->is_free_application ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label">Apply for Fee Waiver (If eligible)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Personal Information -->
                        <div class="step-content" id="step-2" style="display: none;">
                            <h4 class="mb-4">Step 2: Personal Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="first_name" value="{{ $personal->first_name ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Middle Name</label><input type="text" class="form-control" id="middle_name" value="{{ $personal->middle_name ?? '' }}"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="last_name" value="{{ $personal->last_name ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" id="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ ($personal->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ ($personal->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3"><label class="form-label">Date of Birth <span class="text-danger">*</span></label><input type="date" class="form-control" id="date_of_birth" value="{{ $personal->date_of_birth ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nationality</label><input type="text" class="form-control" id="nationality" value="{{ $personal->nationality ?? 'Tanzanian' }}"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Marital Status</label>
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
                        <div class="step-content" id="step-3" style="display: none;">
                            <h4 class="mb-4">Step 3: Contact Information</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Phone Number <span class="text-danger">*</span></label><input type="tel" class="form-control" id="phone" value="{{ $contact->phone ?? '' }}" required><small class="text-muted">Format: 2557XXXXXXXX</small></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Email Address <span class="text-danger">*</span></label><input type="email" class="form-control" id="email" value="{{ $contact->email ?? Auth::user()->email }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Region <span class="text-danger">*</span></label><input type="text" class="form-control" id="region" value="{{ $contact->region ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">District <span class="text-danger">*</span></label><input type="text" class="form-control" id="district" value="{{ $contact->district ?? '' }}" required></div>
                            </div>
                        </div>

                        <!-- STEP 4: Next of Kin -->
                        <div class="step-content" id="step-4" style="display: none;">
                            <h4 class="mb-4">Step 4: Next of Kin</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Guardian Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="guardian_name" value="{{ $kin->guardian_name ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <select class="form-select" id="relationship" required>
                                        <option value="">Select Relationship</option>
                                        <option value="Father" {{ ($kin->relationship ?? '') == 'Father' ? 'selected' : '' }}>Father</option>
                                        <option value="Mother" {{ ($kin->relationship ?? '') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                        <option value="Brother" {{ ($kin->relationship ?? '') == 'Brother' ? 'selected' : '' }}>Brother</option>
                                        <option value="Sister" {{ ($kin->relationship ?? '') == 'Sister' ? 'selected' : '' }}>Sister</option>
                                        <option value="Guardian" {{ ($kin->relationship ?? '') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="Other" {{ ($kin->relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3"><label class="form-label">Guardian Phone <span class="text-danger">*</span></label><input type="tel" class="form-control" id="guardian_phone" value="{{ $kin->guardian_phone ?? '' }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Guardian Address</label><textarea class="form-control" id="guardian_address" rows="2">{{ $kin->guardian_address ?? '' }}</textarea></div>
                            </div>
                        </div>

                        <!-- STEP 5: Academic Information -->
                        <div class="step-content" id="step-5" style="display: none;">
                            <h4 class="mb-4">Step 5: Academic Information</h4>
                            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i><strong>Important:</strong> Enter your CSEE Index Number and click "Fetch NECTA" to load your results.</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CSEE Index Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="csee_index_number" value="{{ $academic->csee_index_number ?? '' }}" placeholder="e.g., S0788/0061/2016">
                                        <button type="button" id="fetchNectaResults" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> Fetch NECTA</button>
                                    </div>
                                    <div class="form-text">Format: S0788/0061/2016 <span id="indexNumberStatus" class="ms-2 badge"></span></div>
                                </div>
                                <div class="col-md-6 mb-3"><label class="form-label">School Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="csee_school" value="{{ $academic->csee_school ?? '' }}" readonly></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Year <span class="text-danger">*</span></label><input type="text" class="form-control" id="csee_year" value="{{ $academic->csee_year ?? '' }}" readonly></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Division <span class="text-danger">*</span></label><input type="text" class="form-control" id="csee_division" value="{{ $academic->csee_division ?? '' }}" readonly></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Points <span class="text-danger">*</span></label><input type="text" class="form-control" id="csee_points" value="{{ $academic->csee_points ?? '' }}" readonly></div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <h5 class="mb-3">Subjects & Grades</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="subjectsTable">
                                            <thead><tr><th width="50">#</th><th>Subject Name</th><th width="20%">Grade</th></tr></thead>
                                            <tbody id="subjectsContainer"><tr><td colspan="3" class="text-center py-3"><i class="fas fa-info-circle me-1"></i> Click "Fetch NECTA" to load your subjects</td></tr></tbody>
                                        </table>
                                    </div>
                                    <div class="text-end"><small class="text-muted"><span id="subjectCount">0</span> subjects loaded</small></div>
                                </div>
                            </div>
                            <h5 class="mb-3">ACSEE (Form Six) - Optional</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3"><label class="form-label">Index Number</label><input type="text" class="form-control" id="acsee_index_number" value="{{ $academic->acsee_index_number ?? '' }}"></div>
                                <div class="col-md-4 mb-3"><label class="form-label">School Name</label><input type="text" class="form-control" id="acsee_school" value="{{ $academic->acsee_school ?? '' }}"></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Year</label><input type="text" class="form-control" id="acsee_year" value="{{ $academic->acsee_year ?? '' }}"></div>
                            </div>
                        </div>

                        <!-- STEP 6: Program Selection -->
<div class="step-content" id="step-6" style="display: none;">
    @php
        \Log::info('Step 6 View Data', [
            'eligible_count' => $eligibleProgrammesList->count(),
            'eligible_ids' => $eligibleProgrammesList->pluck('id')->toArray(),
            'non_eligible_count' => $nonEligibleProgrammesList->count()
        ]);
    @endphp
    
    @php
        // Get eligible programme IDs from database
        $eligibleIds = DB::table('application_eligible_programmes')->where('application_id', $application->id)->pluck('programme_id')->toArray();
        $eligibleProgrammesList = DB::table('programmes')->whereIn('id', $eligibleIds)->get();
        
        // Get all programmes for non-eligible list
        $allProgrammeIds = DB::table('programmes')->where('is_active', 1)->pluck('id')->toArray();
        $nonEligibleIds = array_diff($allProgrammeIds, $eligibleIds);
        $nonEligibleProgrammesList = DB::table('programmes')->whereIn('id', $nonEligibleIds)->get();
        
        $totalProgrammes = $eligibleProgrammesList->count() + $nonEligibleProgrammesList->count();
        $eligibleCount = $eligibleProgrammesList->count();
        $eligiblePercentage = $totalProgrammes > 0 ? round(($eligibleCount / $totalProgrammes) * 100) : 0;
    @endphp
    
    <!-- Overall Eligibility Progress Bar -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white py-2">
            <i class="fas fa-chart-line me-2"></i> Your Eligibility Summary
        </div>
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Overall Eligibility Score</small>
                        <small class="text-muted">{{ $eligiblePercentage }}% ({{ $eligibleCount }}/{{ $totalProgrammes }} Programmes)</small>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $eligiblePercentage }}%; font-size: 14px; font-weight: bold; line-height: 30px;" 
                             aria-valuenow="{{ $eligiblePercentage }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ $eligiblePercentage }}% Eligible
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-success fs-6 p-2 me-1">
                        <i class="fas fa-check-circle"></i> {{ $eligibleCount }} Eligible
                    </span>
                    <span class="badge bg-danger fs-6 p-2">
                        <i class="fas fa-times-circle"></i> {{ $nonEligibleProgrammesList->count() }} Not Eligible
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> Only programmes you are eligible for will be selectable. See details below for each programme.
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">First Choice <span class="text-danger">*</span></label>
            <select class="form-select" id="first_choice_program_id" required>
                <option value="">Select Program</option>
                @foreach($eligibleProgrammesList as $program)
                    <option value="{{ $program->id }}" {{ ($programChoice->first_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->code }}) 
                        @if($loop->first && $eligibleCount > 0) ⭐ Best Match @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Second Choice</label>
            <select class="form-select" id="second_choice_program_id">
                <option value="">Select Program</option>
                @foreach($eligibleProgrammesList as $program)
                    <option value="{{ $program->id }}" {{ ($programChoice->second_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Third Choice</label>
            <select class="form-select" id="third_choice_program_id">
                <option value="">Select Program</option>
                @foreach($eligibleProgrammesList as $program)
                    <option value="{{ $program->id }}" {{ ($programChoice->third_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">How did you hear about us?</label>
            <select class="form-select" id="information_source">
                <option value="">Select Source</option>
                <option value="Friend/Family">Friend/Family</option>
                <option value="Social Media">Social Media</option>
                <option value="Radio/TV">Radio/TV</option>
                <option value="School Visit">School Visit</option>
                <option value="Website">Website</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>
    
    <!-- Eligible Programmes Section (100% Progress) -->
    <div class="card border-success mt-3">
        <div class="card-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i> 
            Programmes You Are Eligible For ({{ $eligibleProgrammesList->count() }}) 
            <span class="badge bg-light text-success ms-2">100% Match</span>
        </div>
        <div class="card-body">
            @if($eligibleProgrammesList->count() > 0)
                <div class="row">
                    @foreach($eligibleProgrammesList as $program)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 text-success">
                                                <i class="fas fa-graduation-cap me-1"></i>
                                                {{ $program->name }}
                                            </h6>
                                            <small class="text-muted">Code: {{ $program->code }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="progress" style="width: 80px; height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%; font-size: 10px;">100%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning mb-0">No programmes match your results. Please contact admission office.</div>
            @endif
        </div>
    </div>
    
    <!-- Non-Eligible Programmes Section (0% - 20% Progress) -->
    @if($nonEligibleProgrammesList->count() > 0)
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger text-white" data-bs-toggle="collapse" data-bs-target="#nonEligibleCollapse" style="cursor: pointer;">
                <i class="fas fa-times-circle me-2"></i> 
                Programmes You Are NOT Eligible For ({{ $nonEligibleProgrammesList->count() }}) 
                <i class="fas fa-chevron-down float-end"></i>
            </div>
            <div class="collapse" id="nonEligibleCollapse">
                <div class="card-body">
                    <div class="row">
                        @foreach($nonEligibleProgrammesList as $program)
                            @php
                                // Get rule for this programme to show requirements
                                $rule = DB::table('eligibility_rules')->where('programme_id', $program->id)->first();
                                $requirements = [];
                                if ($rule) {
                                    if ($rule->min_csee_division) $requirements[] = "Division {$rule->min_csee_division} or better";
                                    if ($rule->min_csee_points) $requirements[] = "Points ≤ {$rule->min_csee_points}";
                                    $coreSubjects = json_decode($rule->core_subjects ?? '[]', true);
                                    if (!empty($coreSubjects)) $requirements[] = "Core: " . implode(', ', $coreSubjects);
                                }
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-danger">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 text-danger">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    {{ $program->name }}
                                                </h6>
                                                <small class="text-muted">Code: {{ $program->code }}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="progress" style="width: 80px; height: 20px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 20%; font-size: 10px;">20%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Requirements not met:</strong>
                                            </small>
                                            <ul class="small mb-0 mt-1 text-muted">
                                                @foreach($requirements as $req)
                                                    <li><i class="fas fa-times text-danger me-1"></i> {{ $req }}</li>
                                                @endforeach
                                                <li class="text-muted mt-1">
                                                    <i class="fas fa-info-circle me-1"></i> 
                                                    Contact admission office for more information
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

                        <!-- STEP 7: Review & Submit -->
                        <div class="step-content" id="step-7" style="display: none;">
                            <h4 class="mb-4">Step 7: Review & Declaration</h4>
                            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><strong>Great job!</strong> You've completed all sections.</div>
                            <div class="row mb-4">
                                <div class="col-md-12"><h6>Application Summary</h6><div id="reviewSummary" class="p-3 border rounded bg-light"><div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading review summary...</p></div></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3"><div class="form-check declaration-item"><input class="form-check-input" type="checkbox" id="confirm_information" required><label class="form-check-label">I confirm that all information provided is true and accurate</label></div></div>
                                <div class="col-md-12 mb-3"><div class="form-check declaration-item"><input class="form-check-input" type="checkbox" id="accept_terms" required><label class="form-check-label">I accept the terms and conditions of admission</label></div></div>
                                <div class="col-md-12 mb-3"><div class="form-check declaration-item"><input class="form-check-input" type="checkbox" id="confirm_documents" required><label class="form-check-label">I confirm that I will submit all required documents</label></div></div>
                                <div class="col-md-12 mb-4"><div class="form-check declaration-item"><input class="form-check-input" type="checkbox" id="allow_data_sharing" required><label class="form-check-label">I allow the college to use my data for academic purposes</label></div></div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <button type="button" id="prevBtn" class="btn btn-outline-secondary" style="display: none;"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                    <div>
                                        <button type="button" id="cancelDraftBtn" class="btn btn-outline-danger"><i class="fas fa-trash me-2"></i> Cancel Draft</button>
                                        <button type="button" id="nextBtn" class="btn btn-primary ms-2">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                        <button type="button" id="submitBtn" class="btn btn-success ms-2" style="display: none;"><i class="fas fa-check me-2"></i> Submit Application</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2"><h6 class="modal-title"><i class="fas fa-clipboard-check me-2"></i> Confirm Academic Results</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-3" id="confirmModalBody"><div class="text-center py-3"><div class="spinner-border text-primary"></div><p class="mt-2">Processing your results...</p></div></div>
            <div class="modal-footer py-2"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fas fa-edit me-1"></i> Edit</button><button type="button" class="btn btn-success btn-sm" id="confirmModalBtn"><i class="fas fa-check me-1"></i> Confirm & Continue</button></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentStep = {{ $currentStep }};
    const totalSteps = 7;
    const csrfToken = $('#csrf_token').val();
    let pendingSave = false;
    let fetchedData = null;

    function showAlert(type, msg) {
        $('.alert-dismissible.position-fixed').remove();
        $('body').append(`<div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top:20px;right:20px;z-index:9999;min-width:300px;max-width:500px;">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        setTimeout(() => $('.alert-dismissible.position-fixed').alert('close'), 5000);
    }

    function formatIndexNumber(input) {
        let value = $(input).val().replace(/[^A-Za-z0-9]/g, '');
        let formatted = '';
        if (value.length > 0) {
            let firstChar = value.charAt(0).toUpperCase();
            formatted = (firstChar === 'S' || firstChar === 'P') ? firstChar : 'S';
            value = value.substring(1);
            if (value.length > 0) {
                formatted += value.substring(0, 4);
                if (value.length > 4) formatted += '/' + value.substring(4, 8);
                if (value.length > 8) formatted += '/' + value.substring(8, 12);
            }
        }
        $(input).val(formatted);
        let regex = /^(S|P)\d{4}\/\d{4}\/\d{4}$/;
        let statusBadge = $('#indexNumberStatus');
        if (!formatted) { statusBadge.removeClass('bg-success bg-danger').text('').hide(); return false; }
        if (regex.test(formatted)) { statusBadge.removeClass('bg-danger').addClass('bg-success').text('✓ Valid').show(); return true; }
        else { statusBadge.removeClass('bg-success').addClass('bg-danger').text('✗ Invalid').show(); return false; }
    }

    $('#fetchNectaResults').click(function() {
        let indexNumber = $('#csee_index_number').val().trim();
        if (!indexNumber) { showAlert('warning', 'Please enter CSEE Index Number first.'); $('#csee_index_number').focus(); return; }
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Fetching...');
        $.ajax({
            url: "{{ route('applicant.application.necta.lookup') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: { index_number: indexNumber },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Fetch NECTA');
                if (res.success && res.data) {
                    fetchedData = res.data;
                    if (fetchedData.year) $('#csee_year').val(fetchedData.year);
                    if (fetchedData.school_name) $('#csee_school').val(fetchedData.school_name);
                    if (fetchedData.division) $('#csee_division').val(fetchedData.division);
                    if (fetchedData.points) $('#csee_points').val(fetchedData.points);
                    $('#subjectsContainer').empty();
                    if (fetchedData.subjects && fetchedData.subjects.length > 0) {
                        fetchedData.subjects.forEach((sub, idx) => {
                            $('#subjectsContainer').append(`<tr><td class="text-center">${idx+1}</td><td><input type="text" class="form-control form-control-sm" value="${sub.name}" readonly></td><td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="${sub.grade}" readonly style="width:100px;"></td></tr>`);
                        });
                        $('#subjectCount').text(fetchedData.subjects.length);
                    } else { $('#subjectsContainer').html('<tr><td colspan="3" class="text-center py-3">No subjects found</td></tr>'); $('#subjectCount').text('0'); }
                    $('#confirmModal').modal('show');
                    $('#confirmModalBody').html(`<div class="text-center"><i class="fas fa-check-circle text-success fa-3x mb-3"></i><h6>Results Loaded!</h6><div class="text-start mt-3"><p><strong>Name:</strong> ${fetchedData.full_name || fetchedData.first_name + ' ' + fetchedData.last_name}</p><p><strong>School:</strong> ${fetchedData.school_name || 'N/A'}</p><p><strong>Year:</strong> ${fetchedData.year || 'N/A'}</p><p><strong>Division:</strong> ${fetchedData.division || 'N/A'}</p><p><strong>Points:</strong> ${fetchedData.points || 'N/A'}</p><p><strong>Subjects:</strong> ${fetchedData.subjects ? fetchedData.subjects.length : 0}</p></div><hr><p class="text-muted">Confirm the information is correct before proceeding.</p></div>`);
                    pendingSave = false;
                } else { showAlert('warning', res.message || 'No results found.'); }
            },
            error: function() { btn.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Fetch NECTA'); showAlert('danger', 'Failed to fetch results.'); }
        });
    });

    $('#confirmModalBtn').click(function() {
        if (!fetchedData) { $('#confirmModal').modal('hide'); return; }
        if (pendingSave) { 
            $('#confirmModal').modal('hide'); 
            // Reload page to show updated eligible programmes
            window.location.reload();
            return; 
        }
        $('#confirmModalBody').html(`<div class="text-center py-3"><div class="spinner-border text-primary"></div><p class="mt-2">Saving and checking eligibility...</p></div>`);
        let subjects = [];
        $('#subjectsContainer tr').each(function() {
            let name = $(this).find('td:eq(1) input').val();
            let grade = $(this).find('td:eq(2) input').val();
            if (name && grade) subjects.push({ name: name, grade: grade });
        });
        $.ajax({
            url: "{{ route('applicant.application.save.academics') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: {
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
                subjects: JSON.stringify(subjects),
                _token: csrfToken
            },
            success: function(res) {
                if (res.success) {
                    let html = `<div class="text-center"><i class="fas fa-${res.has_eligible_programmes ? 'check-circle text-success' : 'exclamation-triangle text-warning'} fa-3x mb-3"></i><h6>${res.message}</h6>`;
                    if (res.eligible_programmes && res.eligible_programmes.length > 0) {
                        html += `<div class="mt-3 text-start"><small>Eligible (${res.eligible_count}):</small><ul class="small mt-1">`;
                        res.eligible_programmes.slice(0, 5).forEach(p => { html += `<li><strong>${p.name}</strong> (${p.code})</li>`; });
                        if (res.eligible_count > 5) html += `<li class="text-muted">... and ${res.eligible_count - 5} more</li>`;
                        html += `</ul></div><button class="btn btn-success btn-sm mt-2" id="continueToProgramsBtn">Continue to Program Selection</button>`;
                    } else { html += `<div class="alert alert-warning mt-3 small">No programmes match your results. Contact admission office.</div>`; }
                    $('#confirmModalBody').html(html);
                    pendingSave = true;
                    $('#continueToProgramsBtn').click(function() { 
                        $('#confirmModal').modal('hide'); 
                        // Reload page to show updated eligible programmes
                        window.location.reload();
                    });
                } else { 
                    $('#confirmModal').modal('hide'); 
                    showAlert('danger', res.message || 'Failed to save'); 
                }
            },
            error: function() { 
                $('#confirmModal').modal('hide'); 
                showAlert('danger', 'Failed to save academic information'); 
            }
        });
    });

    function showStep(step) {
        $('.step-content').hide();
        $(`#step-${step}`).show();
        $('.step-item').removeClass('active completed');
        for (let i = 1; i < step; i++) $(`.step-item[data-step="${i}"]`).addClass('completed');
        $(`.step-item[data-step="${step}"]`).addClass('active');
        $('.step-progress-bar').css('width', ((step - 1) / (totalSteps - 1) * 100) + '%');
        if (step === 1) { $('#prevBtn').hide(); $('#nextBtn').show(); $('#submitBtn').hide(); }
        else if (step === totalSteps) { $('#prevBtn').show(); $('#nextBtn').hide(); $('#submitBtn').show(); }
        else { $('#prevBtn').show(); $('#nextBtn').show(); $('#submitBtn').hide(); }
        if (step === 7) loadReviewSummary();
    }

    function validateStep(step) {
        if (step === 7) {
            let valid = true;
            $('#step-7 input[type="checkbox"][required]').each(function() { if (!$(this).is(':checked')) { $(this).closest('.declaration-item').addClass('border-danger'); valid = false; } else { $(this).closest('.declaration-item').removeClass('border-danger'); } });
            if (!valid) showAlert('warning', 'Please check all declarations');
            return valid;
        }
        if (step === 6) { if (!$('#first_choice_program_id').val()) { showAlert('danger', 'Select first choice program'); return false; } return true; }
        let valid = true;
        $(`#step-${step} [required]`).each(function() { if (!$(this).val()) { $(this).addClass('is-invalid'); valid = false; } else { $(this).removeClass('is-invalid'); } });
        if (step === 3) { let phone = $('#phone').val(); if (phone && !/^2557\d{8}$/.test(phone)) { $('#phone').addClass('is-invalid'); showAlert('warning', 'Phone must start with 2557 + 8 digits'); valid = false; } }
        if (!valid) showAlert('warning', 'Please fill all required fields');
        return valid;
    }

    function saveStep(step) {
        return new Promise((resolve, reject) => {
            let data = { application_id: $('#application_id').val(), _token: csrfToken };
            if (step === 1) {
                data.academic_year_id = $('#academic_year_id').val();
                data.intake = $('#intake').val();
                data.entry_level = $('#entry_level').val();
                data.study_mode = $('#study_mode').val();
                data.is_free_application = $('#is_free_application').is(':checked') ? 1 : 0;
            } else if (step === 2) {
                data.first_name = $('#first_name').val();
                data.middle_name = $('#middle_name').val();
                data.last_name = $('#last_name').val();
                data.gender = $('#gender').val();
                data.date_of_birth = $('#date_of_birth').val();
                data.nationality = $('#nationality').val();
                data.marital_status = $('#marital_status').val();
            } else if (step === 3) {
                data.phone = $('#phone').val();
                data.email = $('#email').val();
                data.region = $('#region').val();
                data.district = $('#district').val();
            } else if (step === 4) {
                data.guardian_name = $('#guardian_name').val();
                data.guardian_phone = $('#guardian_phone').val();
                data.relationship = $('#relationship').val();
                data.guardian_address = $('#guardian_address').val();
            } else if (step === 6) {
                data.first_choice_program_id = $('#first_choice_program_id').val();
                data.second_choice_program_id = $('#second_choice_program_id').val();
                data.third_choice_program_id = $('#third_choice_program_id').val();
                data.information_source = $('#information_source').val();
            }
            let urls = {1:"{{ route('applicant.application.save.step1') }}",2:"{{ route('applicant.application.save.personal') }}",3:"{{ route('applicant.application.save.contact') }}",4:"{{ route('applicant.application.save.next-of-kin') }}",6:"{{ route('applicant.application.save.programs') }}"};
            $.ajax({ url: urls[step], method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, data: data, success: res => { if (res.success) resolve(res); else reject(res.message); }, error: () => reject('Failed to save') });
        });
    }

    function loadReviewSummary() { $.ajax({ url: `/applicant/application/${$('#application_id').val()}/review`, success: d => $('#reviewSummary').html(d), error: () => $('#reviewSummary').html('<div class="alert alert-danger">Failed to load</div>') }); }

    $('#nextBtn').click(async function() {
        if (currentStep === 5) {
            let subjectCount = $('#subjectsContainer tr').length;
            if (subjectCount < 7 && $('#subjectsContainer tr td').length > 0) { showAlert('warning', `Need at least 7 subjects. Loaded: ${subjectCount}`); return; }
            let stepCompleted = {{ $application->step_academic_completed ?? 0 }};
            if (stepCompleted === 1) { currentStep++; showStep(currentStep); showAlert('success', 'Moving to program selection!'); }
            else { $('#fetchNectaResults').click(); }
        } else if (validateStep(currentStep)) {
            let nextBtn = $('#nextBtn');
            nextBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
            try { await saveStep(currentStep); currentStep++; showStep(currentStep); showAlert('success', 'Saved!'); } catch(e) { showAlert('danger', e); }
            finally { nextBtn.prop('disabled', false).html('Next <i class="fas fa-arrow-right ms-2"></i>'); }
        }
    });

    $('#prevBtn').click(function() { currentStep--; showStep(currentStep); });
    $('#cancelDraftBtn').click(function() { if (confirm('Cancel this draft? This cannot be undone.')) window.location.href = `/applicant/application/draft/${$('#application_id').val()}/cancel`; });
    $('#submitBtn').click(function() {
        if (!validateStep(7)) return;
        if (!confirm('Submit application? No changes after submission.')) return;
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');
        $.ajax({
            url: "{{ route('applicant.application.submit') }}",
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                application_id: $('#application_id').val(),
                confirm_information: $('#confirm_information').is(':checked') ? 1 : 0,
                accept_terms: $('#accept_terms').is(':checked') ? 1 : 0,
                confirm_documents: $('#confirm_documents').is(':checked') ? 1 : 0,
                allow_data_sharing: $('#allow_data_sharing').is(':checked') ? 1 : 0,
                _token: csrfToken
            },
            success: res => {
                if (res.success) { 
                    showAlert('success', 'Application submitted successfully!'); 
                    setTimeout(() => {
                        window.location.href = "{{ route('applicant.dashboard') }}";
                    }, 2000);
                } else { 
                    btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i> Submit Application'); 
                    showAlert('danger', res.message); 
                }
            },
            error: function(xhr) { 
                btn.prop('disabled', false).html('<i class="fas fa-check me-2"></i> Submit Application'); 
                let message = xhr.responseJSON?.message || 'Submission failed. Please try again.';
                showAlert('danger', message); 
            }
        });
    });

    $(document).on('change', '#step-7 input[type="checkbox"]', function() { if ($(this).is(':checked')) $(this).closest('.declaration-item').removeClass('border-danger'); });
    showStep(currentStep);
    if (!$('#date_of_birth').val()) { let d = new Date(); d.setFullYear(d.getFullYear() - 18); $('#date_of_birth').val(d.toISOString().split('T')[0]); }
});
</script>

<style>
    /* Progress bar styling */
.progress {
    background-color: #e9ecef;
    border-radius: 50px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.5s ease;
    font-weight: bold;
}

/* Card hover effects */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Collapsible header */
[data-bs-toggle="collapse"] {
    cursor: pointer;
    user-select: none;
}

[data-bs-toggle="collapse"] i.fa-chevron-down {
    transition: transform 0.3s ease;
}

[data-bs-toggle="collapse"][aria-expanded="true"] i.fa-chevron-down {
    transform: rotate(180deg);
}

/* Badge styling */
.badge {
    font-weight: 500;
}
.step-progress { height: 4px; background-color: #e9ecef; border-radius: 2px; margin-bottom: 20px; position: relative; }
.step-progress-bar { position: absolute; height: 100%; background-color: #3498db; border-radius: 2px; transition: width 0.3s ease; }
.step-container { display: flex; justify-content: space-between; }
.step-item { text-align: center; flex: 1; }
.step-icon { width: 36px; height: 36px; border-radius: 50%; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; border: 2px solid #e9ecef; }
.step-number { font-weight: 600; font-size: 14px; }
.step-check { display: none; font-size: 14px; }
.step-label { font-size: 12px; color: #6c757d; }
.step-item.active .step-icon { background-color: #3498db; border-color: #3498db; color: white; }
.step-item.active .step-label { color: #3498db; font-weight: 600; }
.step-item.completed .step-icon { background-color: #2ecc71; border-color: #2ecc71; }
.step-item.completed .step-number { display: none; }
.step-item.completed .step-check { display: block; color: white; }
.declaration-item { padding: 10px; border-radius: 5px; margin-bottom: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; }
.declaration-item.border-danger { border-color: #dc3545; background-color: rgba(220,53,69,0.05); }
.alert.position-fixed { animation: slideIn 0.3s ease; z-index: 9999; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@media (max-width: 768px) { .step-item { flex: 0 0 33.33%; margin-bottom: 15px; } .step-label { font-size: 10px; } }
</style>
@endpush
@endsection