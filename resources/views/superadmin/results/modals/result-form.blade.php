{{-- resources/views/superadmin/results/modals/result-form.blade.php --}}

<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalTitle">Add/Edit Result</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="resultForm">
                @csrf
                <input type="hidden" id="result_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Student <span class="text-danger">*</span></label>
                                <select id="student_id" name="student_id" class="form-control select2" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->registration_number }} - {{ $student->user->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Module <span class="text-danger">*</span></label>
                                <select id="module_id_select" name="module_id" class="form-control select2" required>
                                    <option value="">Select Module</option>
                                    @foreach($allModules as $module)
                                        <option value="{{ $module->id }}">{{ $module->code }} - {{ $module->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Academic Year <span class="text-danger">*</span></label>
                                <select id="academic_year_id_select" name="academic_year_id" class="form-control" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Semester <span class="text-danger">*</span></label>
                                <select id="semester_select" name="semester" class="form-control" required>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>CW Score (0-100)</label>
                                <input type="number" step="0.01" id="raw_cw" name="raw_cw" class="form-control" min="0" max="100">
                                <small class="form-text text-muted">Continuous Assessment</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Exam Score (0-100)</label>
                                <input type="number" step="0.01" id="raw_exam" name="raw_exam" class="form-control" min="0" max="100">
                                <small class="form-text text-muted">Final Examination</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Attempt No</label>
                                <input type="number" id="attempt_no" name="attempt_no" class="form-control" min="1" value="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Workflow Status</label>
                        <select id="workflow_status_select" name="workflow_status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info mt-3" id="calculationPreview" style="display:none;">
                        <strong>Preview:</strong><br>
                        CW: <span id="previewCW">0</span> × <span id="previewCWWeight">0</span>% = <span id="previewCWScore">0</span><br>
                        Exam: <span id="previewExam">0</span> × <span id="previewExamWeight">0</span>% = <span id="previewExamScore">0</span><br>
                        <strong>Total: <span id="previewTotal">0</span></strong><br>
                        Expected Grade: <strong id="previewGrade">-</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Result</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        dropdownParent: $('#resultModal'),
        width: '100%'
    });
    
    // Real-time calculation preview
    $('#raw_cw, #raw_exam, #module_id_select').on('change keyup', function() {
        let moduleId = $('#module_id_select').val();
        let cw = parseFloat($('#raw_cw').val()) || 0;
        let exam = parseFloat($('#raw_exam').val()) || 0;
        
        if (moduleId) {
            $.get(`/superadmin/results/api/modules/${moduleId}/weights`, function(response) {
                if (response.success) {
                    let cwWeight = response.data.cw_weight;
                    let examWeight = response.data.exam_weight;
                    let totalWeight = cwWeight + examWeight;
                    
                    let cwScore = (cw * cwWeight) / totalWeight;
                    let examScore = (exam * examWeight) / totalWeight;
                    let total = cwScore + examScore;
                    
                    $('#previewCW').text(cw);
                    $('#previewCWWeight').text(cwWeight);
                    $('#previewCWScore').text(cwScore.toFixed(2));
                    $('#previewExam').text(exam);
                    $('#previewExamWeight').text(examWeight);
                    $('#previewExamScore').text(examScore.toFixed(2));
                    $('#previewTotal').text(total.toFixed(2));
                    
                    // Get grade
                    $.get(`/superadmin/results/api/get-grade?score=${total}&module_id=${moduleId}`, function(gradeRes) {
                        if (gradeRes.success) {
                            $('#previewGrade').text(`${gradeRes.data.grade} (${gradeRes.data.grade_point} points)`);
                        }
                    });
                    
                    $('#calculationPreview').show();
                }
            });
        }
    });
});

$('#resultForm').on('submit', function(e) {
    e.preventDefault();
    let id = $('#result_id').val();
    let url = id ? `/superadmin/results/api/${id}` : '/superadmin/results/api/store';
    let method = id ? 'PUT' : 'POST';
    
    let data = {
        student_id: $('#student_id').val(),
        module_id: $('#module_id_select').val(),
        academic_year_id: $('#academic_year_id_select').val(),
        semester: $('#semester_select').val(),
        raw_cw: $('#raw_cw').val(),
        raw_exam: $('#raw_exam').val(),
        attempt_type: $('#attempt_type').val(),
        attempt_no: $('#attempt_no').val(),
        workflow_status: $('#workflow_status_select').val(),
        _method: method
    };
    
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#resultModal').modal('hide');
                loadResults();
                loadStats();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON?.errors) {
                $.each(xhr.responseJSON.errors, function(key, val) {
                    toastr.error(val[0]);
                });
            } else {
                toastr.error('Error saving result');
            }
        }
    });
});
</script>