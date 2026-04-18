@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-3">
    {{-- Compact Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-bold text-primary mb-0">
                <i class="fas fa-users me-1"></i>
                Users
            </h6>
            <small class="text-muted">{{ $totalUsers }} total users</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.users.create') }}" 
               class="btn btn-sm btn-primary px-3"
               title="Add New User">
                <i class="fas fa-plus me-1"></i> Add
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 py-2" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <small class="fw-medium">{{ session('success') }}</small>
        </div>
        <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Stats Cards - New Design --}}
    <div class="row g-3 mb-4">
        {{-- Total Users --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">TOTAL USERS</div>
                            <div class="stat-number">{{ number_format($totalUsers) }}</div>
                            <small class="text-muted">
                                All registered users
                            </small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Students --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">STUDENTS</div>
                            <div class="stat-number">{{ number_format($studentCount) }}</div>
                            <small class="text-muted">
                                Enrolled students
                            </small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Staff --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-info">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">STAFF</div>
                            <div class="stat-number">{{ number_format($staffCount) }}</div>
                            <small class="text-muted">
                                Teaching & non-teaching
                            </small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Applicants --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">APPLICANTS</div>
                            <div class="stat-number">{{ number_format($applicantCount) }}</div>
                            <small class="text-muted">
                                Pending applications
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Box - Compact --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('superadmin.users.index') }}" id="searchForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <div>
                            <small class="text-muted mb-1 d-block">Search</small>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-search text-muted fa-xs"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control form-control-sm border-start-0" 
                                       placeholder="Name, email..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div>
                            <small class="text-muted mb-1 d-block">Role</small>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div>
                            <small class="text-muted mb-1 d-block">Type</small>
                            <select name="user_type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="applicant" {{ request('user_type') == 'applicant' ? 'selected' : '' }}>Applicant</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div>
                            <small class="text-muted mb-1 d-block">Status</small>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div>
                            <small class="text-muted mb-1 d-block">Show</small>
                            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('superadmin.users.index') }}" 
                               class="btn btn-sm btn-outline-secondary" 
                               title="Reset">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table - Compact --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr class="table-light">
                            <th class="ps-3 py-2" style="width: 30%">User</th>
                            <th class="py-2" style="width: 20%">Contact</th>
                            <th class="py-2" style="width: 10%">Gender</th>
                            <th class="py-2" style="width: 10%">Type</th>
                            <th class="py-2" style="width: 10%">Status</th>
                            <th class="py-2" style="width: 10%">Date</th>
                            <th class="text-end pe-3 py-2" style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="border-top">
                            {{-- User Info --}}
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                         alt="{{ $user->first_name }}" 
                                         class="rounded-circle me-2"
                                         width="32" height="32">
                                    @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-muted fa-xs"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium text-truncate" style="max-width: 150px;">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <small class="text-muted d-block">
                                            {{ $user->registration_number ?? $user->email }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td>
                                <div class="text-truncate" style="max-width: 120px;">
                                    <small>{{ $user->email }}</small>
                                </div>
                                @if($user->phone)
                                <div>
                                    <small class="text-muted">{{ $user->phone }}</small>
                                </div>
                                @endif
                            </td>

                            {{-- Gender --}}
                            <td>
                                @if(!empty($user->gender))
                                    @php
                                        $g = strtolower(trim($user->gender));
                                        $isMale = in_array($g, ['male', 'm', '1', 'man']);
                                        $isFemale = in_array($g, ['female', 'f', '2', 'woman']);
                                    @endphp
                                    
                                    @if($isMale)
                                    <span class="badge bg-primary" style="font-size: 0.75em;">Male</span>
                                    @elseif($isFemale)
                                    <span class="badge bg-pink" style="font-size: 0.75em; background-color: #e83e8c;">Female</span>
                                    @else
                                    <span class="badge bg-secondary" style="font-size: 0.75em;">{{ ucfirst($g) }}</span>
                                    @endif
                                @else
                                <span class="badge bg-light text-dark border" style="font-size: 0.75em;">
                                    N/A
                                </span>
                                @endif
                            </td>

                            {{-- User Type --}}
                            <td>
                                <span class="badge bg-secondary" style="font-size: 0.75em;">
                                    {{ ucfirst($user->user_type) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($user->status == 'active')
                                <span class="badge bg-success" style="font-size: 0.75em;">
                                    Active
                                </span>
                                @elseif($user->status == 'inactive')
                                <span class="badge bg-secondary" style="font-size: 0.75em;">
                                    Inactive
                                </span>
                                @else
                                <span class="badge bg-danger" style="font-size: 0.75em;">
                                    Suspended
                                </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at->format('d/m/y') }}
                                </small>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('superadmin.users.show', $user->id) }}" 
                                       class="btn btn-sm btn-outline-info px-2"
                                       title="View">
                                        <i class="fas fa-eye fa-xs"></i>
                                    </a>
                                    
                                    <a href="{{ route('superadmin.users.edit', $user->id) }}"
                                       class="btn btn-sm btn-outline-primary px-2"
                                       title="Edit">
                                        <i class="fas fa-edit fa-xs"></i>
                                    </a>

                                    @if($user->status == 'active')
                                    <form method="POST" 
                                          action="{{ route('superadmin.users.update-status', $user->id) }}"
                                          class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="inactive">
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-warning px-2"
                                                title="Deactivate">
                                            <i class="fas fa-toggle-off fa-xs"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" 
                                          action="{{ route('superadmin.users.update-status', $user->id) }}"
                                          class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-success px-2"
                                                title="Activate">
                                            <i class="fas fa-toggle-on fa-xs"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger px-2 delete-btn"
                                            title="Delete"
                                            data-name="{{ $user->first_name }} {{ $user->last_name }}"
                                            data-url="{{ route('superadmin.users.destroy', $user->id) }}">
                                        <i class="fas fa-trash fa-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-user-slash fa-lg mb-2"></i>
                                    <p class="mb-0">No users found</p>
                                    <small>Try different search terms</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Compact Pagination --}}
            @if($users->hasPages())
            <div class="border-top">
                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-filter me-1 fa-sm"></i>
                            Showing <span class="fw-bold">{{ $users->firstItem() }}-{{ $users->lastItem() }}</span> of {{ $users->total() }}
                        </small>
                    </div>
                    
                    <div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                @if ($users->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent text-muted py-1 px-2">
                                            <i class="fas fa-chevron-left fa-xs"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link border-0 bg-transparent text-primary py-1 px-2" 
                                           href="{{ $users->previousPageUrl() }}"
                                           title="Previous">
                                            <i class="fas fa-chevron-left fa-xs"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link border-0 bg-primary py-1 px-2" style="font-size: 0.75rem;">
                                                {{ $page }}
                                            </span>
                                        </li>
                                    @elseif ($page == 1 || $page == $users->lastPage() || ($page >= $users->currentPage() - 1 && $page <= $users->currentPage() + 1))
                                        <li class="page-item">
                                            <a class="page-link border-0 bg-transparent text-dark py-1 px-2" 
                                               href="{{ $url }}" 
                                               style="font-size: 0.75rem;">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($users->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link border-0 bg-transparent text-primary py-1 px-2" 
                                           href="{{ $users->nextPageUrl() }}"
                                           title="Next">
                                            <i class="fas fa-chevron-right fa-xs"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 bg-transparent text-muted py-1 px-2">
                                            <i class="fas fa-chevron-right fa-xs"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Quick Export --}}
    <div class="mt-3 d-flex justify-content-end gap-2">
        <button class="btn btn-sm btn-outline-success" onclick="exportData('excel')">
            <i class="fas fa-file-excel me-1"></i> Excel
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="exportData('pdf')">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </button>
    </div>
</div>

{{-- Delete Confirmation --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                </div>
                <h6 class="fw-bold mb-2">Delete User?</h6>
                <p class="text-muted small mb-3" id="deleteUserName"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Stats Cards Design */
.dashboard-stat-card {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.dashboard-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

/* Table styling */
.table-sm td, .table-sm th {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04) !important;
}

/* Pagination styling */
.pagination-sm .page-link {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
    border-radius: 4px;
}

.pagination-sm .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: 500;
}

/* Badge styling */
.badge {
    font-weight: 500;
    padding: 0.25em 0.6em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-number {
        font-size: 1.25rem;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<script>
// Delete confirmation
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const userName = this.getAttribute('data-name');
        const url = this.getAttribute('data-url');
        
        document.getElementById('deleteUserName').textContent = 
            `Delete ${userName}? This cannot be undone.`;
        document.getElementById('deleteForm').action = url;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });
});

// Export function
function exportData(format) {
    const form = document.getElementById('searchForm');
    const originalAction = form.action;
    
    form.action = "{{ route('superadmin.users.export') }}?format=" + format;
    form.submit();
    form.action = originalAction;
}

// Auto submit some filters
document.querySelectorAll('select[name="role"], select[name="user_type"], select[name="status"]').forEach(select => {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endsection