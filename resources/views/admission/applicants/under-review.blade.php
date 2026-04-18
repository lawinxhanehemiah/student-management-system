@extends('layouts.admission')

@section('title', 'Under Review Applications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Under Review Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Under Review</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i> All Applications
                </a>
                <a href="{{ route('admission.applicants.pending-review') }}" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-clock me-2"></i> Pending Review
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Under Review</h6>
                            <h4 class="mb-0">{{ $applications->total() }}</h4>
                        </div>
                        <div class="icon-shape icon-lg bg-warning text-white rounded">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Assigned to Me</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'under_review')
                                    ->where('reviewed_by', Auth::id())
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-info text-white rounded">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Avg. Review Time</h6>
                            <h4 class="mb-0">
                                @php
                                    $avgHours = DB::table('applications')
                                        ->where('status', 'under_review')
                                        ->avg(DB::raw('TIMESTAMPDIFF(HOUR, updated_at, NOW())')) ?? 0;
                                    $display = $avgHours < 24 ? round($avgHours) . 'h' : round($avgHours/24) . 'd';
                                @endphp
                                {{ $display }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-primary text-white rounded">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Overdue Reviews</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'under_review')
                                    ->whereDate('updated_at', '<=', now()->subDays(2))
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-danger text-white rounded">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Applications Currently Under Review</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search applications..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select form-select-sm" style="width: 150px;" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="assigned">Assigned to Me</option>
                    <option value="overdue">Overdue</option>
                    <option value="recent">Recent (Today)</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Application #</th>
                            <th>Applicant Name</th>
                            <th>National ID</th>
                            <th>Entry Level</th>
                            <th>Academic Year</th>
                            <th>Reviewer</th>
                            <th>Review Started</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                        @php
                            $reviewDuration = $application->updated_at ? $application->updated_at->diffInHours(now()) : 0;
                            $isOverdue = $reviewDuration > 48; // 2 days
                            $isAssignedToMe = $application->reviewed_by == Auth::id();
                        @endphp
                        <tr class="@if($isOverdue) table-danger @elseif($isAssignedToMe) table-info @endif">
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                                <div class="small text-muted">{{ $application->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-light text-primary rounded-circle">
                                            {{ strtoupper(substr($application->first_name ?? 'A', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $application->first_name ?? 'N/A' }} {{ $application->last_name ?? '' }}</div>
                                        <div class="small text-muted">
                                            <i class="fas fa-phone-alt me-1"></i> {{ $application->phone ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $application->national_id ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $application->entry_level }}</span>
                            </td>
                            <td>{{ $application->academic_year ?? 'N/A' }}</td>
                            <td>
                                @if($application->reviewed_by)
                                    <span class="badge bg-success">Assigned</span>
                                    <div class="small text-muted mt-1">
                                        User #{{ $application->reviewed_by }}
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    {{ $application->updated_at->format('M d, Y') }}
                                </div>
                                <div class="text-muted smaller">
                                    {{ $application->updated_at->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                @if($isOverdue)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Overdue
                                    </span>
                                @elseif($reviewDuration > 24)
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i> {{ $reviewDuration }}h
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="fas fa-clock me-1"></i> {{ $reviewDuration }}h
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admission.applicants.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="Continue Review">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($isAssignedToMe)
                                    <button class="btn btn-outline-success" title="Complete Review" 
                                            onclick="completeReview({{ $application->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-outline-info" title="Take Over Review" 
                                            onclick="takeOverReview({{ $application->id }})">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    @endif
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($isAssignedToMe)
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="completeReview({{ $application->id }})">
                                                    <i class="fas fa-check text-success me-2"></i> Complete Review
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="unassignReview({{ $application->id }})">
                                                    <i class="fas fa-user-times text-warning me-2"></i> Unassign
                                                </a>
                                            </li>
                                            @else
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="takeOverReview({{ $application->id }})">
                                                    <i class="fas fa-user-plus text-info me-2"></i> Take Over
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="addReviewNotes({{ $application->id }})">
                                                    <i class="fas fa-sticky-note text-primary me-2"></i> Add Notes
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="escalateReview({{ $application->id }})">
                                                    <i class="fas fa-level-up-alt text-danger me-2"></i> Escalate
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <h5>No applications under review</h5>
                                    <p>All applications have been processed or are pending initial review.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }} entries
                </div>
                <div>
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filter by status
    $('#filterStatus').change(function() {
        const filter = $(this).val();
        
        if (filter === 'assigned') {
            $('tbody tr').show().filter(function() {
                return !$(this).hasClass('table-info');
            }).hide();
        } else if (filter === 'overdue') {
            $('tbody tr').show().filter(function() {
                return !$(this).hasClass('table-danger');
            }).hide();
        } else if (filter === 'recent') {
            $('tbody tr').show().filter(function() {
                const dateText = $(this).find('td:nth-child(7) .small').text();
                return !dateText.includes({{ now()->format('M d, Y') }});
            }).hide();
        } else {
            $('tbody tr').show();
        }
    });
});

function takeOverReview(applicationId) {
    Swal.fire({
        title: 'Take Over Review?',
        text: 'You will be assigned as the reviewer for this application.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, take over',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admission/applications/${applicationId}/take-over`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Assigned!',
                        text: 'You are now the reviewer for this application',
                        icon: 'success',
                        confirmButtonText: 'Continue Review'
                    }).then(() => {
                        window.location.href = `/admission/applications/${applicationId}/review`;
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to take over review',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function completeReview(applicationId) {
    Swal.fire({
        title: 'Complete Review',
        text: 'Please provide your final decision and notes:',
        icon: 'question',
        input: 'select',
        inputOptions: {
            'approve': 'Approve Application',
            'waitlist': 'Waitlist Application',
            'reject': 'Reject Application'
        },
        inputPlaceholder: 'Select decision',
        showCancelButton: true,
        confirmButtonText: 'Submit Decision',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to select a decision';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const decision = result.value;
            
            Swal.fire({
                title: 'Review Notes',
                text: 'Please provide notes for your decision:',
                input: 'textarea',
                inputPlaceholder: 'Enter review notes here...',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Back',
                inputValidator: (value) => {
                    if (!value && decision === 'reject') {
                        return 'Reason is required for rejection';
                    }
                    return null;
                }
            }).then((notesResult) => {
                if (notesResult.isConfirmed) {
                    const notes = notesResult.value;
                    
                    $.ajax({
                        url: `/admission/applications/${applicationId}/complete-review`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            decision: decision,
                            notes: notes
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Review Completed!',
                                text: `Application has been ${decision}d successfully`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to complete review',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    });
}

function unassignReview(applicationId) {
    Swal.fire({
        title: 'Unassign Review?',
        text: 'This will remove you as the reviewer for this application.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, unassign',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admission/applications/${applicationId}/unassign`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Unassigned!',
                        text: 'You are no longer the reviewer for this application',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to unassign review',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function addReviewNotes(applicationId) {
    Swal.fire({
        title: 'Add Review Notes',
        input: 'textarea',
        inputPlaceholder: 'Enter your review notes here...',
        showCancelButton: true,
        confirmButtonText: 'Save Notes',
        cancelButtonText: 'Cancel',
        inputAttributes: {
            'rows': 6
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: `/admission/applications/${applicationId}/add-notes`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    notes: result.value
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Notes Saved!',
                        text: 'Review notes have been saved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to save notes',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function escalateReview(applicationId) {
    Swal.fire({
        title: 'Escalate Review',
        text: 'This will escalate the application to a senior reviewer.',
        input: 'select',
        inputOptions: {
            'quality': 'Quality Assurance Issue',
            'complex': 'Complex Case',
            'dispute': 'Applicant Dispute',
            'other': 'Other Reason'
        },
        inputPlaceholder: 'Select escalation reason',
        showCancelButton: true,
        confirmButtonText: 'Escalate',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please select an escalation reason';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Escalation Notes',
                input: 'textarea',
                inputPlaceholder: 'Provide details for escalation...',
                showCancelButton: true,
                confirmButtonText: 'Submit Escalation',
                cancelButtonText: 'Back'
            }).then((notesResult) => {
                if (notesResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/escalate`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: result.value,
                            notes: notesResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Escalated!',
                                text: 'Application has been escalated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to escalate',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    });
}
</script>

<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

.table-danger:hover {
    background-color: rgba(220, 53, 69, 0.05) !important;
}

.table-info:hover {
    background-color: rgba(23, 162, 184, 0.05) !important;
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.smaller {
    font-size: 0.75rem;
}
</style>
@endpush
@endsection