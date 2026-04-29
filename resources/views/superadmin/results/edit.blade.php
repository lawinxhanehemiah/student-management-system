{{-- resources/views/superadmin/results/edit.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Edit Result')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit"></i> Edit Result
            </h3>
            <div class="card-tools">
                <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Results
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <form id="resultForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="result_id" name="id" value="{{ $result->id }}">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Student <span class="text-danger">*</span></label>
                            <select id="student_id" name="student_id" class="form-control select2" required>
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ $result->student_id == $student->id ? 'selected' : '' }}>
                                        {{ $student->registration_number }} - {{ $student->user->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Module <span class="text-danger">*</span></label>
                            <select id="module_id" name="module_id" class="form-control select2" required>
                                <option value="">Select Module</option>
                                @foreach($allModules as $module)
                                    <option value="{{ $module->id }}" {{ $result->module_id == $module->id ? 'selected' : '' }}>
                                        {{ $module->code }} - {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select id="academic_year_id" name="academic_year_id" class="form-control" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $result->academic_year_id == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester</label>
                            <select id="semester" name="semester" class="form-control" required>
                                <option value="1" {{ $result->semester == 1 ? 'selected' : '' }}>Semester 1</option>
                                <option value="2" {{ $result->semester == 2 ? 'selected' : '' }}>Semester 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Workflow Status</label>
                            <select id="workflow_status" name="workflow_status" class="form-control">
                                <option value="draft" {{ $result->workflow_status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ $result->workflow_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="published" {{ $result->workflow_status == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>CW Score (0-100)</label>
                            <input type="number" step="0.01" id="raw_cw" name="raw_cw" class="form-control" min="0" max="100" value="{{ $result->raw_cw }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exam Score (0-100)</label>
                            <input type="number" step="0.01" id="raw_exam" name="raw_exam" class="form-control" min="0" max="100" value="{{ $result->raw_exam }}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Attempt Type</label>
                            <select id="attempt_type" name="attempt_type" class="form-control">
                                <option value="normal" {{ $result->attempt_type == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="supplementary" {{ $result->attempt_type == 'supplementary' ? 'selected' : '' }}>Supplementary</option>
                                <option value="special" {{ $result->attempt_type == 'special' ? 'selected' : '' }}>Special</option>
                                <option value="carryover" {{ $result->attempt_type == 'carryover' ? 'selected' : '' }}>Carryover</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Attempt No</label>
                            <input type="number" id="attempt_no" name="attempt_no" class="form-control" min="1" value="{{ $result->attempt_no }}">
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info" id="calculationPreview" style="display:none;">
                    <strong>Grade Preview:</strong><br>
                    CW: <span id="previewCW">0</span> → <span id="previewCWScore">0</span><br>
                    Exam: <span id="previewExam">0</span> → <span id="previewExamScore">0</span><br>
                    <strong>Total: <span id="previewTotal">0</span></strong><br>
                    Grade: <strong id="previewGrade">-</strong>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Result
                    </button>
                    <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });
});

$('#resultForm').on('submit', function(e) {
    e.preventDefault();
    
    let id = $('#result_id').val();
    let submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: `/superadmin/results/api/${id}`,
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(function() {
                    window.location.href = '{{ route("superadmin.results.index") }}';
                }, 1500);
            } else {
                toastr.error(response.message);
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Result');
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Result');
            if (xhr.responseJSON?.errors) {
                $.each(xhr.responseJSON.errors, function(key, val) {
                    toastr.error(val[0]);
                });
            } else {
                toastr.error('Error updating result');
            }
        }
    });
});
</script>
@endpush