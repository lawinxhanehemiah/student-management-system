@extends('layouts.admission')

@section('title', 'Create New Intake')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create New Intake</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.intakes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admission.intakes.store') }}" id="intakeForm">
                    @csrf
                    <div class="card-body">
                        
                        <!-- Basic Information -->
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Intake Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., March 2025 Intake" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Intake Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="e.g., 2025-MAR" required>
                                    <small class="text-muted">Unique identifier for this intake</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="upcoming">Upcoming</option>
                                        <option value="open">Open</option>
                                        <option value="closed">Closed</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date Ranges -->
                        <h5 class="mb-3 mt-4">Date Ranges</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Application Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="application_deadline" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Announcement Date</label>
                                    <input type="date" name="announcement_date" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registration Deadline</label>
                                    <input type="date" name="registration_deadline" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Max Applications</label>
                                    <input type="number" name="max_applications" class="form-control" min="1" placeholder="Leave empty for unlimited">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Programme Assignment -->
                        <h5 class="mb-3 mt-4">Assign Programmes</h5>
                        <div class="alert alert-info">
                            <i class="feather-info"></i> Select programmes that will be available for this intake.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="programmesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">Select</th>
                                        <th>Programme</th>
                                        <th>Code</th>
                                        <th>Capacity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programmes as $programme)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="programme_ids[]" value="{{ $programme->id }}" class="programme-checkbox">
                                             </td
                                            <td>{{ $programme->name }}</td
                                            <td>{{ $programme->code }}</td
                                            <td>
                                                <input type="number" name="capacities[]" class="form-control form-control-sm capacity-input" style="width: 100px;" placeholder="Optional" disabled>
                                             </td
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save"></i> Create Intake
                        </button>
                        <a href="{{ route('admission.intakes.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enable/disable capacity input when checkbox is checked
    $('.programme-checkbox').on('change', function() {
        var $capacityInput = $(this).closest('tr').find('.capacity-input');
        if ($(this).is(':checked')) {
            $capacityInput.prop('disabled', false);
        } else {
            $capacityInput.prop('disabled', true).val('');
        }
    });
    
    // Form validation
    $('#intakeForm').on('submit', function(e) {
        var checked = $('.programme-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one programme to assign to this intake.');
            return false;
        }
    });
});
</script>
@endsection