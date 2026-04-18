{{-- resources/views/hod/promotion/bulk.blade.php --}}
@extends('layouts.hod')

@section('title', 'Bulk Promotion')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        Bulk Promotion
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Bulk promotion will promote all eligible students at once. 
                        Each student will be checked individually against promotion conditions.
                    </div>

                    <form action="{{ route('hod.promotion.process-bulk') }}" method="POST" id="bulkForm">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label class="fw-bold">Promotion Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <input type="radio" name="promotion_type" value="semester" 
                                                   id="semesterPromotion" class="form-check-input me-2" required>
                                            <label for="semesterPromotion" class="form-check-label">
                                                <i class="fas fa-arrow-right-circle fa-2x text-primary"></i>
                                                <h5 class="mt-2">Semester Promotion</h5>
                                                <small class="text-muted">Promote to next semester</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <input type="radio" name="promotion_type" value="level" 
                                                   id="levelPromotion" class="form-check-input me-2">
                                            <label for="levelPromotion" class="form-check-label">
                                                <i class="fas fa-arrow-up fa-2x text-success"></i>
                                                <h5 class="mt-2">Level Promotion</h5>
                                                <small class="text-muted">Promote to next academic year</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="fw-bold">Select Students</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <input type="checkbox" name="select_all" value="1" id="selectAllStudents" class="form-check-input me-2">
                                            <label for="selectAllStudents" class="form-check-label">
                                                <strong>Select All Eligible Students</strong>
                                            </label>
                                            <p class="text-muted small mt-2">
                                                <i class="fas fa-info-circle"></i>
                                                This will automatically select all students who meet promotion conditions
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="manualSelectionDiv" style="display: none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <label class="fw-bold">Manual Selection</label>
                                            <select name="student_ids[]" id="studentSelect" class="form-control select2" multiple>
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}">
                                                        {{ $student->registration_number }} - 
                                                        {{ $student->user->first_name }} {{ $student->user->last_name }}
                                                        (Year {{ $student->current_level }}, Sem {{ $student->current_semester }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Hold Ctrl to select multiple students</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Conditions Check:</strong>
                            <ul class="mb-0 mt-2">
                                <li>✓ Student must be ACTIVE</li>
                                <li>✓ Must have completed current semester/year</li>
                                <li>✓ All results must be available</li>
                                <li>✓ GPA/CGPA must be ≥ 2.0</li>
                                <li>✓ Fee balance must be cleared</li>
                            </ul>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-play"></i> Start Bulk Promotion
                            </button>
                            <a href="{{ route('hod.promotion.history') }}" class="btn btn-info btn-lg">
                                <i class="fas fa-history"></i> View Promotion History
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#studentSelect').select2({
        placeholder: 'Search and select students...',
        allowClear: true
    });
    
    // Toggle between select all and manual selection
    $('#selectAllStudents').change(function() {
        if ($(this).is(':checked')) {
            $('#manualSelectionDiv').hide();
            $('#studentSelect').prop('disabled', true);
        } else {
            $('#manualSelectionDiv').show();
            $('#studentSelect').prop('disabled', false);
        }
    });
    
    // Form submission
    $('#bulkForm').submit(function(e) {
        e.preventDefault();
        
        var promotionType = $('input[name="promotion_type"]:checked').val();
        var selectAll = $('#selectAllStudents').is(':checked');
        
        if (!promotionType) {
            Swal.fire('Error', 'Please select promotion type', 'error');
            return false;
        }
        
        var message = '';
        if (selectAll) {
            message = 'Are you sure you want to promote ALL eligible students? This action cannot be undone.';
        } else {
            var selectedCount = $('#studentSelect').val() ? $('#studentSelect').val().length : 0;
            if (selectedCount === 0) {
                Swal.fire('Error', 'Please select at least one student', 'error');
                return false;
            }
            message = `Are you sure you want to promote ${selectedCount} selected student(s)?`;
        }
        
        Swal.fire({
            title: 'Confirm Bulk Promotion',
            html: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the promotion',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form via AJAX
                var formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Bulk Promotion Completed',
                            html: `
                                <div class="text-left">
                                    <p><strong>Total Processed:</strong> ${response.total}</p>
                                    <p><strong>Successfully Promoted:</strong> <span class="text-success">${response.eligible}</span></p>
                                    <p><strong>Failed/Ineligible:</strong> <span class="text-danger">${response.ineligible}</span></p>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('hod.promotion.results') }}?type=${$('input[name="promotion_type"]:checked').val()}&successful=${encodeURIComponent(JSON.stringify(response.successful))}&failed=${encodeURIComponent(JSON.stringify(response.failed))}" 
                                       class="btn btn-primary">
                                        View Detailed Results
                                    </a>
                                </div>
                            `,
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to process bulk promotion', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    transition: all 0.3s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    min-height: 100px;
}
</style>
@endpush
@endsection