{{-- resources/views/admission/applications/index.blade.php --}}
@extends('layouts.admission')

@section('title', 'Manage Applications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Applications Management</h3>
                    <div class="card-tools">
    <a href="{{ route('admission.officer.applications.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Application
    </a>
</div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="all">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="academic_year" class="form-control">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ request('academic_year') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email or application number..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Applications Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                   
                                    <th>Applicant</th>
                                    <th>Academic Year</th>
                                    <th>Intake</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                <tr>
                                    
                                    <td>
                                        <strong>{{ $app->applicant_name }}</strong><br>
                                        <small>{{ $app->applicant_email }}</small>
                                    </td>
                                    <td>{{ $app->academic_year_name }}</td>
                                    <td>{{ $app->intake }}</td>
                                    <td>
                                        @if($app->status == 'draft')
                                            <span class="badge badge-warning">Draft</span>
                                        @elseif($app->status == 'submitted')
                                            <span class="badge badge-info">Submitted</span>
                                        @elseif($app->status == 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($app->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $app->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $completed = 0;
                                            if($app->step_basic_completed) $completed++;
                                            if($app->step_personal_completed) $completed++;
                                            if($app->step_contact_completed) $completed++;
                                            if($app->step_next_of_kin_completed) $completed++;
                                            if($app->step_academic_completed) $completed++;
                                            if($app->step_programs_completed) $completed++;
                                            $percent = round(($completed / 6) * 100);
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
                                        </div>
                                        <small>{{ $completed }}/6 steps completed</small>
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($app->created_at)) }}</td>
                                    <td>
                                        <!-- Show/View Button -->
                                        <a href="{{ route('admission.officer.applications.show', $app->id) }}" class="btn btn-sm btn-info" title="View Application">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Edit Button -->
                                        <a href="{{ route('admission.officer.applications.edit', $app->id) }}" class="btn btn-sm btn-primary" title="Edit Application">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Delete Button (only for draft) -->
                                        @if($app->status == 'draft')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $app->id }})" title="Delete Application">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-2 d-block"></i>
                                        No applications found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $applications->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Application Modal -->
