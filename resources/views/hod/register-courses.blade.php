@extends('layouts.hod')

@section('title', 'Register Courses')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Register Courses for 
                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Reg No: {{ $student->registration_number }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Academic Information:</strong> Year {{ $student->current_level }}, 
                        Semester {{ $student->current_semester }}, 
                        {{ ucfirst(str_replace('_', ' ', $student->study_mode)) }} Mode
                    </div>

                    <form action="{{ route('hod.students.store-course-registration', $student->id) }}" 
                          method="POST" id="courseRegistrationForm">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="select-all">
                                        </th>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Credits</th>
                                        <th>Prerequisites</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableCourses as $course)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="courses[]" 
                                                   value="{{ $course->id }}" 
                                                   class="course-checkbox"
                                                   {{ in_array($course->id, $registeredCourseIds) ? 'checked' : '' }}>
                                        </td>
                                        <td><strong>{{ $course->code }}</strong></td>
                                        <td>{{ $course->name }}</td>
                                        <td class="text-center">{{ $course->credit_hours ?? 3 }}</td>
                                        <td>
                                            @if($course->prerequisites)
                                                <small class="text-muted">{{ $course->prerequisites }}</small>
                                            @else
                                                <span class="text-success">None</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-danger">
                                            No courses available for this level and semester.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h5 class="card-title">Registration Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Selected Courses:</strong> <span id="selectedCount">0</span></p>
                                        <p><strong>Total Credits:</strong> <span id="totalCredits">0</span></p>
                                        <p><strong>Minimum Required Credits:</strong> 15</p>
                                        <p><strong>Maximum Allowed Credits:</strong> 24</p>
                                        <div class="progress mt-2">
                                            <div id="creditsProgress" class="progress-bar" role="progressbar" 
                                                 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="24">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h5 class="card-title">Important Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>Ensure you meet all prerequisites before selecting courses</li>
                                            <li>Minimum of 15 credits required per semester</li>
                                            <li>Maximum of 24 credits allowed per semester</li>
                                            <li>Changes after registration deadline require approval</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-save"></i> Register Courses
                            </button>
                            <a href="{{ route('hod.students.profile', $student->id) }}" 
                               class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
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
    // Calculate total credits function
    function calculateTotal() {
        let totalCredits = 0;
        let selectedCount = 0;
        
        $('.course-checkbox:checked').each(function() {
            let row = $(this).closest('tr');
            let credits = parseInt(row.find('td:eq(3)').text()) || 0;
            totalCredits += credits;
            selectedCount++;
        });
        
        $('#selectedCount').text(selectedCount);
        $('#totalCredits').text(totalCredits);
        
        // Update progress bar
        let percentage = (totalCredits / 24) * 100;
        $('#creditsProgress').css('width', percentage + '%');
        
        // Color based on credits
        if (totalCredits < 15) {
            $('#creditsProgress').removeClass('bg-success bg-warning').addClass('bg-danger');
        } else if (totalCredits > 24) {
            $('#creditsProgress').removeClass('bg-success bg-danger').addClass('bg-warning');
        } else {
            $('#creditsProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
        }
        
        // Enable/disable submit button
        let isValid = totalCredits >= 15 && totalCredits <= 24;
        $('#submitBtn').prop('disabled', !isValid);
        
        if (totalCredits < 15) {
            $('#submitBtn').attr('title', 'Minimum 15 credits required');
        } else if (totalCredits > 24) {
            $('#submitBtn').attr('title', 'Maximum 24 credits allowed');
        } else {
            $('#submitBtn').attr('title', '');
        }
    }
    
    // Select All functionality
    $('#selectAll').change(function() {
        $('.course-checkbox').prop('checked', $(this).is(':checked'));
        calculateTotal();
    });
    
    // Calculate on checkbox change
    $('.course-checkbox').change(function() {
        calculateTotal();
    });
    
    // Initial calculation
    calculateTotal();
    
    // Form validation
    $('#courseRegistrationForm').submit(function(e) {
        let totalCredits = parseInt($('#totalCredits').text());
        
        if (totalCredits < 15) {
            e.preventDefault();
            alert('Please select at least 15 credits worth of courses.');
            return false;
        }
        
        if (totalCredits > 24) {
            e.preventDefault();
            alert('You have exceeded the maximum of 24 credits.');
            return false;
        }
        
        if ($('.course-checkbox:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one course.');
            return false;
        }
        
        return confirm('Are you sure you want to register these courses? This action will replace any existing registrations.');
    });
});
</script>
@endpush
@endsection