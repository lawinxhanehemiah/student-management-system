{{-- resources/views/hod/dashboard.blade.php --}}
@extends('layouts.hod')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Department Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ $user->first_name }} {{ $user->last_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary py-2 px-3">
                <i class="fas fa-calendar-alt me-2"></i>
                {{ $currentAcademicYear->name ?? 'No Active Academic Year' }}
            </span>
            <span class="badge bg-success py-2 px-3">
                <i class="fas fa-building me-2"></i>
                {{ $programme->name ?? 'No Programme' }}
            </span>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card bg-gradient-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white text-opacity-75 fs-13">Total Students</span>
                            <h3 class="text-white mb-0">{{ number_format($stats['total_students']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="fas fa-users text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-white text-opacity-75">
                            <i class="fas fa-arrow-up me-1"></i>
                            {{ $stats['by_level'][3] }} final year students
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white text-opacity-75 fs-13">Active Students</span>
                            <h3 class="text-white mb-0">{{ number_format($stats['active_students']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="fas fa-user-check text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-white text-opacity-75">
                            {{ round(($stats['active_students'] / max($stats['total_students'], 1)) * 100) }}% of total
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card bg-gradient-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white text-opacity-75 fs-13">Graduating Class</span>
                            <h3 class="text-white mb-0">{{ number_format($stats['graduating']) }}</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="fas fa-graduation-cap text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-white text-opacity-75">
                            <i class="fas fa-calendar me-1"></i>
                            Expected {{ date('Y') + 1 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card bg-gradient-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-white text-opacity-75 fs-13">Gender Ratio</span>
                            <h3 class="text-white mb-0">{{ $stats['by_gender']['male'] }} : {{ $stats['by_gender']['female'] }}</h3>
                        </div>
                        <div class="icon-circle bg-white bg-opacity-25">
                            <i class="fas fa-venus-mars text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-white text-opacity-75">
                            {{ round(($stats['by_gender']['female'] / max($stats['total_students'], 1)) * 100) }}% Female
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Distribution by Level</h5>
                </div>
                <div class="card-body">
                    <canvas id="levelChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Gender Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="genderChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Student Search Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-search me-2"></i> Quick Student Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-semibold mb-2">Search Student</label>
                            <div class="position-relative">
                                <input type="text" 
                                       class="form-control" 
                                       id="studentSearchInput" 
                                       placeholder="Type registration number or name to search..."
                                       autocomplete="off">
                                <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
                                <div id="searchLoading" class="search-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Searching...
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle"></i> Search by registration number or student name
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-2">Actions</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-info" id="viewProfileBtn" disabled>
                                <i class="fas fa-user me-1"></i> Profile
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="viewHistoryBtn" disabled>
                                <i class="fas fa-history me-1"></i> History
                            </button>
                            <button type="button" class="btn btn-outline-success" id="registerCoursesBtn" disabled>
                                <i class="fas fa-edit me-1"></i> Register
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="clearanceBtn" disabled>
                                <i class="fas fa-check-circle me-1"></i> Clearance
                            </button>
                        </div>
                    </div>
                </div>
                <div id="selectedStudentInfo" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-user-graduate me-2"></i>
                        <strong>Selected Student:</strong> 
                        <span id="selectedStudentName"></span> 
                        (<span id="selectedStudentRegNo"></span>)
                        <button type="button" class="btn btn-sm btn-link" id="clearSelection">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Recent Students & Quick Actions -->
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Recently Enrolled Students</h5>
                    <a href="{{ route('hod.students.all') }}" class="btn btn-sm btn-primary">
                        View All <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Registration #</th>
                                    <th>Student Name</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentStudents as $student)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $student->registration_number }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary me-2">
                                                {{ strtoupper(substr($student->user->first_name, 0, 1)) }}{{ strtoupper(substr($student->user->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div>{{ $student->user->first_name }} {{ $student->user->last_name }}</div>
                                                <small class="text-muted">{{ $student->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Year {{ $student->current_level }}</td>
                                    <td>
                                        @if($student->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($student->status == 'graduated')
                                            <span class="badge bg-info">Graduated</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hod.students.profile', $student->id) }}" 
                                               class="btn btn-outline-info" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('hod.students.academic-history', $student->id) }}" 
                                               class="btn btn-outline-primary" title="Academic History">
                                                <i class="fas fa-history"></i>
                                            </a>
                                            <a href="{{ route('hod.students.register-courses', $student->id) }}" 
                                               class="btn btn-outline-success" title="Register Courses">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('hod.students.clearance', $student->id) }}" 
                                               class="btn btn-outline-warning" title="Clearance Status">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Data" height="100">
                                        <p class="text-muted mt-3">No students found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('hod.students.all') }}" class="btn btn-outline-primary btn-lg text-start">
                            <i class="fas fa-users me-2"></i>
                            Manage Students
                            <small class="d-block text-muted">View and manage all students</small>
                        </a>
                        
                        <a href="{{ route('hod.results.enter') }}" class="btn btn-outline-success btn-lg text-start">
                            <i class="fas fa-chart-bar me-2"></i>
                            Enter Results
                            <small class="d-block text-muted">Input student examination results</small>
                        </a>
                        
                        <a href="{{ route('hod.promotion.semester') }}" class="btn btn-outline-warning btn-lg text-start">
                            <i class="fas fa-arrow-right me-2"></i>
                            Promote Students
                            <small class="d-block text-muted">Promote students to next level</small>
                        </a>
                        
                        <a href="{{ route('hod.reports.performance') }}" class="btn btn-outline-info btn-lg text-start">
                            <i class="fas fa-file-alt me-2"></i>
                            Generate Reports
                            <small class="d-block text-muted">View performance analytics</small>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Department Info Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Department Information</h6>
                    <div class="mb-3">
                        <label class="text-muted fs-12">Programme</label>
                        <p class="fw-medium mb-0">{{ $programme->name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted fs-12">Programme Code</label>
                        <p class="fw-medium mb-0">{{ $programme->code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-muted fs-12">Duration</label>
                        <p class="fw-medium mb-0">{{ $programme->duration ?? 'N/A' }} Years</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .dashboard-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #3b8cff 0%, #6f3bff 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #495057;
    }
    .fs-12 {
        font-size: 12px;
    }
    .fs-13 {
        font-size: 13px;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* Previous styles remain... */
    
    /* Search Results Dropdown Styles */
    .position-relative {
        position: relative;
    }
    
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    
    .search-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .search-result-item.active {
        background-color: #e3f2fd;
    }
    
    .search-result-regno {
        font-weight: 600;
        color: #007bff;
    }
    
    .search-result-name {
        color: #495057;
    }
    
    .search-result-details {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    .search-loading {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
        z-index: 1000;
        color: #6c757d;
    }
    
    .no-results {
        padding: 15px;
        text-align: center;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('#studentSearch').select2({
        placeholder: 'Search student by name or registration number',
        allowClear: true
    });
    
    // Action buttons for quick search
    $('#viewProfileBtn').click(function() {
        let studentId = $('#studentSearch').val();
        if (studentId) {
            window.location.href = '{{ route("hod.students.profile", "") }}/' + studentId;
        } else {
            Swal.fire('Error', 'Please select a student first', 'error');
        }
    });
    
    $('#viewHistoryBtn').click(function() {
        let studentId = $('#studentSearch').val();
        if (studentId) {
            window.location.href = '{{ route("hod.students.academic-history", "") }}/' + studentId;
        } else {
            Swal.fire('Error', 'Please select a student first', 'error');
        }
    });
    
    $('#registerCoursesBtn').click(function() {
        let studentId = $('#studentSearch').val();
        if (studentId) {
            window.location.href = '{{ route("hod.students.register-courses", "") }}/' + studentId;
        } else {
            Swal.fire('Error', 'Please select a student first', 'error');
        }
    });
    
    $('#clearanceBtn').click(function() {
        let studentId = $('#studentSearch').val();
        if (studentId) {
            window.location.href = '{{ route("hod.students.clearance", "") }}/' + studentId;
        } else {
            Swal.fire('Error', 'Please select a student first', 'error');
        }
    });
    
    // Level Distribution Chart
    const levelCtx = document.getElementById('levelChart').getContext('2d');
    new Chart(levelCtx, {
        type: 'bar',
        data: {
            labels: ['Year 1', 'Year 2', 'Year 3', 'Year 4'],
            datasets: [{
                label: 'Number of Students',
                data: [
                    {{ $stats['by_level'][1] ?? 0 }}, 
                    {{ $stats['by_level'][2] ?? 0 }}, 
                    {{ $stats['by_level'][3] ?? 0 }},
                    {{ $stats['by_level'][4] ?? 0 }}
                ],
                backgroundColor: 'rgba(102, 126, 234, 0.5)',
                borderColor: 'rgb(102, 126, 234)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [
                    {{ $stats['by_gender']['male'] ?? 0 }}, 
                    {{ $stats['by_gender']['female'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

$(document).ready(function() {
    let searchTimeout;
    let selectedStudentId = null;
    let selectedStudentData = null;
    
    // Search input handler
    $('#studentSearchInput').on('input', function() {
        let query = $(this).val().trim();
        
        if (query.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Show loading
        $('#searchLoading').show();
        
        // Debounce search
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("hod.api.search-students") }}',
                type: 'GET',
                data: { query: query },
                success: function(response) {
                    $('#searchLoading').hide();
                    
                    if (response.success && response.students.length > 0) {
                        displaySearchResults(response.students);
                    } else {
                        $('#searchResults').html('<div class="no-results"><i class="fas fa-search"></i> No students found</div>').show();
                    }
                },
                error: function() {
                    $('#searchLoading').hide();
                    $('#searchResults').html('<div class="no-results text-danger"><i class="fas fa-exclamation-triangle"></i> Error searching students</div>').show();
                }
            });
        }, 300);
    });
    
    // Display search results
    function displaySearchResults(students) {
        let html = '';
        
        students.forEach(function(student) {
            let statusBadge = '';
            if (student.status === 'active') {
                statusBadge = '<span class="badge bg-success ms-2">Active</span>';
            } else if (student.status === 'graduated') {
                statusBadge = '<span class="badge bg-info ms-2">Graduated</span>';
            } else {
                statusBadge = '<span class="badge bg-warning ms-2">Inactive</span>';
            }
            
            html += `
                <div class="search-result-item" data-id="${student.id}" data-name="${student.name}" data-regno="${student.registration_number}" data-level="${student.current_level}" data-status="${student.status}">
                    <div>
                        <span class="search-result-regno">${student.registration_number}</span>
                        ${statusBadge}
                    </div>
                    <div class="search-result-name">${student.name}</div>
                    <div class="search-result-details">
                        Year ${student.current_level} | ${student.programme_name || 'N/A'}
                    </div>
                </div>
            `;
        });
        
        $('#searchResults').html(html).show();
        
        // Add click handlers to results
        $('.search-result-item').click(function() {
            selectStudent(
                $(this).data('id'),
                $(this).data('name'),
                $(this).data('regno'),
                $(this).data('level'),
                $(this).data('status')
            );
        });
    }
    
    // Select a student
    function selectStudent(id, name, regNo, level, status) {
        selectedStudentId = id;
        selectedStudentData = { id, name, regNo, level, status };
        
        // Update input
        $('#studentSearchInput').val(`${regNo} - ${name}`);
        
        // Hide results
        $('#searchResults').hide();
        
        // Show selected student info
        $('#selectedStudentName').text(name);
        $('#selectedStudentRegNo').text(regNo);
        $('#selectedStudentInfo').show();
        
        // Enable action buttons
        $('#viewProfileBtn, #viewHistoryBtn, #registerCoursesBtn, #clearanceBtn').prop('disabled', false);
        
        // Optional: Add visual feedback
        $('#studentSearchInput').addClass('border-success');
    }
    
    // Clear selection
    $('#clearSelection').click(function() {
        selectedStudentId = null;
        selectedStudentData = null;
        $('#studentSearchInput').val('').removeClass('border-success');
        $('#selectedStudentInfo').hide();
        $('#viewProfileBtn, #viewHistoryBtn, #registerCoursesBtn, #clearanceBtn').prop('disabled', true);
        $('#searchResults').hide();
    });
    
    // Action buttons
    $('#viewProfileBtn').click(function() {
        if (selectedStudentId) {
            window.location.href = '{{ route("hod.students.profile", "") }}/' + selectedStudentId;
        } else {
            Swal.fire('Error', 'Please search and select a student first', 'error');
        }
    });
    
    $('#viewHistoryBtn').click(function() {
        if (selectedStudentId) {
            window.location.href = '{{ route("hod.students.academic-history", "") }}/' + selectedStudentId;
        } else {
            Swal.fire('Error', 'Please search and select a student first', 'error');
        }
    });
    
    $('#registerCoursesBtn').click(function() {
        if (selectedStudentId) {
            window.location.href = '{{ route("hod.students.register-courses", "") }}/' + selectedStudentId;
        } else {
            Swal.fire('Error', 'Please search and select a student first', 'error');
        }
    });
    
    $('#clearanceBtn').click(function() {
        if (selectedStudentId) {
            window.location.href = '{{ route("hod.students.clearance", "") }}/' + selectedStudentId;
        } else {
            Swal.fire('Error', 'Please search and select a student first', 'error');
        }
    });
    
    // Close dropdown when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#studentSearchInput, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
    
    // Handle keyboard navigation
    let currentFocus = -1;
    
    $('#studentSearchInput').keydown(function(e) {
        let items = $('.search-result-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= items.length) currentFocus = 0;
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = items.length - 1;
            updateFocus(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && items[currentFocus]) {
                items[currentFocus].click();
            }
        }
    });
    
    function updateFocus(items) {
        items.removeClass('active');
        if (currentFocus >= 0 && items[currentFocus]) {
            items[currentFocus].addClass('active');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }
});
</script>
@endpush
@endsection