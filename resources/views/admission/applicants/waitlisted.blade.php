@extends('layouts.admission')

@section('title', 'Waitlisted Applications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Waitlisted Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Waitlisted</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i> All Applications
                </a>
                <button class="btn btn-warning ms-2" onclick="processWaitlist()">
                    <i class="fas fa-cogs me-2"></i> Process Waitlist
                </button>
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
                            <h6 class="text-muted mb-1">Total Waitlisted</h6>
                            <h4 class="mb-0">{{ $applications->total() }}</h4>
                        </div>
                        <div class="icon-shape icon-lg bg-warning text-white rounded">
                            <i class="fas fa-pause-circle"></i>
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
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'waitlisted')
                                    ->whereMonth('waitlisted_at', now()->month)
                                    ->whereYear('waitlisted_at', now()->year)
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-info text-white rounded">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Eligible for Upgrade</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'waitlisted')
                                    ->whereDate('waitlisted_at', '<=', now()->subDays(14))
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-success text-white rounded">
                            <i class="fas fa-arrow-up"></i>
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
                            <h6 class="text-muted mb-1">Expiring Soon</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'waitlisted')
                                    ->whereDate('waitlisted_at', '<=', now()->subDays(28))
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

    <!-- Waitlist Management -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Waitlist Management</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Available Spots</h5>
                            <h2 class="text-primary mb-0">
                                {{ DB::table('programmes')
                                    ->where('is_active', 1)
                                    ->sum('available_seats') ?? 0 }}
                            </h2>
                            <p class="text-muted mb-0">Across all programs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Waitlist Priority</h5>
                            <div class="btn-group mt-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="sortByDate()">
                                    <i class="fas fa-calendar me-1"></i> By Date
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="sortByScore()">
                                    <i class="fas fa-star me-1"></i> By Score
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="sortByProgram()">
                                    <i class="fas fa-graduation-cap me-1"></i> By Program
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Quick Actions</h5>
                            <div class="btn-group mt-2">
                                <button class="btn btn-sm btn-success" onclick="upgradeEligible()">
                                    <i class="fas fa-arrow-up me-1"></i> Upgrade Eligible
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="expireOld()">
                                    <i class="fas fa-times me-1"></i> Expire Old
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Waitlisted Applications</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select form-select-sm" style="width: 150px;" id="filterPriority">
                    <option value="">All Priorities</option>
                    <option value="high">High Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="low">Low Priority</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Application #</th>
                            <th>Applicant Name</th>
                            <th>National ID</th>
                            <th>Entry Level</th>
                            <th>Academic Year</th>
                            <th>Waitlisted On</th>
                            <th>Days Waitlisted</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                        @php
                            $daysWaitlisted = $application->waitlisted_at ? 
                                \Carbon\Carbon::parse($application->waitlisted_at)->diffInDays(now()) : 0;
                            
                            if ($daysWaitlisted >= 28) {
                                $priorityClass = 'danger';
                                $priorityText = 'Expired';
                                $rowClass = 'table-danger';
                            } elseif ($daysWaitlisted >= 14) {
                                $priorityClass = 'success';
                                $priorityText = 'High';
                                $rowClass = 'table-success';
                            } elseif ($daysWaitlisted >= 7) {
                                $priorityClass = 'warning';
                                $priorityText = 'Medium';
                                $rowClass = 'table-warning';
                            } else {
                                $priorityClass = 'secondary';
                                $priorityText = 'Low';
                                $rowClass = '';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input application-checkbox" type="checkbox" value="{{ $application->id }}">
                                </div>
                            </td>
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                                <div class="small text-muted">{{ $application->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-light text-warning rounded-circle">
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
                                <div class="small">
                                    {{ $application->waitlisted_at ? \Carbon\Carbon::parse($application->waitlisted_at)->format('M d, Y') : 'N/A' }}
                                </div>
                                <div class="text-muted smaller">
                                    @if($application->waitlisted_at)
                                        {{ \Carbon\Carbon::parse($application->waitlisted_at)->diffForHumans() }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($daysWaitlisted >= 28)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $daysWaitlisted }} days
                                    </span>
                                @elseif($daysWaitlisted >= 21)
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i> {{ $daysWaitlisted }} days
                                    </span>
                                @else
                                    <span class="badge bg-info">{{ $daysWaitlisted }} days</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $priorityClass }}">{{ $priorityText }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admission.applicants.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($daysWaitlisted < 28)
                                    <button class="btn btn-outline-success" title="Upgrade to Approved" 
                                            onclick="upgradeApplication({{ $application->id }})">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-info" title="Send Waitlist Update" 
                                            onclick="sendWaitlistUpdate({{ $application->id }})">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($daysWaitlisted < 28)
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="upgradeApplication({{ $application->id }})">
                                                    <i class="fas fa-arrow-up text-success me-2"></i> Upgrade to Approved
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="extendWaitlist({{ $application->id }})">
                                                    <i class="fas fa-clock text-warning me-2"></i> Extend Waitlist
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="sendWaitlistUpdate({{ $application->id }})">
                                                    <i class="fas fa-envelope text-primary me-2"></i> Send Update
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="moveToRejected({{ $application->id }})">
                                                    <i class="fas fa-times-circle text-danger me-2"></i> Move to Rejected
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-pause-circle fa-3x mb-3"></i>
                                    <h5>No waitlisted applications found</h5>
                                    <p>Waitlisted applications will appear here.</p>
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
                <div>
                    <button class="btn btn-sm btn-success" id="bulkUpgradeBtn" disabled>
                        <i class="fas fa-arrow-up me-1"></i> Upgrade Selected
                    </button>
                    <button class="btn btn-sm btn-outline-primary ms-2" id="bulkNotifyBtn" disabled 
                            onclick="bulkNotify()">
                        <i class="fas fa-envelope me-1"></i> Notify Selected
                    </button>
                    <button class="btn btn-sm btn-outline-danger ms-2" id="bulkRejectBtn" disabled 
                            onclick="bulkRejectFromWaitlist()">
                        <i class="fas fa-times me-1"></i> Reject Selected
                    </button>
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
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.application-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });

    // Individual checkbox change
    $('.application-checkbox').change(function() {
        updateBulkButtons();
    });

    // Update bulk buttons state
    function updateBulkButtons() {
        const checkedCount = $('.application-checkbox:checked').length;
        const hasChecked = checkedCount > 0;
        
        $('#bulkUpgradeBtn').prop('disabled', !hasChecked);
        $('#bulkNotifyBtn').prop('disabled', !hasChecked);
        $('#bulkRejectBtn').prop('disabled', !hasChecked);
        
        if (hasChecked) {
            $('#bulkUpgradeBtn').html(`<i class="fas fa-arrow-up me-1"></i> Upgrade ${checkedCount} Selected`);
        }
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filter by priority
    $('#filterPriority').change(function() {
        const filter = $(this).val();
        
        if (filter === 'high') {
            $('tbody tr').show().filter(function() {
                return !$(this).hasClass('table-success');
            }).hide();
        } else if (filter === 'medium') {
            $('tbody tr').show().filter(function() {
                return !$(this).hasClass('table-warning');
            }).hide();
        } else if (filter === 'low') {
            $('tbody tr').show().filter(function() {
                return $(this).hasClass('table-success') || 
                       $(this).hasClass('table-warning') || 
                       $(this).hasClass('table-danger');
            }).hide();
        } else if (filter === 'expired') {
            $('tbody tr').show().filter(function() {
                return !$(this).hasClass('table-danger');
            }).hide();
        } else {
            $('tbody tr').show();
        }
    });
});

