<!-- Student Selector Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalLabel">Select Student</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="student_id">Select Student</label>
                    <select class="form-control select2" id="student_id" style="width: 100%;">
                        <option value="">-- Select Student --</option>
                        @foreach($allStudents ?? [] as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->registration_number }} - 
                                {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle"></i> 
                    Please select a student to continue
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="proceedBtn">Proceed</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let currentAction = '';

function openStudentModal(action) {
    currentAction = action;
    $('#studentModal').modal('show');
    
    // Initialize select2 if not already initialized
    if (!$('#student_id').hasClass('select2-hidden-accessible')) {
        $('#student_id').select2({
            placeholder: 'Search student by name or registration number',
            allowClear: true,
            dropdownParent: $('#studentModal')
        });
    }
}

$('#proceedBtn').click(function() {
    let studentId = $('#student_id').val();
    
    if (!studentId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a student first',
            icon: 'error',
            timer: 2000
        });
        return;
    }
    
    let url = '';
    let title = '';
    
    switch(currentAction) {
        case 'profile':
            url = '{{ route("hod.students.profile", "") }}/' + studentId;
            title = 'Student Profile';
            break;
        case 'academic':
            url = '{{ route("hod.students.academic-history", "") }}/' + studentId;
            title = 'Academic History';
            break;
        case 'register':
            url = '{{ route("hod.students.register-courses", "") }}/' + studentId;
            title = 'Register Courses';
            break;
        case 'clearance':
            url = '{{ route("hod.students.clearance", "") }}/' + studentId;
            title = 'Clearance Status';
            break;
        default:
            return;
    }
    
    // Close modal and redirect
    $('#studentModal').modal('hide');
    window.location.href = url;
});

// Allow pressing Enter to proceed
$('#student_id').keypress(function(e) {
    if (e.which == 13) {
        $('#proceedBtn').click();
    }
});
</script>
@endpush