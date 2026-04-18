@extends('layouts.admission')

@section('title', 'Approved Applications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Approved Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Approved</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i> All Applications
                </a>
                <button class="btn btn-success ms-2" onclick="exportApproved()">
                    <i class="fas fa-file-export me-2"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Approved</h6>
                            <h4 class="mb-0">{{ $applications->total() }}</h4>
                        </div>
                        <div class="icon-shape icon-lg bg-success text-white rounded">
                            <i class="fas fa-check-circle"></i>
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
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'approved')
                                    ->whereMonth('approved_at', now()->month)
                                    ->whereYear('approved_at', now()->year)
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-primary text-white rounded">
                            <i class="fas fa-calendar-alt"></i>
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
                            <h6 class="text-muted mb-1">Approval Rate</h6>
                            <h4 class="mb-0">
                                @php
                                    $total = DB::table('applications')
                                        ->whereIn('status', ['approved', 'rejected', 'waitlisted'])
                                        ->count();
                                    $approved = DB::table('applications')
                                        ->where('status', 'approved')
                                        ->count();
                                    $rate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
                                @endphp
                                {{ $rate }}%
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-info text-white rounded">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Admission</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'approved')
                                    ->whereNull('admission_letter_sent_at')
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-warning text-white rounded">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Program</label>
                    <select class="form-select form-select-sm" id="filterProgram">
                        <option value="">All Programs</option>
                        @php
                            $programs = DB::table('programmes')
                                ->where('is_active', 1)
                                ->orderBy('name')
                                ->get();
                        @endphp
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entry Level</label>
                    <select class="form-select form-select-sm" id="filterLevel">
                        <option value="">All Levels</option>
                        <option value="CSEE">CSEE</option>
                        <option value="ACSEE">ACSEE</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Degree">Degree</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <button class="btn btn-sm btn-primary" onclick="applyFilters()">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="resetFilters()">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Approved Applications List</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-outline-success" onclick="sendAdmissionLetters()">
                    <i class="fas fa-envelope me-1"></i> Send Admission Letters
                </button>
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
                            <th>Program</th>
                            <th>Entry Level</th>
                            <th>Approved On</th>
                            <th>Approved By</th>
                            <th>Admission Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                        <tr data-app-id="{{ $application->id }}">
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
                                        <div class="avatar-title bg-light text-success rounded-circle">
                                            {{ strtoupper(substr($application->first_name ?? 'A', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $application->first_name ?? 'N/A' }} {{ $application->last_name ?? '' }}</div>
                                        <div class="small text-muted">
                                            <i class="fas fa-id-card me-1"></i> {{ $application->national_id ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $application->program_name ?? 'N/A' }}</div>
                                @if($application->program_code)
                                    <div class="small text-muted">{{ $application->program_code }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $application->entry_level }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    {{ $application->approved_at ? \Carbon\Carbon::parse($application->approved_at)->format('M d, Y') : 'N/A' }}
                                </div>
                                <div class="text-muted smaller">
                                    @if($application->approved_at)
                                        {{ \Carbon\Carbon::parse($application->approved_at)->diffForHumans() }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    @if($application->approved_by)
                                        User #{{ $application->approved_by }}
                                    @else
                                        System
                                    @endif
                                </div>
                                @if($application->approval_notes)
                                    <a href="#" class="small text-primary" 
                                       onclick="showNotes('{{ addslashes($application->approval_notes) }}')">
                                        View Notes
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($application->admission_letter_sent_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Letter Sent
                                    </span>
                                    <div class="small text-muted mt-1">
                                        {{ \Carbon\Carbon::parse($application->admission_letter_sent_at)->format('M d') }}
                                    </div>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admission.applicants.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$application->admission_letter_sent_at)
                                    <a href="{{ route('admission.applicants.send-letter-form', $application->id) }}" 
                                       class="btn btn-outline-success" title="Send Admission Letter">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    @else
                                    <button class="btn btn-outline-info" title="Resend Letter" 
                                            onclick="resendLetter({{ $application->id }})">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('admission.applicants.generate-letter', $application->id) }}" 
                                       class="btn btn-outline-warning" title="Generate Offer Letter" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if(!$application->admission_letter_sent_at)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admission.applicants.send-letter-form', $application->id) }}">
                                                    <i class="fas fa-envelope text-success me-2"></i> Send Admission Letter
                                                </a>
                                            </li>
                                            @else
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="resendLetter({{ $application->id }})">
                                                    <i class="fas fa-redo text-info me-2"></i> Resend Letter
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admission.applicants.generate-letter', $application->id) }}" target="_blank">
                                                    <i class="fas fa-file-pdf text-danger me-2"></i> Generate PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admission.applicants.preview-letter', $application->id) }}" target="_blank">
                                                    <i class="fas fa-eye text-primary me-2"></i> Preview Letter
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   onclick="revokeApproval({{ $application->id }})">
                                                    <i class="fas fa-times-circle me-2"></i> Revoke Approval
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
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h5>No approved applications found</h5>
                                    <p>Approved applications will appear here.</p>
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
                    <button class="btn btn-sm btn-success" id="bulkSendBtn" disabled>
                        <i class="fas fa-envelope me-1"></i> Send Letters to Selected
                    </button>
                    <button class="btn btn-sm btn-outline-danger ms-2" id="bulkRevokeBtn" disabled 
                            onclick="bulkRevoke()">
                        <i class="fas fa-times-circle me-1"></i> Revoke Selected
                    </button>
                    <button class="btn btn-sm btn-outline-primary ms-2" id="bulkDownloadBtn" disabled 
                            onclick="bulkDownloadLetters()">
                        <i class="fas fa-download me-1"></i> Download Selected
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
        
        $('#bulkSendBtn').prop('disabled', !hasChecked);
        $('#bulkRevokeBtn').prop('disabled', !hasChecked);
        $('#bulkDownloadBtn').prop('disabled', !hasChecked);
        
        if (hasChecked) {
            $('#bulkSendBtn').html(`<i class="fas fa-envelope me-1"></i> Send ${checkedCount} Letters`);
            $('#bulkDownloadBtn').html(`<i class="fas fa-download me-1"></i> Download ${checkedCount}`);
        } else {
            $('#bulkSendBtn').html(`<i class="fas fa-envelope me-1"></i> Send Letters to Selected`);
            $('#bulkDownloadBtn').html(`<i class="fas fa-download me-1"></i> Download Selected`);
        }
    }

    // Search functionality
    $('#searchInput').on('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
});

function performSearch() {
    const value = $('#searchInput').val().toLowerCase();
    if (value.trim() === '') {
        $('tbody tr').show();
        return;
    }
    
    $('tbody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(value) > -1);
    });
}

function showNotes(notes) {
    Swal.fire({
        title: 'Approval Notes',
        html: `<div class="text-start p-3 bg-light rounded">${notes}</div>`,
        icon: 'info',
        confirmButtonText: 'Close',
        width: '600px'
    });
}

// Function to open send letter form (redirects to new page)
function sendLetter(applicationId) {
    window.location.href = `/admission/applicants/${applicationId}/send-letter-form`;
}

function resendLetter(applicationId) {
    Swal.fire({
        title: 'Resend Admission Letter?',
        html: `<div class="text-start">
                  <p>This will resend the admission letter to the applicant.</p>
                  <div class="form-check mt-3">
                      <input class="form-check-input" type="checkbox" id="resendWithEmail" checked>
                      <label class="form-check-label" for="resendWithEmail">
                          <i class="fas fa-envelope text-primary me-1"></i> Send email notification
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" id="resendWithSms">
                      <label class="form-check-label" for="resendWithSms">
                          <i class="fas fa-sms text-info me-1"></i> Send SMS notification
                      </label>
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Resend',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#17a2b8'
    }).then((result) => {
        if (result.isConfirmed) {
            const sendEmail = $('#resendWithEmail').is(':checked');
            const sendSms = $('#resendWithSms').is(':checked');
            
            Swal.fire({
                title: 'Sending...',
                text: 'Please wait while we resend the letter',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admission/applicants/${applicationId}/resend-letter`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    send_email: sendEmail,
                    send_sms: sendSms
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Letter Resent!',
                        html: `<p>Admission letter has been resent successfully.</p>
                               ${sendEmail ? '<p class="text-success"><i class="fas fa-check-circle me-1"></i> Email sent</p>' : ''}
                               ${sendSms ? '<p class="text-info"><i class="fas fa-check-circle me-1"></i> SMS sent</p>' : ''}`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to resend letter',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function revokeApproval(applicationId) {
    Swal.fire({
        title: 'Revoke Approval?',
        html: `<div class="text-start">
                  <p>This will change the application status back to pending review.</p>
                  <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                  <label class="form-label mt-2">Reason for revocation:</label>
                  <textarea class="form-control" id="revocationReason" rows="3" placeholder="Enter reason..."></textarea>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, revoke',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            const reason = document.getElementById('revocationReason').value;
            if (!reason) {
                Swal.showValidationMessage('Please provide a reason for revocation');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;
            
            $.ajax({
                url: `/admission/applicants/${applicationId}/revoke`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    reason: reason
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Approval Revoked!',
                        text: 'Application status has been changed to pending review',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to revoke approval',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function bulkRevoke() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'No Selection',
            text: 'Please select at least one application',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Revoke Multiple Approvals?',
        html: `<div class="text-start">
                  <p>You are about to revoke approval for <strong>${selectedIds.length}</strong> application(s).</p>
                  <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                  <label class="form-label mt-2">Reason for revocation:</label>
                  <textarea class="form-control" id="bulkRevocationReason" rows="3" placeholder="Enter reason..."></textarea>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, revoke all',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            const reason = document.getElementById('bulkRevocationReason').value;
            if (!reason) {
                Swal.showValidationMessage('Please provide a reason for revocation');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;
            
            Swal.fire({
                title: 'Processing...',
                text: 'Revoking approvals',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applicants/bulk-revoke',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    application_ids: selectedIds,
                    reason: reason
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Revocations Complete!',
                        html: `<p>Successfully revoked ${response.count} application(s).</p>
                               <p class="text-muted">Reason: ${reason}</p>`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to revoke approvals',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function sendAdmissionLetters() {
    const pendingCount = {{ DB::table('applications')
        ->where('status', 'approved')
        ->whereNull('admission_letter_sent_at')
        ->count() }};
    
    if (pendingCount === 0) {
        Swal.fire({
            title: 'No Pending Letters',
            text: 'All admission letters have been sent',
            icon: 'info',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Send All Pending Letters?',
        html: `<div class="text-start">
                  <p>This will send admission letters to <strong>${pendingCount}</strong> applicants.</p>
                  <div class="form-check mt-3">
                      <input class="form-check-input" type="checkbox" id="sendEmail" checked>
                      <label class="form-check-label" for="sendEmail">
                          <i class="fas fa-envelope me-1"></i> Send email notifications
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" id="sendSms">
                      <label class="form-check-label" for="sendSms">
                          <i class="fas fa-sms me-1"></i> Send SMS notifications
                      </label>
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, send all',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            const sendEmail = $('#sendEmail').is(':checked');
            const sendSms = $('#sendSms').is(':checked');
            
            Swal.fire({
                title: 'Sending Letters...',
                html: `<p>Sending admission letters to ${pendingCount} applicants...</p>
                       <div class="progress mt-3" style="height: 20px;">
                           <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                role="progressbar" style="width: 0%"></div>
                       </div>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applicants/send-all-letters',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    send_email: sendEmail,
                    send_sms: sendSms
                },
                success: function(response) {
                    let successMessage = `<p>Successfully sent ${response.sent} admission letter(s).</p>`;
                    
                    if (response.failed > 0) {
                        successMessage += `<p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> 
                                           Failed to send ${response.failed} letter(s).</p>`;
                    }
                    
                    if (sendEmail) {
                        successMessage += `<p class="text-success"><i class="fas fa-check-circle me-1"></i> Emails sent</p>`;
                    }
                    
                    if (sendSms) {
                        successMessage += `<p class="text-info"><i class="fas fa-check-circle me-1"></i> SMS sent</p>`;
                    }
                    
                    Swal.fire({
                        title: 'Letters Sent!',
                        html: successMessage,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to send letters',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function exportApproved() {
    Swal.fire({
        title: 'Export Approved Applications',
        html: `<div class="text-start">
                  <p>Select export format for approved applications:</p>
                  <div class="form-check">
                      <input class="form-check-input" type="radio" name="exportFormat" id="exportExcel" value="excel" checked>
                      <label class="form-check-label" for="exportExcel">
                          <i class="fas fa-file-excel text-success me-2"></i> Excel (.xlsx)
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="radio" name="exportFormat" id="exportPdf" value="pdf">
                      <label class="form-check-label" for="exportPdf">
                          <i class="fas fa-file-pdf text-danger me-2"></i> PDF Report
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="radio" name="exportFormat" id="exportCsv" value="csv">
                      <label class="form-check-label" for="exportCsv">
                          <i class="fas fa-file-csv text-primary me-2"></i> CSV File
                      </label>
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Export',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const format = $('input[name="exportFormat"]:checked').val();
            return format;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const format = result.value;
            
            let url = '';
            switch(format) {
                case 'excel':
                    url = '/admission/applicants/approved/export/excel';
                    break;
                case 'pdf':
                    url = '/admission/applicants/approved/export/pdf';
                    break;
                case 'csv':
                    url = '/admission/applicants/approved/export/csv';
                    break;
            }
            
            if (url) {
                window.open(url, '_blank');
            }
        }
    });
}

function applyFilters() {
    const program = $('#filterProgram').val();
    const level = $('#filterLevel').val();
    const dateFrom = $('#filterDateFrom').val();
    const dateTo = $('#filterDateTo').val();
    
    let query = '?';
    if (program) query += `program=${program}&`;
    if (level) query += `level=${level}&`;
    if (dateFrom) query += `date_from=${dateFrom}&`;
    if (dateTo) query += `date_to=${dateTo}`;
    
    // Remove trailing & if exists
    if (query.endsWith('&')) {
        query = query.slice(0, -1);
    }
    
    window.location.href = window.location.pathname + query;
}

function resetFilters() {
    $('#filterProgram').val('');
    $('#filterLevel').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    window.location.href = window.location.pathname;
}

// Bulk send letters
$('#bulkSendBtn').click(function() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Send Admission Letters',
        html: `<div class="text-start">
                  <p>You have selected <strong>${selectedIds.length}</strong> applicant(s).</p>
                  <p>How would you like to proceed?</p>
                  <div class="form-check mt-3">
                      <input class="form-check-input" type="radio" name="sendOption" id="sendBulk" value="bulk" checked>
                      <label class="form-check-label" for="sendBulk">
                          <i class="fas fa-paper-plane me-2"></i> Send bulk letters (all at once)
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="radio" name="sendOption" id="sendIndividual" value="individual">
                      <label class="form-check-label" for="sendIndividual">
                          <i class="fas fa-user-edit me-2"></i> Send individual letters (customize each)
                      </label>
                  </div>
                  <div class="mt-3" id="bulkOptions">
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="bulkSendEmail" checked>
                          <label class="form-check-label" for="bulkSendEmail">
                              <i class="fas fa-envelope me-1"></i> Send email notifications
                          </label>
                      </div>
                      <div class="form-check mt-1">
                          <input class="form-check-input" type="checkbox" id="bulkSendSms">
                          <label class="form-check-label" for="bulkSendSms">
                              <i class="fas fa-sms me-1"></i> Send SMS notifications
                          </label>
                      </div>
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const option = $('input[name="sendOption"]:checked').val();
            return option;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const option = result.value;
            
            if (option === 'bulk') {
                // Bulk send via AJAX
                const sendEmail = $('#bulkSendEmail').is(':checked');
                const sendSms = $('#bulkSendSms').is(':checked');
                
                Swal.fire({
                    title: 'Sending Bulk Letters...',
                    html: `<p>Sending admission letters to ${selectedIds.length} selected applicants...</p>
                           <div class="progress mt-3" style="height: 20px;">
                               <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                    role="progressbar" style="width: 0%"></div>
                           </div>`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/admission/applicants/bulk-send-letters',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        application_ids: selectedIds,
                        send_email: sendEmail,
                        send_sms: sendSms
                    },
                    success: function(response) {
                        let successMessage = `<p>Successfully processed ${response.sent} letter(s).</p>`;
                        
                        if (response.failed > 0) {
                            successMessage += `<p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> 
                                               ${response.failed} failed to send.</p>`;
                        }
                        
                        if (sendEmail) {
                            successMessage += `<p class="text-success"><i class="fas fa-check-circle me-1"></i> Emails sent</p>`;
                        }
                        
                        if (sendSms) {
                            successMessage += `<p class="text-info"><i class="fas fa-check-circle me-1"></i> SMS sent</p>`;
                        }
                        
                        Swal.fire({
                            title: 'Complete!',
                            html: successMessage,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to send letters',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            } else {
                // Individual send - redirect to first selected
                const firstId = selectedIds[0];
                window.location.href = `/admission/applicants/${firstId}/send-letter-form`;
            }
        }
    });
});

function bulkDownloadLetters() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Download Admission Letters',
        html: `<div class="text-start">
                  <p>Download admission letters for <strong>${selectedIds.length}</strong> selected applicant(s).</p>
                  <div class="form-check mt-3">
                      <input class="form-check-input" type="radio" name="downloadOption" id="downloadZip" value="zip" checked>
                      <label class="form-check-label" for="downloadZip">
                          <i class="fas fa-file-archive me-2"></i> ZIP file (all letters in one file)
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="radio" name="downloadOption" id="downloadSeparate" value="separate">
                      <label class="form-check-label" for="downloadSeparate">
                          <i class="fas fa-file-pdf me-2"></i> Separate PDF files
                      </label>
                  </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Download',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const option = $('input[name="downloadOption"]:checked').val();
            return option;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const option = result.value;
            
            if (option === 'zip') {
                // Create a form to submit multiple IDs
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admission/applicants/bulk-download-zip';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // Add application IDs
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'application_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            } else {
                // Download individual files (first one as example)
                const firstId = selectedIds[0];
                window.open(`/admission/applicants/${firstId}/generate-letter`, '_blank');
            }
        }
    });
}

// Add this to show/hide bulk options based on selection
$('input[name="sendOption"]').change(function() {
    if ($(this).val() === 'bulk') {
        $('#bulkOptions').show();
    } else {
        $('#bulkOptions').hide();
    }
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
    background-color: rgba(40, 167, 69, 0.05);
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
    font-weight: 500;
}

.smaller {
    font-size: 0.75rem;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.dropdown-menu {
    min-width: 200px;
}

.progress-bar {
    transition: width 0.3s ease;
}

.swal2-popup {
    border-radius: 10px;
}

.swal2-title {
    font-size: 1.25rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group {
        flex-wrap: wrap;
    }
    
    .btn-group-sm .btn {
        margin-bottom: 2px;
    }
    
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .card-header .input-group {
        width: 100% !important;
        margin-top: 10px;
    }
}
</style>
@endpush
@endsection