function upgradeApplication(applicationId) {
    Swal.fire({
        title: 'Upgrade to Approved?',
        text: 'This will move the application from waitlist to approved status.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, upgrade',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Upgrade Notes',
                input: 'textarea',
                inputPlaceholder: 'Add any notes about this upgrade...',
                showCancelButton: true,
                confirmButtonText: 'Complete Upgrade',
                cancelButtonText: 'Cancel'
            }).then((notesResult) => {
                if (notesResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/upgrade-from-waitlist`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            notes: notesResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Upgraded!',
                                text: 'Application has been approved successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to upgrade application',
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

function extendWaitlist(applicationId) {
    Swal.fire({
        title: 'Extend Waitlist Period',
        text: 'How many more days should this application remain on waitlist?',
        input: 'select',
        inputOptions: {
            '7': '7 days (1 week)',
            '14': '14 days (2 weeks)',
            '30': '30 days (1 month)',
            '60': '60 days (2 months)'
        },
        inputPlaceholder: 'Select extension period',
        showCancelButton: true,
        confirmButtonText: 'Extend',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please select an extension period';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const days = result.value;
            
            Swal.fire({
                title: 'Extension Notes',
                input: 'textarea',
                inputPlaceholder: 'Why are you extending this waitlist?',
                showCancelButton: true,
                confirmButtonText: 'Confirm Extension',
                cancelButtonText: 'Back'
            }).then((notesResult) => {
                if (notesResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/extend-waitlist`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            days: days,
                            notes: notesResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Extended!',
                                text: `Waitlist period extended by ${days} days`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to extend waitlist',
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

function sendWaitlistUpdate(applicationId) {
    Swal.fire({
        title: 'Send Waitlist Update',
        input: 'select',
        inputOptions: {
            'status': 'Status Update',
            'position': 'Position Update',
            'extension': 'Extension Notification',
            'upgrade': 'Potential Upgrade Notification'
        },
        inputPlaceholder: 'Select update type',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please select an update type';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const updateType = result.value;
            
            Swal.fire({
                title: 'Update Message',
                input: 'textarea',
                inputPlaceholder: 'Enter the message to send...',
                showCancelButton: true,
                confirmButtonText: 'Send Update',
                cancelButtonText: 'Back'
            }).then((messageResult) => {
                if (messageResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/send-waitlist-update`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            update_type: updateType,
                            message: messageResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Update Sent!',
                                text: 'Waitlist update has been sent to the applicant',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to send update',
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

function moveToRejected(applicationId) {
    Swal.fire({
        title: 'Move to Rejected?',
        text: 'This will move the application from waitlist to rejected status.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, move to rejected',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Rejection Reason',
                input: 'textarea',
                inputPlaceholder: 'Why are you rejecting this application?',
                showCancelButton: true,
                confirmButtonText: 'Confirm Rejection',
                cancelButtonText: 'Back',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please provide a rejection reason';
                    }
                }
            }).then((reasonResult) => {
                if (reasonResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/reject-from-waitlist`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reason: reasonResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Rejected!',
                                text: 'Application has been moved to rejected status',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to reject application',
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

function processWaitlist() {
    Swal.fire({
        title: 'Process Waitlist',
        html: `<div class="text-start">
                  <p>Automatically process waitlisted applications based on:</p>
                  <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" id="upgradeEligible" checked>
                      <label class="form-check-label" for="upgradeEligible">
                          Upgrade applications waitlisted for 14+ days
                      </label>
                  </div>
                  <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" id="expireOld" checked>
                      <label class="form-check-label" for="expireOld">
                          Expire applications waitlisted for 28+ days
                      </label>
                  </div>
                  <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" id="notifyAll">
                      <label class="form-check-label" for="notifyAll">
                          Send notifications to affected applicants
                      </label>
                  </div>
                  <div class="alert alert-info">
                      <i class="fas fa-info-circle me-2"></i>
                      This process will run in the background and may take several minutes.
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Start Processing',
        cancelButtonText: 'Cancel',
        width: '500px'
    }).then((result) => {
        if (result.isConfirmed) {
            const upgradeEligible = $('#upgradeEligible').is(':checked');
            const expireOld = $('#expireOld').is(':checked');
            const notifyAll = $('#notifyAll').is(':checked');
            
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process the waitlist',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/process-waitlist',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    upgrade_eligible: upgradeEligible,
                    expire_old: expireOld,
                    notify_all: notifyAll
                },
                success: function(response) {
                    let html = `<div class="text-start">
                                   <p><strong>Waitlist Processing Complete</strong></p>
                                   <p>Upgraded: ${response.upgraded || 0} applications</p>
                                   <p>Expired: ${response.expired || 0} applications</p>
                                   <p>Notifications sent: ${response.notified || 0}</p>`;
                    
                    if (response.errors && response.errors.length > 0) {
                        html += `<p class="text-danger mt-3"><strong>Errors:</strong></p>
                                 <ul class="small">`;
                        response.errors.forEach(error => {
                            html += `<li>${error}</li>`;
                        });
                        html += `</ul>`;
                    }
                    
                    html += `</div>`;
                    
                    Swal.fire({
                        title: 'Complete!',
                        html: html,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to process waitlist',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function upgradeEligible() {
    Swal.fire({
        title: 'Upgrade Eligible Applications',
        text: 'This will upgrade all applications waitlisted for 14+ days.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Upgrade All Eligible',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Upgrading eligible applications',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/upgrade-eligible',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        text: `Successfully upgraded ${response.upgraded} applications`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to upgrade applications',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function expireOld() {
    Swal.fire({
        title: 'Expire Old Applications',
        text: 'This will expire all applications waitlisted for 28+ days.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Expire All Old',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Expiring old waitlisted applications',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/expire-old',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        text: `Successfully expired ${response.expired} applications`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to expire applications',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function sortByDate() {
    window.location.href = window.location.pathname + '?sort=date';
}

function sortByScore() {
    window.location.href = window.location.pathname + '?sort=score';
}

function sortByProgram() {
    window.location.href = window.location.pathname + '?sort=program';
}

function bulkNotify() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Notify Selected Applicants',
        input: 'textarea',
        inputPlaceholder: 'Enter notification message...',
        showCancelButton: true,
        confirmButtonText: 'Send Notifications',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please enter a message';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sending...',
                text: 'Sending notifications to selected applicants',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/bulk-waitlist-notify',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    application_ids: selectedIds,
                    message: result.value
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        text: `Notifications sent to ${response.sent} applicants`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to send notifications',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function bulkRejectFromWaitlist() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Reject Selected from Waitlist',
        html: `<div class="text-start">
                  <p>You are about to reject <strong>${selectedIds.length}</strong> applications from waitlist.</p>
                  <label class="form-label mt-2">Reason for rejection:</label>
                  <textarea class="form-control" id="bulkRejectReason" rows="3" placeholder="Enter reason..."></textarea>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject Selected',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            const reason = document.getElementById('bulkRejectReason').value;
            if (!reason) {
                Swal.showValidationMessage('Please provide a rejection reason');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;
            
            Swal.fire({
                title: 'Processing...',
                text: 'Rejecting selected applications',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/bulk-reject-from-waitlist',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    application_ids: selectedIds,
                    reason: reason
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        text: `${response.rejected} applications have been rejected`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to reject applications',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Bulk upgrade
$('#bulkUpgradeBtn').click(function() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Upgrade Selected Applications',
        html: `<div class="text-start">
                  <p>Upgrade <strong>${selectedIds.length}</strong> applications from waitlist to approved.</p>
                  <label class="form-label mt-2">Upgrade notes (optional):</label>
                  <textarea class="form-control" id="bulkUpgradeNotes" rows="3" placeholder="Enter notes..."></textarea>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Upgrade Selected',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const notes = document.getElementById('bulkUpgradeNotes').value;
            
            Swal.fire({
                title: 'Processing...',
                text: 'Upgrading selected applications',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/bulk-upgrade-from-waitlist',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    application_ids: selectedIds,
                    notes: notes
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        text: `${response.upgraded} applications have been upgraded`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to upgrade applications',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
});
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
    background-color: rgba(255, 193, 7, 0.05) !important;
}

.table-success:hover {
    background-color: rgba(40, 167, 69, 0.05) !important;
}

.table-warning:hover {
    background-color: rgba(255, 193, 7, 0.05) !important;
}

.table-danger:hover {
    background-color: rgba(220, 53, 69, 0.05) !important;
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.smaller {
    font-size: 0.75rem;
}

.form-check-input:checked {
    background-color: #ffc107;
    border-color: #ffc107;
}
</style>
@endpush
@endsection