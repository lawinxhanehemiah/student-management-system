{{-- 
    Updated Pending Review Component with:
    - Compact design for better data density
    - Improved pagination for large datasets
    - Fixed/sticky headers for better navigation
    - Responsive design with horizontal scroll for mobile
    - Enhanced bulk actions
--}}
@extends('layouts.admission')

@section('title', 'Pending Review Applications')

@section('content')
<div class="container-fluid px-2 px-md-4">
    <!-- Page Header - More Compact -->
    <div class="page-header mb-3 mb-md-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h1 class="h4 mb-1 text-dark">Pending Review Applications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admission.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admission.applicants.index') }}">Applications</a></li>
                        <li class="breadcrumb-item active">Pending Review</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admission.applicants.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> All Applications
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards - More Compact -->
    <div class="row g-2 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-start border-3 border-primary h-100">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title small text-muted mb-1">Total Pending</div>
                            <div class="stat-number h5 mb-0 fw-bold">{{ $applications->total() }}</div>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                            <i class="fas fa-clock fa-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-start border-3 border-success h-100">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title small text-muted mb-1">This Month</div>
                            <div class="stat-number h5 mb-0 fw-bold">
                                @php
                                    $thisMonthCount = DB::table('applications')
                                        ->where('status', 'submitted')
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();
                                @endphp
                                {{ number_format($thisMonthCount) }}
                            </div>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle p-2">
                            <i class="fas fa-calendar-alt fa-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-start border-3 border-warning h-100">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title small text-muted mb-1">Avg. Days Pending</div>
                            <div class="stat-number h5 mb-0 fw-bold">
                                @php
        $avgDays = DB::table('applications')
            ->where('status', 'submitted')
            ->avg(DB::raw('DATEDIFF(NOW(), created_at)')) ?? 0;
    @endphp
    {{ intval($avgDays) }}
                            </div>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                            <i class="fas fa-calendar-day fa-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-start border-3 border-info h-100">
                <div class="card-body p-2 p-sm-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title small text-muted mb-1">Action Required</div>
                            <div class="stat-number h5 mb-0 fw-bold">
                                @php
                                    $actionRequired = DB::table('applications')
                                        ->where('status', 'submitted')
                                        ->whereDate('created_at', '<=', now()->subDays(3))
                                        ->count();
                                @endphp
                                {{ number_format($actionRequired) }}
                            </div>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle p-2">
                            <i class="fas fa-exclamation-circle fa-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table - Compact & Responsive with Pagination -->
    <div class="card">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-semibold">Applications Awaiting Review</h6>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search fa-xs"></i>
                    </button>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1 fa-xs"></i> Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item small" href="#" data-filter="today">Today</a></li>
                        <li><a class="dropdown-item small" href="#" data-filter="week">This Week</a></li>
                        <li><a class="dropdown-item small" href="#" data-filter="month">This Month</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small" href="#" data-sort="oldest">Oldest First</a></li>
                        <li><a class="dropdown-item small" href="#" data-sort="newest">Newest First</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        {{-- Table wrapper with horizontal scroll and sticky header --}}
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0" style="min-width: 800px;">
                <thead class="table-light sticky-top bg-white" style="position: sticky; top: 0; z-index: 10;">
                    <tr class="small">
                        <th width="30" class="ps-3">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>App. #</th>
                        <th>Applicant Name</th>
                        <th>Entry Level</th>
                        <th>Academic Year</th>
                        <th>Days Pending</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        @php
                            $daysPending = $application->created_at->diffInDays(now());
                            $pendingClass = $daysPending > 7 ? 'danger' : ($daysPending > 3 ? 'warning' : 'success');
                            $pendingText = $daysPending > 7 ? 'Urgent' : ($daysPending > 3 ? 'Overdue' : 'Normal');
                        @endphp
                        <tr class="align-middle small" data-created="{{ $application->created_at->toDateString() }}">
                            <td class="ps-3">
                                <input type="checkbox" class="application-checkbox form-check-input" value="{{ $application->id }}">
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $application->application_number }}</span>
                                <div class="small text-muted">{{ $application->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                        {{ strtoupper(substr($application->first_name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $application->first_name ?? 'N/A' }} {{ $application->last_name ?? '' }}</div>
                                        @if($application->phone)
                                            <div class="small text-muted">
                                                <i class="fas fa-phone-alt fa-xs me-1"></i> {{ $application->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <span class="badge bg-info">{{ $application->entry_level ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $application->academic_year ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $pendingClass }}">
    {{ intval($daysPending) }} days
    @if($daysPending > 3)
        <i class="fas fa-exclamation-circle ms-1 fa-xs"></i>
    @endif
</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admission.applicants.show', $application->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye fa-xs"></i>
                                    </a>
                                    <button class="btn btn-outline-success" title="Start Review" 
                                            onclick="startReview({{ $application->id }})">
                                        <i class="fas fa-play fa-xs"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-xs"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item small" href="#" 
                                                   onclick="quickAction('approve', {{ $application->id }})">
                                                    <i class="fas fa-check text-success me-2"></i> Approve
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item small" href="#" 
                                                   onclick="quickAction('waitlist', {{ $application->id }})">
                                                    <i class="fas fa-hourglass-half text-warning me-2"></i> Waitlist
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item small" href="#" 
                                                   onclick="quickAction('reject', {{ $application->id }})">
                                                    <i class="fas fa-times text-danger me-2"></i> Reject
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item small" href="#" 
                                                   onclick="assignToMe({{ $application->id }})">
                                                    <i class="fas fa-user-check me-2"></i> Assign to Me
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                <h6 class="text-muted mb-1">No pending applications found</h6>
                                <p class="small text-muted mb-0">All applications have been reviewed.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Enhanced Pagination for Large Data --}}
        <div class="card-footer bg-white py-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" id="bulkReviewBtn" disabled>
                        <i class="fas fa-play me-1 fa-xs"></i> <span id="bulkReviewText">Start Bulk Review</span>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" id="bulkActionsBtn" disabled>
                            <i class="fas fa-cog me-1 fa-xs"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item small" href="#" onclick="bulkAction('approve')">Approve Selected</a></li>
                            <li><a class="dropdown-item small" href="#" onclick="bulkAction('waitlist')">Waitlist Selected</a></li>
                            <li><a class="dropdown-item small" href="#" onclick="bulkAction('reject')">Reject Selected</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="#" onclick="bulkAction('assign')">Assign to Me</a></li>
                        </ul>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="small text-muted">
                        Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} 
                        of {{ $applications->total() }} entries
                    </div>
                    {{-- Per Page Selector --}}
                    <select id="perPage" class="form-select form-select-sm w-auto" style="width: auto;">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <div>
                        {{ $applications->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Compact Stats Icons */
    .stat-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    
    /* Table Sticky Header */
    .table-responsive {
        scrollbar-width: thin;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.7rem 0.5rem;
    }
    
    .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    /* Sticky header for large data scrolling */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }
    
    /* Better pagination styling */
    .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
    }
    
    .page-link {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }
    
    /* Avatar styling */
    .avatar-sm {
        width: 28px;
        height: 28px;
        font-size: 12px;
        font-weight: 600;
    }
    
    /* Badge styling */
    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    
    /* Form check */
    .form-check-input {
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Row hover effect */
    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    
    /* Loading spinner */
    .loading-spinner {
        display: inline-block;
        width: 0.8rem;
        height: 0.8rem;
        border: 2px solid rgba(0,0,0,.1);
        border-radius: 50%;
        border-top-color: #007bff;
        animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Compact cards on mobile */
    @media (max-width: 576px) {
        .stat-number {
            font-size: 1rem;
        }
        .stat-title {
            font-size: 0.7rem;
        }
        .stat-icon {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }
        .card-body {
            padding: 0.5rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

$(document).ready(function() {
    // Setup AJAX with CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // Per Page Change Handler - for large data pagination
    $('#perPage').change(function() {
        const perPage = $(this).val();
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    });

    // Select all checkbox
    $('#selectAll').change(function() {
        $('.application-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkButtons();
    });

    // Individual checkbox change
    $(document).on('change', '.application-checkbox', function() {
        updateBulkButtons();
        // Update select all state
        const total = $('.application-checkbox').length;
        const checked = $('.application-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked && total > 0);
    });

    // Update bulk buttons state
    function updateBulkButtons() {
        const checkedCount = $('.application-checkbox:checked').length;
        const hasChecked = checkedCount > 0;
        
        $('#bulkReviewBtn').prop('disabled', !hasChecked);
        $('#bulkActionsBtn').prop('disabled', !hasChecked);
        
        if (hasChecked) {
            $('#bulkReviewText').text(`Review ${checkedCount} Selected`);
        } else {
            $('#bulkReviewText').text('Start Bulk Review');
        }
    }

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });

    // Filter functionality
    $('[data-filter]').click(function(e) {
        e.preventDefault();
        const filter = $(this).data('filter');
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        
        $('tbody tr').show();
        
        if (filter === 'today') {
            $('tbody tr').each(function() {
                const created = $(this).data('created');
                if (created !== todayStr) {
                    $(this).hide();
                }
            });
        } else if (filter === 'week') {
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            $('tbody tr').each(function() {
                const created = new Date($(this).data('created'));
                if (created < weekAgo) {
                    $(this).hide();
                }
            });
        } else if (filter === 'month') {
            const monthAgo = new Date();
            monthAgo.setMonth(monthAgo.getMonth() - 1);
            $('tbody tr').each(function() {
                const created = new Date($(this).data('created'));
                if (created < monthAgo) {
                    $(this).hide();
                }
            });
        }
    });

    // Sort functionality
    $('[data-sort]').click(function(e) {
        e.preventDefault();
        const sort = $(this).data('sort');
        const rows = $('tbody tr').get();
        
        rows.sort(function(a, b) {
            const dateA = new Date($(a).data('created'));
            const dateB = new Date($(b).data('created'));
            return sort === 'oldest' ? dateA - dateB : dateB - dateA;
        });
        
        $.each(rows, function(index, row) {
            $('tbody').append(row);
        });
    });
});

function startReview(applicationId) {
    window.location.href = `/admission/applications/${applicationId}/review`;
}

function showError(title, message) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function quickAction(action, applicationId) {
    const actions = {
        'approve': { title: 'Approve Application', btnColor: '#28a745', text: 'approve' },
        'waitlist': { title: 'Waitlist Application', btnColor: '#ffc107', text: 'waitlist' },
        'reject': { title: 'Reject Application', btnColor: '#dc3545', text: 'reject' }
    };

    const actionData = actions[action];
    const isReject = action === 'reject';
    
    Swal.fire({
        title: actionData.title,
        html: `
            <div class="text-start">
                <p class="small">Please provide ${isReject ? 'a reason' : 'notes'} for this action:</p>
                <textarea id="actionNotes" class="form-control form-control-sm" rows="3" 
                          placeholder="${isReject ? 'Enter rejection reason...' : 'Enter notes (optional)...'}"></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionData.text}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: actionData.btnColor,
        preConfirm: () => {
            const notes = document.getElementById('actionNotes').value;
            if (isReject && !notes.trim()) {
                Swal.showValidationMessage('Reason is required for rejection');
                return false;
            }
            return notes;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const notes = result.value || '';
            
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `/admission/applications/${applicationId}/${action}`,
                method: 'POST',
                data: {
                    notes: notes,
                    reason: notes,
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: `Application ${action}d successfully`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to process request',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function assignToMe(applicationId) {
    Swal.fire({
        title: 'Assign to Yourself?',
        text: 'You will be responsible for reviewing this application.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, assign to me',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `/admission/applications/${applicationId}/assign`,
                method: 'POST',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Assigned!',
                        text: 'Application assigned to you successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to assign application',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function bulkAction(action) {
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

    const actionTitles = {
        'approve': { title: 'Approve Selected Applications', text: 'approve', btnColor: '#28a745', isReject: false },
        'waitlist': { title: 'Waitlist Selected Applications', text: 'waitlist', btnColor: '#ffc107', isReject: false },
        'reject': { title: 'Reject Selected Applications', text: 'reject', btnColor: '#dc3545', isReject: true },
        'assign': { title: 'Assign Selected Applications', text: 'assign to yourself', btnColor: '#0d6efd', isReject: false }
    };

    const actionData = actionTitles[action];
    
    const dialogConfig = {
        title: actionData.title,
        html: `<div class="text-start">
                <p>You are about to <strong>${actionData.text}</strong> <strong class="text-primary">${selectedIds.length}</strong> application(s).</p>
                ${actionData.isReject ? '<div class="mb-3"><label class="form-label small">Reason <span class="text-danger">*</span></label><textarea id="bulkReason" class="form-control form-control-sm" rows="2" placeholder="Enter reason..."></textarea></div>' : 
                  (action === 'assign' ? '' : '<div class="mb-3"><label class="form-label small">Notes (optional)</label><textarea id="bulkNotes" class="form-control form-control-sm" rows="2" placeholder="Enter notes..."></textarea></div>')}
                <small class="text-muted">This action cannot be undone.</small>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Yes, ${actionData.text}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: actionData.btnColor,
        preConfirm: () => {
            if (actionData.isReject) {
                const reason = document.getElementById('bulkReason')?.value;
                if (!reason?.trim()) {
                    Swal.showValidationMessage('Reason is required for rejection');
                    return false;
                }
                return { reason: reason };
            }
            if (action === 'approve' || action === 'waitlist') {
                const notes = document.getElementById('bulkNotes')?.value || '';
                return { notes: notes };
            }
            return true;
        }
    };
    
    Swal.fire(dialogConfig).then((result) => {
        if (result.isConfirmed) {
            const extraData = result.value || {};
            
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const postData = {
                action: action,
                application_ids: selectedIds,
                _token: csrfToken
            };
            
            if (actionData.isReject) {
                postData.reason = extraData.reason;
                postData.notes = extraData.reason;
            } else if (action === 'approve' || action === 'waitlist') {
                postData.notes = extraData.notes;
            }
            
            $.ajax({
                url: '/admission/applications/bulk-actions',
                method: 'POST',
                data: postData,
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: `Successfully processed ${response.count} application(s)`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to process bulk action',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Start bulk review
$('#bulkReviewBtn').click(function() {
    const selectedIds = [];
    $('.application-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length > 0) {
        sessionStorage.setItem('bulkReviewIds', JSON.stringify(selectedIds));
        window.location.href = '/admission/applications/bulk-review';
    }
});
</script>
@endpush
@endsection