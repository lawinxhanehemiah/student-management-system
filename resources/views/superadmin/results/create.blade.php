{{-- resources/views/superadmin/results/create.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Add New Result')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="feather-plus-circle"></i> Add New Result
            </h3>
            <div class="card-tools">
                <a href="{{ route('superadmin.results.index') }}" class="btn btn-light btn-sm">
                    <i class="feather-arrow-left"></i> Back to Results
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <form id="resultForm">
                @csrf
                
                {{-- Row 1: Programme, Level, Semester --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Programme <span class="text-danger">*</span></label>
                            <select id="programme_id" name="programme_id" class="form-control" required>
                                <option value="">-- Select Programme --</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NTA Level <span class="text-danger">*</span></label>
                            <select id="nta_level" name="nta_level" class="form-control" disabled required>
                                <option value="">-- Select Programme First --</option>
                                <option value="4">NTA Level 4</option>
                                <option value="5">NTA Level 5</option>
                                <option value="6">NTA Level 6</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester <span class="text-danger">*</span></label>
                            <select id="semester" name="semester" class="form-control" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Row 2: Student Search --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Student <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="student_search" class="form-control" 
                                       placeholder="Enter Registration Number...">
                                <button class="btn btn-primary" type="button" id="searchStudentBtn">
                                    <i class="feather-search"></i> Search
                                </button>
                            </div>
                            <input type="hidden" id="student_id" name="student_id">
                            <div id="studentInfo" class="mt-2" style="display:none;">
                                <div class="alert alert-success">
                                    <i class="feather-check-circle"></i> 
                                    <strong id="studentName"></strong><br>
                                    <small>Reg: <span id="studentReg"></span> | Programme: <span id="studentProgramme"></span> | Level: <span id="studentLevel"></span></small>
                                    <button type="button" class="close" onclick="clearStudent()">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Module Dropdown --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Module <span class="text-danger">*</span></label>
                            <select id="module_id" name="module_id" class="form-control" disabled required>
                                <option value="">-- Select Programme and Level First --</option>
                            </select>
                            <div id="moduleInfo" class="mt-2" style="display:none;">
                                <div class="alert alert-info">
                                    <i class="feather-book"></i> 
                                    <strong id="moduleName"></strong><br>
                                    <small>Code: <span id="moduleCode"></span> | CW: <span id="moduleCW"></span>% | Exam: <span id="moduleExam"></span>% | Credits: <span id="moduleCredits"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Row 3: Academic Year, Workflow, Attempt Type --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select id="academic_year_id" name="academic_year_id" class="form-control" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Workflow Status</label>
                            <select id="workflow_status" name="workflow_status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Attempt Type</label>
                            <select id="attempt_type" name="attempt_type" class="form-control">
                                <option value="normal">Normal</option>
                                <option value="supplementary">Supplementary</option>
                                <option value="special">Special</option>
                                <option value="carryover">Carryover</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Row 4: CW and Exam Scores --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>CW Score</label>
                            <input type="number" step="0.01" id="raw_cw" name="raw_cw" class="form-control" min="0">
                            <small class="form-text text-muted" id="cwWeightInfo"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exam Score</label>
                            <input type="number" step="0.01" id="raw_exam" name="raw_exam" class="form-control" min="0">
                            <small class="form-text text-muted" id="examWeightInfo"></small>
                        </div>
                    </div>
                </div>
                
                {{-- Row 5: Attempt No --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Attempt No</label>
                            <input type="number" id="attempt_no" name="attempt_no" class="form-control" min="1" value="1">
                        </div>
                    </div>
                </div>
                
                {{-- Grade Preview --}}
                <div class="alert alert-info" id="calculationPreview" style="display:none;">
                    <strong>Grade Preview:</strong>
                    <div class="row">
                        <div class="col-md-6">Total Score: <strong id="previewTotal">0</strong></div>
                        <div class="col-md-6">Pass Mark: <strong id="previewPassMark">50</strong>%</div>
                    </div>
                    <div>Grade: <strong id="previewGrade">-</strong> | Points: <strong id="previewPoints">-</strong> | Status: <strong id="previewStatus">-</strong></div>
                </div>
                
                {{-- Buttons --}}
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="feather-save"></i> Save Result
                    </button>
                    <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    console.log('Create result page loaded');
    
    // Programme change - enable NTA level
    $('#programme_id').on('change', function() {
        var programmeId = $(this).val();
        if (programmeId) {
            $('#nta_level').prop('disabled', false);
            clearStudent();
            clearModule();
        } else {
            $('#nta_level').prop('disabled', true).val('');
            clearModuleDropdown();
        }
    });
    
    // NTA Level change - load modules
    $('#nta_level').on('change', function() {
        var programmeId = $('#programme_id').val();
        var ntaLevel = $(this).val();
        
        if (programmeId && ntaLevel) {
            loadModulesDropdown(programmeId, ntaLevel);
        } else {
            clearModuleDropdown();
        }
    });
    
    // Module selection - show module info
    $('#module_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var moduleId = $(this).val();
        
        if (moduleId) {
            var cwWeight = selectedOption.data('cw') || 50;
            var examWeight = selectedOption.data('exam') || 50;
            var moduleCode = selectedOption.data('code') || '';
            var moduleName = selectedOption.text() || '';
            var credits = selectedOption.data('credits') || 3;
            
            $('#moduleName').text(moduleName);
            $('#moduleCode').text(moduleCode);
            $('#moduleCW').text(cwWeight);
            $('#moduleExam').text(examWeight);
            $('#moduleCredits').text(credits);
            $('#moduleInfo').show();
            
            $('#raw_cw').attr('max', cwWeight);
            $('#raw_exam').attr('max', examWeight);
            $('#cwWeightInfo').text('Weight: ' + cwWeight + '% (max ' + cwWeight + ')');
            $('#examWeightInfo').text('Weight: ' + examWeight + '% (max ' + examWeight + ')');
            
            calculatePreview();
        } else {
            clearModule();
        }
    });
    
    // Search student
    $('#searchStudentBtn').on('click', function() {
        searchStudent();
    });
    
    $('#student_search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchStudent();
        }
    });
    
    // Calculate preview on score change
    $('#raw_cw, #raw_exam').on('keyup change', function() {
        calculatePreview();
    });
    
    // Form submit
    $('#resultForm').on('submit', function(e) {
        e.preventDefault();
        saveResult();
    });
});

// Load modules dropdown
function loadModulesDropdown(programmeId, ntaLevel) {
    var moduleSelect = $('#module_id');
    moduleSelect.prop('disabled', true);
    moduleSelect.html('<option value="">Loading modules...</option>');
    
    $.ajax({
        url: '/superadmin/results/api/modules/search',
        method: 'GET',
        data: {
            programme_id: programmeId,
            nta_level: ntaLevel
        },
        success: function(response) {
            if (response.success && response.data.data && response.data.data.length > 0) {
                var options = '<option value="">-- Select Module --</option>';
                
                $.each(response.data.data, function(index, module) {
                    options += '<option value="' + module.id + '" ' +
                        'data-cw="' + module.cw_weight + '" ' +
                        'data-exam="' + module.exam_weight + '" ' +
                        'data-code="' + module.code + '" ' +
                        'data-credits="' + (module.default_credits || 3) + '">' +
                        module.code + ' - ' + module.name + 
                        ' (CW: ' + module.cw_weight + '%, Exam: ' + module.exam_weight + '%)' +
                        '</option>';
                });
                
                moduleSelect.html(options);
                moduleSelect.prop('disabled', false);
            } else {
                moduleSelect.html('<option value="">No modules found</option>');
                moduleSelect.prop('disabled', true);
            }
        },
        error: function(xhr) {
            moduleSelect.html('<option value="">Error loading modules</option>');
            moduleSelect.prop('disabled', true);
        }
    });
}

// Search student
function searchStudent() {
    var searchTerm = $('#student_search').val().trim();
    
    if (!searchTerm) {
        toastr.warning('Please enter registration number');
        return;
    }
    
    var btn = $('#searchStudentBtn');
    btn.html('<i class="feather-loader"></i> Searching...').prop('disabled', true);
    
    $.ajax({
        url: '/superadmin/results/api/student/by-reg-number',
        method: 'GET',
        data: { reg_number: searchTerm },
        success: function(response) {
            btn.html('<i class="feather-search"></i> Search').prop('disabled', false);
            
            if (response.success) {
                var student = response.data;
                
                var selectedProgramme = $('#programme_id').val();
                if (selectedProgramme && student.programme_id != selectedProgramme) {
                    toastr.error('Student belongs to ' + student.programme_name);
                    clearStudent();
                    return;
                }
                
                $('#studentName').text(student.name);
                $('#studentReg').text(student.registration_number);
                $('#studentProgramme').text(student.programme_name);
                $('#studentLevel').text(student.current_level);
                $('#studentInfo').show();
                $('#student_id').val(student.id);
                
                if (!selectedProgramme && student.programme_id) {
                    $('#programme_id').val(student.programme_id).trigger('change');
                }
                
                if (student.current_level) {
                    $('#nta_level').val(student.current_level).trigger('change');
                }
                
                toastr.success('Student found: ' + student.name);
            } else {
                toastr.error('Student not found');
                clearStudent();
            }
        },
        error: function() {
            btn.html('<i class="feather-search"></i> Search').prop('disabled', false);
            toastr.error('Error searching student');
        }
    });
}

function clearStudent() {
    $('#student_id').val('');
    $('#studentInfo').hide();
    $('#student_search').val('');
}

function clearModule() {
    $('#moduleInfo').hide();
    $('#raw_cw').val('').attr('max', 100);
    $('#raw_exam').val('').attr('max', 100);
    $('#cwWeightInfo').text('');
    $('#examWeightInfo').text('');
    $('#calculationPreview').hide();
}

function clearModuleDropdown() {
    var moduleSelect = $('#module_id');
    moduleSelect.html('<option value="">-- Select Programme and Level First --</option>');
    moduleSelect.prop('disabled', true);
    clearModule();
}

function calculatePreview() {
    var moduleId = $('#module_id').val();
    var studentId = $('#student_id').val();
    var cw = parseFloat($('#raw_cw').val()) || 0;
    var exam = parseFloat($('#raw_exam').val()) || 0;
    
    if (moduleId && studentId) {
        $.ajax({
            url: '/superadmin/results/api/calculate-score',
            method: 'GET',
            data: {
                module_id: moduleId,
                student_id: studentId,
                cw: cw,
                exam: exam
            },
            success: function(response) {
                if (response.success) {
                    $('#previewTotal').text(response.data.total);
                    $('#previewGrade').text(response.data.grade);
                    $('#previewPoints').text(response.data.grade_point);
                    $('#previewPassMark').text(response.data.pass_mark);
                    
                    var statusClass = response.data.result_status === 'pass' ? 'success' : 'danger';
                    $('#previewStatus').html('<span class="badge bg-' + statusClass + '">' + 
                        response.data.result_status.toUpperCase() + '</span>');
                    
                    $('#calculationPreview').show();
                }
            }
        });
    } else {
        $('#calculationPreview').hide();
    }
}

function saveResult() {
    if (!$('#student_id').val()) {
        toastr.error('Please select a student');
        return;
    }
    if (!$('#module_id').val()) {
        toastr.error('Please select a module');
        return;
    }
    if (!$('#programme_id').val()) {
        toastr.error('Please select programme');
        return;
    }
    if (!$('#nta_level').val()) {
        toastr.error('Please select NTA level');
        return;
    }
    if (!$('#semester').val()) {
        toastr.error('Please select semester');
        return;
    }
    
    var submitBtn = $('#submitBtn');
    submitBtn.prop('disabled', true).html('<i class="feather-loader"></i> Saving...');
    
    $.ajax({
        url: '{{ route("superadmin.results.api.store") }}',
        method: 'POST',
        data: $('#resultForm').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Result saved successfully!');
                setTimeout(function() {
                    window.location.href = '{{ route("superadmin.results.index") }}';
                }, 1500);
            } else {
                toastr.error(response.message || 'Error saving result');
                submitBtn.prop('disabled', false).html('<i class="feather-save"></i> Save Result');
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html('<i class="feather-save"></i> Save Result');
            var errorMsg = xhr.responseJSON?.message || 'Error saving result';
            toastr.error(errorMsg);
        }
    });
}
</script>
@endpush