<div class="modal fade" id="createApplicationModal" tabindex="-1" role="dialog" aria-labelledby="createApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="createApplicationForm" action="{{ route('admission.officer.applications.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createApplicationModalLabel">Create New Application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Search/Lookup Applicant Section -->
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        Enter applicant's email or phone number. If they exist, their details will be auto-filled.
                    </div>
                    
                    <div class="form-group">
                        <label>Search Applicant (Email or Phone) *</label>
                        <div class="input-group">
                            <input type="text" id="applicant_search" class="form-control" placeholder="Enter email address or phone number...">
                            <div class="input-group-append">
                                <button type="button" id="searchApplicantBtn" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Search by email (e.g., john@example.com) or phone (e.g., 2557XXXXXXXX)
                        </small>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="searchResults" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-user-check"></i> 
                            <span id="searchResultText"></span>
                            <button type="button" class="close" onclick="clearSearch()">&times;</button>
                        </div>
                    </div>
                    
                    <!-- Hidden fields for applicant data -->
                    <input type="hidden" name="user_id" id="selected_user_id">
                    <input type="hidden" name="is_new_applicant" id="is_new_applicant" value="0">
                    
                    <hr>
                    
                    <!-- Applicant Details Form -->
                    <h6 class="mt-3">Applicant Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="password_field">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank for auto-generated">
                                <small class="form-text text-muted">Only for new applicants</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Application Details -->
                    <h6 class="mt-3">Application Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Academic Year *</label>
                                <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Intake *</label>
                                <select name="intake" id="intake" class="form-control" required>
                                    <option value="March">March Intake</option>
                                    <option value="September">September Intake</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Entry Level *</label>
                                <select name="entry_level" id="entry_level" class="form-control" required>
                                    <option value="CSEE">CSEE (Form Four)</option>
                                    <option value="ACSEE">ACSEE (Form Six)</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Degree">Degree</option>
                                    <option value="Mature">Mature Entry</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Study Mode</label>
                                <select name="study_mode" class="form-control">
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Distance">Distance Learning</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    // Reset form
    $('#createApplicationForm')[0].reset();
    $('#selected_user_id').val('');
    $('#is_new_applicant').val('0');
    $('#searchResults').hide();
    $('#applicant_search').val('');
    $('#password_field').show();
    
    // Enable all fields for editing
    $('#first_name, #last_name, #email, #phone, #gender').prop('readonly', false);
    
    $('#createApplicationModal').modal('show');
}

function clearSearch() {
    $('#searchResults').hide();
    $('#applicant_search').val('');
    $('#selected_user_id').val('');
    $('#is_new_applicant').val('0');
    $('#first_name, #last_name, #email, #phone, #gender').prop('readonly', false).val('');
    $('#password_field').show();
}

// Search for existing applicant
$('#searchApplicantBtn').click(function() {
    let searchValue = $('#applicant_search').val().trim();
    
    if (!searchValue) {
        alert('Please enter email or phone number to search');
        return;
    }
    
    let btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
    
    // FIXED: Added correct route URL
    $.ajax({
        url: '{{ route("admission.officer.applicants.search") }}',
        method: 'GET',
        data: { q: searchValue },
        success: function(response) {
            btn.prop('disabled', false).html('<i class="fas fa-search"></i> Search');
            
            if (response.found && response.applicant) {
                let applicant = response.applicant;
                $('#selected_user_id').val(applicant.id);
                $('#is_new_applicant').val('0');
                $('#first_name').val(applicant.first_name).prop('readonly', true);
                $('#last_name').val(applicant.last_name).prop('readonly', true);
                $('#email').val(applicant.email).prop('readonly', true);
                $('#phone').val(applicant.phone || '').prop('readonly', true);
                $('#gender').val(applicant.gender || '').prop('readonly', true);
                $('#password_field').hide();
                
                $('#searchResultText').html(`<strong>${applicant.first_name} ${applicant.last_name}</strong> found! (${applicant.email})`);
                $('#searchResults').show();
                
                if (response.has_application) {
                    $('#searchResultText').append(`<br><span class="text-warning">⚠️ This applicant already has an application for ${response.existing_academic_year}. You can still create a new one.</span>`);
                }
            } else {
                $('#selected_user_id').val('');
                $('#is_new_applicant').val('1');
                $('#first_name').val('').prop('readonly', false);
                $('#last_name').val('').prop('readonly', false);
                $('#email').val(searchValue.includes('@') ? searchValue : '').prop('readonly', false);
                $('#phone').val(!searchValue.includes('@') ? searchValue : '').prop('readonly', false);
                $('#gender').val('').prop('readonly', false);
                $('#password_field').show();
                
                $('#searchResultText').html(`No existing applicant found with "<strong>${searchValue}</strong>". Fill in details below to create new applicant.`);
                $('#searchResults').show();
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-search"></i> Search');
            console.log('Search error:', xhr);
            alert('Search failed. Please try again.');
        }
    });
});

// Form validation before submit
$('#createApplicationForm').on('submit', function(e) {
    let isNew = $('#is_new_applicant').val() === '1';
    let userId = $('#selected_user_id').val();
    
    if (!isNew && !userId) {
        e.preventDefault();
        alert('Please search and select an existing applicant first.');
        return false;
    }
    
    if (!$('#first_name').val() || !$('#last_name').val() || !$('#email').val()) {
        e.preventDefault();
        alert('Please fill in all required fields (First Name, Last Name, Email)');
        return false;
    }
    
    if (!$('#academic_year_id').val() || !$('#intake').val() || !$('#entry_level').val()) {
        e.preventDefault();
        alert('Please fill in all application details');
        return false;
    }
});

function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admission-officer/applications/' + id;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection