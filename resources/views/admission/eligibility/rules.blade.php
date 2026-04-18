@extends('layouts.admission')

@section('title', 'Eligibility Rules')

@section('content')
<div class="container-fluid px-2 px-md-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="feather-shield me-1"></i> Eligibility Rules Engine
                        </h6>
                        <small class="text-muted">Configure programme-specific requirements including Health/Non-Health categories</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addHealthTemplateModal">
                            <i class="feather-plus me-1"></i> Health Template
                        </button>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addNonHealthTemplateModal">
                            <i class="feather-plus me-1"></i> Non-Health Template
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                            <i class="feather-plus me-1"></i> Custom Rule
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Info Alert -->
                    <div class="alert alert-info m-3 mb-0 p-2 small">
                        <i class="feather-info me-1"></i> 
                        <strong>Eligibility Rules</strong> define the minimum requirements for applicants to qualify for each programme.
                        <hr class="my-1">
                        <strong>Health Programmes:</strong> Clinical Medicine, Pharmacy, Environmental Health, Nursing, etc. (Requires: Physics, Chemistry, Biology)<br>
                        <strong>Non-Health Programmes:</strong> Business, Education, Humanities, etc. (Any 4 passes with D or above)
                    </div>
                    
                    <!-- Rules Table -->
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="min-width: 1300px;">
                            <thead class="table-light sticky-top">
                                <tr class="small">
                                    <th>Programme</th>
                                    <th>Category</th>
                                    <th>Min Points</th>
                                    <th>Min Division</th>
                                    <th>Core Subjects</th>
                                    <th>Alternative Subjects</th>
                                    <th>Min Grade</th>
                                    <th>Entry Level</th>
                                    <th>Status</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                    @php
                                        $coreSubjects = json_decode($rule->core_subjects ?? '[]', true) ?: [];
                                        $alternativeSubjects = json_decode($rule->alternative_subjects ?? '[]', true) ?: [];
                                        
                                        $categoryLabels = [
                                            'health' => '<span class="badge bg-danger">Health</span>',
                                            'non_health' => '<span class="badge bg-info">Non-Health</span>',
                                            'general' => '<span class="badge bg-secondary">General</span>',
                                        ];
                                    @endphp
                                    <tr class="align-middle small">
                                        <td>
                                            <span class="fw-semibold">{{ $rule->programme_name ?? 'N/A' }}</span>
                                            @if($rule->programme_code)
                                                <div class="small text-muted">{{ $rule->programme_code }}</div>
                                            @endif
                                        </td>
                                        <td>{!! $categoryLabels[$rule->category] ?? $categoryLabels['general'] !!}</td>
                                        <td>{{ $rule->min_csee_points ?? '—' }} ({{ $rule->points_operator === 'gte' ? '≥' : '≤' }})</td>
                                        <td>{{ $rule->min_csee_division ?? '—' }}</td>
                                        <!-- Core Subjects -->
                                        <td>
                                            @if(count($coreSubjects) > 0)
                                                @foreach($coreSubjects as $subject)
                                                    <span class="badge bg-danger me-1 mb-1">{{ $subject }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <!-- Alternative Subjects -->
                                        <td>
                                            @if(count($alternativeSubjects) > 0)
                                                <small class="text-muted d-block mb-1">Need {{ $rule->min_alternative_count ?? 1 }} of:</small>
                                                @foreach($alternativeSubjects as $subject)
                                                    <span class="badge bg-warning me-1 mb-1">{{ $subject }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $rule->min_subject_grade ?? 'D' }}</td>
                                        <td>
                                            @php
                                                $entryLabels = [
                                                    'CSEE' => 'CSEE (Form IV)',
                                                    'ACSEE' => 'ACSEE (Form VI)',
                                                    'Diploma' => 'Diploma',
                                                    'Degree' => 'Degree',
                                                ];
                                            @endphp
                                            {{ $entryLabels[$rule->entry_level] ?? 'CSEE' }}
                                        </td>
                                        <td>
                                            @if($rule->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info edit-rule" 
                                                        data-id="{{ $rule->id }}"
                                                        data-programme="{{ $rule->programme_id }}"
                                                        data-category="{{ $rule->category ?? 'general' }}"
                                                        data-min-csee-points="{{ $rule->min_csee_points }}"
                                                        data-points-operator="{{ $rule->points_operator ?? 'lte' }}"
                                                        data-min-csee-division="{{ $rule->min_csee_division }}"
                                                        data-core-subjects='{{ $rule->core_subjects }}'
                                                        data-alternative-subjects='{{ $rule->alternative_subjects }}'
                                                        data-min-alternative-count="{{ $rule->min_alternative_count ?? 1 }}"
                                                        data-min-subject-grade="{{ $rule->min_subject_grade ?? 'D' }}"
                                                        data-min-acsee="{{ $rule->min_acsee_principal_passes ?? 0 }}"
                                                        data-entry-level="{{ $rule->entry_level ?? 'CSEE' }}"
                                                        data-active="{{ $rule->is_active }}"
                                                        title="Edit">
                                                    <i class="feather-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger delete-rule" data-id="{{ $rule->id }}" title="Delete">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="feather-shield display-4 text-muted d-block mb-3"></i>
                                            <h6 class="text-muted">No eligibility rules defined</h6>
                                            <p class="small text-muted">Use templates or create custom rules for programmes.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($rules) && method_exists($rules, 'links'))
                        <div class="card-footer bg-white py-2">
                            {{ $rules->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Health Programme Template Modal -->
<div class="modal fade" id="addHealthTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-danger text-white">
                <h6 class="modal-title"><i class="feather-plus me-1"></i> Health Programme Template</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission.eligibility.rules.store') }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="alert alert-danger p-2 small">
                        <strong>Health Programme Requirements (NACTE Standard):</strong>
                        <ul class="mb-0 mt-1">
                            <li>Core Sciences: <strong>Physics, Chemistry, Biology</strong> (Minimum D grade)</li>
                            <li>Supporting Subject: <strong>Mathematics</strong> (Minimum D grade)</li>
                            <li>Total points ≤ 25 (Division II or better)</li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-semibold">Programme <span class="text-danger">*</span></label>
                            <select name="programme_id" class="form-select form-select-sm" required>
                                <option value="">Select Health Programme</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->name }} ({{ $programme->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="category" value="health">
                    <input type="hidden" name="min_csee_points" value="25">
                    <input type="hidden" name="points_operator" value="lte">
                    <input type="hidden" name="min_csee_division" value="II">
                    <input type="hidden" name="min_subject_grade" value="D">
                    <input type="hidden" name="min_alternative_count" value="0">
                    <input type="hidden" name="core_subjects" value='["Physics","Chemistry","Biology","Mathematics"]'>
                    <input type="hidden" name="alternative_subjects" value='[]'>
                    <input type="hidden" name="entry_level" value="CSEE">
                    
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Create Health Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Non-Health Programme Template Modal -->
<div class="modal fade" id="addNonHealthTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-info text-white">
                <h6 class="modal-title"><i class="feather-plus me-1"></i> Non-Health Programme Template</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission.eligibility.rules.store') }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="alert alert-info p-2 small">
                        <strong>Non-Health Programme Requirements:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Minimum D grade in any <strong>4 subjects</strong> from approved list</li>
                            <li>Division III or better</li>
                            <li>Any combination of passes</li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-semibold">Programme <span class="text-danger">*</span></label>
                            <select name="programme_id" class="form-select form-select-sm" required>
                                <option value="">Select Non-Health Programme</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->name }} ({{ $programme->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="category" value="non_health">
                    <input type="hidden" name="min_csee_points" value="28">
                    <input type="hidden" name="points_operator" value="lte">
                    <input type="hidden" name="min_csee_division" value="III">
                    <input type="hidden" name="min_subject_grade" value="D">
                    <input type="hidden" name="min_alternative_count" value="4">
                    <input type="hidden" name="core_subjects" value='[]'>
                    <input type="hidden" name="alternative_subjects" value='["Mathematics","English","Kiswahili","Geography","History","Civics","Commerce","Bookkeeping","Physics","Chemistry","Biology"]'>
                    <input type="hidden" name="entry_level" value="CSEE">
                    
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm">Create Non-Health Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Rule Modal with Complete Subject List -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="feather-plus me-1"></i> Add Custom Eligibility Rule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission.eligibility.rules.store') }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="row">
                        <div class="col-md-8 mb-2">
                            <label class="form-label small fw-semibold">Programme <span class="text-danger">*</span></label>
                            <select name="programme_id" class="form-select form-select-sm" required>
                                <option value="">Select Programme</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->name }} ({{ $programme->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="general">General</option>
                                <option value="health">Health</option>
                                <option value="non_health">Non-Health</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="small fw-semibold mt-2 mb-1">CSEE Requirements</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Points Operator</label>
                            <select name="points_operator" class="form-select form-select-sm">
                                <option value="lte">≤ (Less than or equal)</option>
                                <option value="gte">≥ (Greater than or equal)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Minimum Points</label>
                            <input type="number" name="min_csee_points" class="form-control form-control-sm" min="7" max="36" placeholder="e.g., 25">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Minimum Division</label>
                            <select name="min_csee_division" class="form-select form-select-sm">
                                <option value="">Not Required</option>
                                <option value="I">Division I</option>
                                <option value="II">Division II</option>
                                <option value="III">Division III</option>
                                <option value="IV">Division IV</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="small fw-semibold mt-2 mb-1">Subject Requirements</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Core Subjects (All Required)</label>
                            <select name="core_subjects[]" class="form-select form-select-sm" multiple size="6">
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="Civics">Civics</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Bookkeeping">Bookkeeping</option>
                                <option value="Literature">Literature in English</option>
                                <option value="French">French</option>
                                <option value="Arabic">Arabic</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Computer Science">Computer Science</option>
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Alternative Subjects (Choose at least one)</label>
                            <select name="alternative_subjects[]" class="form-select form-select-sm" multiple size="6">
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="Civics">Civics</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Bookkeeping">Bookkeeping</option>
                                <option value="Literature">Literature in English</option>
                                <option value="French">French</option>
                                <option value="Arabic">Arabic</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Computer Science">Computer Science</option>
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Minimum Subject Grade</label>
                            <select name="min_subject_grade" class="form-select form-select-sm">
                                <option value="A">A (Excellent)</option>
                                <option value="B">B (Very Good)</option>
                                <option value="C">C (Good)</option>
                                <option value="D" selected>D (Credit)</option>
                                <option value="E">E (Pass)</option>
                                <option value="F">F (Fail)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Minimum Alternative Count</label>
                            <input type="number" name="min_alternative_count" class="form-control form-control-sm" min="0" max="10" value="1">
                        </div>
                    </div>
                    
                    <h6 class="small fw-semibold mt-2 mb-1">Advanced Level (Optional)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Minimum Principal Passes (ACSEE)</label>
                            <input type="number" name="min_acsee_principal_passes" class="form-control form-control-sm" min="0" max="3" value="0">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Entry Level</label>
                            <select name="entry_level" class="form-select form-select-sm">
                                <option value="CSEE">CSEE (Form IV)</option>
                                <option value="ACSEE">ACSEE (Form VI)</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Degree">Degree</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="feather-edit me-1"></i> Edit Eligibility Rule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRuleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-2">
                    <input type="hidden" name="rule_id" id="edit_rule_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-semibold">Programme</label>
                            <select name="programme_id" id="edit_programme_id" class="form-select form-select-sm" disabled>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->name }} ({{ $programme->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Category</label>
                            <select name="category" id="edit_category" class="form-select form-select-sm">
                                <option value="general">General</option>
                                <option value="health">Health</option>
                                <option value="non_health">Non-Health</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Points Operator</label>
                            <select name="points_operator" id="edit_points_operator" class="form-select form-select-sm">
                                <option value="lte">≤ (Less than or equal)</option>
                                <option value="gte">≥ (Greater than or equal)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Min Points</label>
                            <input type="number" name="min_csee_points" id="edit_min_csee_points" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Min Division</label>
                            <select name="min_csee_division" id="edit_min_csee_division" class="form-select form-select-sm">
                                <option value="">Not Required</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Min Grade</label>
                            <select name="min_subject_grade" id="edit_min_subject_grade" class="form-select form-select-sm">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Min Alternative Count</label>
                            <input type="number" name="min_alternative_count" id="edit_min_alternative_count" class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Core Subjects</label>
                            <select name="core_subjects[]" id="edit_core_subjects" class="form-select form-select-sm" multiple size="5">
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="Civics">Civics</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Bookkeeping">Bookkeeping</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Alternative Subjects</label>
                            <select name="alternative_subjects[]" id="edit_alternative_subjects" class="form-select form-select-sm" multiple size="5">
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="Civics">Civics</option>
                                <option value="Commerce">Commerce</option>
                                <option value="Bookkeeping">Bookkeeping</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Min ACSEE Passes</label>
                            <input type="number" name="min_acsee_principal_passes" id="edit_min_acsee" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label small">Entry Level</label>
                            <select name="entry_level" id="edit_entry_level" class="form-select form-select-sm">
                                <option value="CSEE">CSEE</option>
                                <option value="ACSEE">ACSEE</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Degree">Degree</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input" value="1">
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    select[multiple] {
        min-height: 120px;
        font-size: 0.75rem;
    }
    .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }
    .table-responsive {
        scrollbar-width: thin;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }
    @media (max-width: 768px) {
        .table {
            font-size: 0.7rem;
        }
        .badge {
            font-size: 0.55rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Edit Rule
    $('.edit-rule').click(function() {
        let id = $(this).data('id');
        let category = $(this).data('category');
        let minPoints = $(this).data('min-csee-points');
        let pointsOp = $(this).data('points-operator');
        let minDivision = $(this).data('min-csee-division');
        let minGrade = $(this).data('min-subject-grade');
        let minAltCount = $(this).data('min-alternative-count');
        let minAcsee = $(this).data('min-acsee');
        let entryLevel = $(this).data('entry-level');
        let isActive = $(this).data('active');
        
        // Parse subjects
        let coreSubjects = [];
        let altSubjects = [];
        
        try {
            let coreStr = $(this).data('core-subjects');
            if (coreStr && coreStr !== 'null' && coreStr !== '[]') {
                coreSubjects = typeof coreStr === 'string' ? JSON.parse(coreStr) : coreStr;
            }
        } catch(e) {}
        
        try {
            let altStr = $(this).data('alternative-subjects');
            if (altStr && altStr !== 'null' && altStr !== '[]') {
                altSubjects = typeof altStr === 'string' ? JSON.parse(altStr) : altStr;
            }
        } catch(e) {}
        
        $('#edit_rule_id').val(id);
        $('#edit_category').val(category || 'general');
        $('#edit_min_csee_points').val(minPoints || '');
        $('#edit_points_operator').val(pointsOp || 'lte');
        $('#edit_min_csee_division').val(minDivision || '');
        $('#edit_min_subject_grade').val(minGrade || 'D');
        $('#edit_min_alternative_count').val(minAltCount || 1);
        $('#edit_min_acsee').val(minAcsee || 0);
        $('#edit_entry_level').val(entryLevel || 'CSEE');
        $('#edit_is_active').prop('checked', isActive == 1);
        
        $('#edit_core_subjects').val(coreSubjects);
        $('#edit_alternative_subjects').val(altSubjects);
        
        $('#editRuleForm').attr('action', '{{ url("admission/eligibility/rules") }}/' + id);
        new bootstrap.Modal(document.getElementById('editRuleModal')).show();
    });
    
    // Delete Rule
    $('.delete-rule').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete Rule?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("admission/eligibility/rules") }}/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to delete rule', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection