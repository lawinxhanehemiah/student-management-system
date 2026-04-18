@extends('layouts.admission')

@section('title', 'Rejected Applications')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Rejected Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Rejected</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i> All Applications
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Rejected</h6>
                            <h4 class="mb-0">{{ $applications->total() }}</h4>
                        </div>
                        <div class="icon-shape icon-lg bg-danger text-white rounded">
                            <i class="fas fa-times-circle"></i>
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
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h4 class="mb-0">
                                {{ DB::table('applications')
                                    ->where('status', 'rejected')
                                    ->whereMonth('rejected_at', now()->month)
                                    ->whereYear('rejected_at', now()->year)
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-warning text-white rounded">
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
                            <h6 class="text-muted mb-1">Rejection Rate</h6>
                            <h4 class="mb-0">
                                @php
                                    $total = DB::table('applications')
                                        ->whereIn('status', ['approved', 'rejected', 'waitlisted'])
                                        ->count();
                                    $rejected = DB::table('applications')
                                        ->where('status', 'rejected')
                                        ->count();
                                    $rate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;
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
            <div class="card border-left-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Appeals Received</h6>
                            <h4 class="mb-0">
                                {{ DB::table('application_appeals')
                                    ->join('applications', 'application_appeals.application_id', '=', 'applications.id')
                                    ->where('applications.status', 'rejected')
                                    ->count() }}
                            </h4>
                        </div>
                        <div class="icon-shape icon-lg bg-secondary text-white rounded">
                            <i class="fas fa-gavel"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Reasons Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Rejection Reasons</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="rejectionReasonsChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="list-group">
                                @php
                                    $reasons = DB::table('applications')
                                        ->where('status', 'rejected')
                                        ->whereNotNull('rejection_reason')
                                        ->select('rejection_reason', DB::raw('COUNT(*) as count'))
                                        ->groupBy('rejection_reason')
                                        ->orderBy('count', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @foreach($reasons as $reason)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ Str::limit($reason->rejection_reason, 40) }}</h6>
                                            <div class="progress" style="height: 6px; width: 150px;">
                                                <div class="progress-bar bg-danger" 
                                                     style="width: {{ ($reason->count / $reasons->sum('count')) * 100 }}%"></div>
                                            </div>
                                        </div>
                                        <span class="badge bg-danger rounded-pill">{{ $reason->count }}</span>
                                    </div>
                                </div>
                                @endforeach
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
            <h5 class="mb-0">Rejected Applications</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-outline-warning" onclick="exportRejected()">
                    <i class="fas fa-file-export me-2"></i> Export
                </button>
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
                            <th>Rejected On</th>
                            <th>Rejected By</th>
                            <th>Rejection Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                        <tr>
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                                <div class="small text-muted">{{ $application->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-light text-danger rounded-circle">
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
                                    {{ $application->rejected_at ? \Carbon\Carbon::parse($application->rejected_at)->format('M d, Y') : 'N/A' }}
                                </div>
                                <div class="text-muted smaller">
                                    @if($application->rejected_at)
                                        {{ \Carbon\Carbon::parse($application->rejected_at)->diffForHumans() }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    @if($application->rejected_by)
                                        User #{{ $application->rejected_by }}
                                    @else
                                        System
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($application->rejection_reason)
                                    <div class="small text-truncate" style="max-width: 200px;" 
                                         title="{{ $application->rejection_reason }}">
                                        {{ Str::limit($application->rejection_reason, 60) }}
                                    </div>
                                    <a href="#" class="small text-primary" 
                                       onclick="showReason('{{ addslashes($application->rejection_reason) }}')">
                                        View Full
                                    </a>
                                @else
                                    <span class="text-muted small">No reason provided</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admission.applicants.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success" title="Reconsider" 
                                            onclick="reconsiderApplication({{ $application->id }})">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <button class="btn btn-outline-info" title="Send Rejection Letter" 
                                            onclick="sendRejectionLetter({{ $application->id }})">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="reconsiderApplication({{ $application->id }})">
                                                    <i class="fas fa-redo text-success me-2"></i> Reconsider Application
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="sendRejectionLetter({{ $application->id }})">
                                                    <i class="fas fa-envelope text-primary me-2"></i> Send Rejection Letter
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="permanentlyDelete({{ $application->id }})">
                                                    <i class="fas fa-trash text-danger me-2"></i> Permanently Delete
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
                                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                                    <h5>No rejected applications found</h5>
                                    <p>Rejected applications will appear here.</p>
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
                    <button class="btn btn-sm btn-outline-success" onclick="bulkReconsider()">
                        <i class="fas fa-redo me-1"></i> Reconsider Selected
                    </button>
                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="bulkSendRejectionLetters()">
                        <i class="fas fa-envelope me-1"></i> Send Letters to Selected
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Initialize chart
    initializeRejectionChart();
});

function initializeRejectionChart() {
    const ctx = document.getElementById('rejectionReasonsChart').getContext('2d');
    
    // Sample data - replace with actual data from controller
    const data = {
        labels: ['Academic Requirements', 'Incomplete Documents', 'Late Application', 'Capacity Full', 'Other'],
        datasets: [{
            data: [35, 25, 20, 15, 5],
            backgroundColor: [
                '#dc3545',
                '#fd7e14',
                '#ffc107',
                '#20c997',
                '#6c757d'
            ],
            borderWidth: 1
        }]
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + '%';
                            return label;
                        }
                    }
                }
            }
        }
    });
}

function showReason(reason) {
    Swal.fire({
        title: 'Rejection Reason',
        html: `<div class="text-start p-3 bg-light rounded">${reason}</div>`,
        icon: 'info',
        confirmButtonText: 'Close',
        width: '600px'
    });
}

function reconsiderApplication(applicationId) {
    Swal.fire({
        title: 'Reconsider Application?',
        text: 'This will change the application status back to pending review.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, reconsider',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Reason for Reconsideration',
                input: 'textarea',
                inputPlaceholder: 'Why are you reconsidering this application?',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please provide a reason for reconsideration';
                    }
                }
            }).then((notesResult) => {
                if (notesResult.isConfirmed) {
                    $.ajax({
                        url: `/admission/applications/${applicationId}/reconsider`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            notes: notesResult.value
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Reconsidered!',
                                text: 'Application has been moved back to pending review',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to reconsider application',
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

function sendRejectionLetter(applicationId) {
    Swal.fire({
        title: 'Send Rejection Letter?',
        text: 'This will send the official rejection letter to the applicant.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, send letter',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admission/applications/${applicationId}/send-rejection-letter`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Letter Sent!',
                        text: 'Rejection letter has been sent successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to send letter',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function permanentlyDelete(applicationId) {
    Swal.fire({
        title: 'Permanently Delete?',
        html: `<div class="text-start">
                  <p class="text-danger"><strong>Warning: This action cannot be undone!</strong></p>
                  <p>The application and all related data will be permanently removed from the system.</p>
                  <div class="form-check mt-3">
                      <input class="form-check-input" type="checkbox" id="confirmDelete">
                      <label class="form-check-label" for="confirmDelete">
                          I understand this action is irreversible
                      </label>
                  </div>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete Permanently',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            if (!document.getElementById('confirmDelete').checked) {
                Swal.showValidationMessage('Please confirm that you understand this action is irreversible');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admission/applications/${applicationId}/permanent-delete`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Application has been permanently deleted',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete application',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function bulkReconsider() {
    Swal.fire({
        title: 'Reconsider Multiple Applications',
        text: 'How many applications do you want to reconsider?',
        input: 'number',
        inputPlaceholder: 'Enter number',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value || value < 1) {
                return 'Please enter a valid number';
            }
            if (value > 100) {
                return 'Maximum 100 applications at once';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/admission/applications/bulk-reconsider?count=${result.value}`;
        }
    });
}

function bulkSendRejectionLetters() {
    Swal.fire({
        title: 'Send Rejection Letters',
        html: `<p>Send rejection letters to all applicants?</p>
               <div class="form-check mt-3">
                   <input class="form-check-input" type="checkbox" id="includeEmail">
                   <label class="form-check-label" for="includeEmail">
                       Include email notifications
                   </label>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Send All Letters',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const includeEmail = $('#includeEmail').is(':checked');
            
            Swal.fire({
                title: 'Sending Letters...',
                text: 'Please wait while we process your request',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admission/applications/bulk-send-rejection-letters',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    include_email: includeEmail
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Complete!',
                        html: `<p>Successfully sent ${response.sent} rejection letter(s).</p>
                               ${response.failed > 0 ? 
                                 `<p class="text-danger">${response.failed} failed to send.</p>` : ''}`,
                        icon: 'success',
                        confirmButtonText: 'OK'
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

function exportRejected() {
    Swal.fire({
        title: 'Export Rejected Applications',
        text: 'Select export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel (.xlsx)',
        cancelButtonText: 'Cancel',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('/admission/applications/rejected/export/excel', '_blank');
        } else if (result.dismiss === Swal.DismissReason.deny) {
            window.open('/admission/applications/rejected/export/csv', '_blank');
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

.chart-container {
    position: relative;
}

.table tbody tr:hover {
    background-color: rgba(220, 53, 69, 0.05);
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.smaller {
    font-size: 0.75rem;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endpush
@endsection