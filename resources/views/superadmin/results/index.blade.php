{{-- resources/views/superadmin/results/index.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Student Results')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Student Results Management</h3>
        </div>
        <div class="card-body">
            
            {{-- Filters Row --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Programme</label>
                    <select id="filter_programme" class="form-control">
                        <option value="">All Programmes</option>
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>NTA Level</label>
                    <select id="filter_level" class="form-control">
                        <option value="">All Levels</option>
                        <option value="4">Level 4</option>
                        <option value="5">Level 5</option>
                        <option value="6">Level 6</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Academic Year</label>
                    <select id="filter_year" class="form-control">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Semester</label>
                    <select id="filter_semester" class="form-control">
                        <option value="">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" onclick="loadStudents()">Filter</button>
                </div>
            </div>

            {{-- Search Row --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="search_reg" class="form-control" placeholder="Search by Registration Number or NACTE Number...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="loadStudents()">Search</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ url('superadmin/results') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100" onclick="showAddModal()">Add Result</button>
                </div>
            </div>

            {{-- Students Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead style="background-color: #000000; color: white;">
                        <tr>
                            <th>#</th>
                            <th>Reg Number</th>
                            <th>Student Name</th>
                            <th>Programme</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary"></div> Loading students...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Result Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add Result</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="add_student_id" name="student_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Module</label>
                                <select id="module_id" name="module_id" class="form-control" required>
                                    <option value="">Select Module</option>
                                    @foreach($allModules as $module)
                                        <option value="{{ $module->id }}">{{ $module->code }} - {{ $module->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Academic Year</label>
                                <select id="academic_year_id" name="academic_year_id" class="form-control" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Semester</label>
                            <select id="semester" name="semester" class="form-control" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Attempt Type</label>
                            <select id="attempt_type" name="attempt_type" class="form-control">
                                <option value="normal">Normal</option>
                                <option value="supplementary">Supplementary</option>
                                <option value="special">Special</option>
                                <option value="carryover">Carryover</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Attempt No</label>
                            <input type="number" id="attempt_no" name="attempt_no" class="form-control" value="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>CW Score</label>
                            <input type="number" step="0.01" id="raw_cw" name="raw_cw" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Exam Score</label>
                            <input type="number" step="0.01" id="raw_exam" name="raw_exam" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Workflow Status</label>
                        <select id="workflow_status" name="workflow_status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    loadStudents();
    
    $('#filter_programme, #filter_level, #filter_year, #filter_semester').on('change', function() {
        loadStudents();
    });
    
    $('#search_reg').on('keypress', function(e) {
        if (e.which === 13) loadStudents();
    });
});

function loadStudents() {
    let data = {
        programme_id: $('#filter_programme').val(),
        nta_level: $('#filter_level').val(),
        academic_year_id: $('#filter_year').val(),
        semester: $('#filter_semester').val(),
        search: $('#search_reg').val()
    };
    
    $('#studentsTableBody').html('<tr><td colspan="6" class="text-center"><div class="spinner-border"></div> Loading...</td></tr>');
    
    $.ajax({
        url: '/superadmin/results/api/students/search',
        method: 'GET',
        data: data,
        success: function(response) {
            if (response.success && response.data.data) {
                displayStudents(response.data.data);
            } else {
                $('#studentsTableBody').html('<tr><td colspan="6" class="text-center text-danger">No students found</td></tr>');
            }
        },
        error: function() {
            $('#studentsTableBody').html('<td><td colspan="6" class="text-center text-danger">Error loading students</td></tr>');
        }
    });
}

function displayStudents(students) {
    let tbody = $('#studentsTableBody');
    tbody.empty();
    
    if (!students || students.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center text-muted">No students found</td></tr>');
        return;
    }
    
    $.each(students, function(i, s) {
        let name = s.name || 'N/A';
        let regNumber = s.registration_number || 'N/A';
        let programme = s.programme_name || 'N/A';
        let year = s.current_level || 'N/A';
        
        let row = `
            <tr>
                <td>${i+1}</td>
                <td><strong>${regNumber}</strong><br><small class="text-muted">${s.nacte_reg_number || ''}</small></td>
                <td>${name}</td>
                <td>${programme}</td>
                <td>Level ${year}</td>
                <td>
                    <a href="{{ url('superadmin/results/transcript') }}/${s.id}" class="btn btn-sm btn-info" target="_blank">
                        <i class="feather-eye"></i> View Results
                    </a>
                    <button class="btn btn-sm btn-primary" onclick="openAddModal(${s.id})">
                        <i class="feather-plus"></i> Add Result
                    </button>
                 </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function openAddModal(studentId) {
    $('#add_student_id').val(studentId);
    $('#addForm')[0].reset();
    $('#addModal').modal('show');
}

function showAddModal() {
    let firstStudentId = $('#studentsTableBody .btn-primary').first().attr('onclick');
    if (firstStudentId) {
        let id = firstStudentId.match(/\d+/)[0];
        openAddModal(id);
    } else {
        toastr.warning('Please load students first');
    }
}

$('#addForm').on('submit', function(e) {
    e.preventDefault();
    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('Saving...');
    
    $.ajax({
        url: '/superadmin/results/api/store',
        method: 'POST',
        data: $(this).serialize(),
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(r) {
            if (r.success) {
                toastr.success(r.message);
                $('#addModal').modal('hide');
                loadStudents();
            } else {
                toastr.error(r.message || 'Error saving');
            }
            btn.prop('disabled', false).html('Save');
        },
        error: function(x) {
            btn.prop('disabled', false).html('Save');
            toastr.error(x.responseJSON?.message || 'Error saving');
        }
    });
});
</script>
@endsection