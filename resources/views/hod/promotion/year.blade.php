{{-- resources/views/hod/promotion/year.blade.php --}}
@extends('layouts.hod')

@section('title', 'Promote by Level')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Promote by Level</h4>
            <p class="text-muted mb-0">Review and promote students to next academic year</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary py-2 px-3">
                <i class="fas fa-calendar-alt me-2"></i>
                {{ $academicYear->name ?? 'Academic Year' }}
            </span>
            <span class="badge bg-success py-2 px-3">
                <i class="fas fa-building me-2"></i>
                {{ $programme->name ?? 'Programme' }}
            </span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Total Students</h6>
                            <h2 class="mb-0">{{ $students->total() }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Eligible</h6>
                            <h2 class="mb-0">{{ $stats['eligible'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Ineligible</h6>
                            <h2 class="mb-0">{{ $stats['ineligible'] }}</h2>
                        </div>
                        <i class="fas fa-times-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Probation</h6>
                            <h2 class="mb-0">{{ $stats['probation'] }}</h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('hod.promotion.year') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Level</label>
                        <select name="level" class="form-select form-select-sm">
                            <option value="">All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ ($filters['level'] ?? '') == $level ? 'selected' : '' }}>
                                    Year {{ $level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Semester</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester }}" {{ ($filters['semester'] ?? '') == $semester ? 'selected' : '' }}>
                                    Semester {{ $semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="eligibility_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ ($filters['eligibility_status'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Reg No or Name..." 
                                   value="{{ $filters['search'] ?? '' }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                        @if(!empty($filters['search']) || !empty($filters['level']) || !empty($filters['semester']) || !empty($filters['eligibility_status']))
                            <a href="{{ route('hod.promotion.year') }}" class="btn btn-outline-danger btn-sm ms-2">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            Showing {{ $students->firstItem() ?? 0 }} - {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} students
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                <i class="fas fa-check-double me-1"></i> Select All Eligible
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">
                <i class="fas fa-times me-1"></i> Clear All
            </button>
            <button type="button" class="btn btn-sm btn-success" id="promoteBtn" disabled>
                <i class="fas fa-arrow-up me-1"></i> Promote (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Level / Sem</th>
                            <th>CGPA</th>
                            <th>Sem 1</th>
                            <th>Sem 2</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th width="70"></th>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                            @php
                                $eligibility = $eligibleStudents[$student->id] ?? [];
                                $isEligible = isset($eligibility['eligible']) && $eligibility['eligible'];
                                $cgpa = $eligibility['cgpa'] ?? 0;
                                $feeCleared = $eligibility['fee_cleared'] ?? false;
                                $conditions = $eligibility['conditions'] ?? [];
                                $cgpaClass = $cgpa >= 2.0 ? 'success' : ($cgpa >= 1.5 ? 'warning' : 'danger');
                                $cgpaText = $cgpa >= 2.0 ? 'Good' : ($cgpa >= 1.5 ? 'Pass' : 'Probation');
                            @endphp
                            <tr class="{{ $isEligible ? '' : 'table-warning' }}">
                                <td class="text-center">
                                    @if($isEligible)
                                        <input type="checkbox" name="student_ids[]" 
                                               value="{{ $student->id }}" 
                                               class="student-checkbox">
                                    @else
                                        <i class="fas fa-ban text-muted"></i>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $student->registration_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <span class="fw-semibold small">
                                                {{ strtoupper(substr($student->user->first_name ?? 'S', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div>{{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}</div>
                                            <small class="text-muted">{{ $student->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">Y{{ $student->current_level }}</span>
                                    <span class="badge bg-light text-dark ms-1">S{{ $student->current_semester }}</span>
                                </td>
                                <td>
                                    @if($cgpa > 0)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-{{ $cgpaClass }}">
                                                {{ number_format($cgpa, 2) }}
                                            </span>
                                            <small class="text-muted">{{ $cgpaText }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->semester_1_completed)
                                        <i class="fas fa-check-circle text-success"></i> Done
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i> Pending
                                    @endif
                                </td>
                                <td>
                                    @if($student->semester_2_completed)
                                        <i class="fas fa-check-circle text-success"></i> Done
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i> Pending
                                    @endif
                                </td>
                                <td>
                                    @if($feeCleared)
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-check-circle me-1"></i> Cleared
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Outstanding
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($isEligible)
                                        <span class="badge bg-success">Eligible</span>
                                    @else
                                        <span class="badge bg-danger">Ineligible</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-link text-secondary p-1 view-student" data-id="{{ $student->id }}" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$isEligible && count($conditions) > 0)
                                            <button type="button" class="btn btn-link text-danger p-1 view-conditions" 
                                                    data-conditions='{{ json_encode($conditions) }}'
                                                    data-name="{{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}"
                                                    title="Why Ineligible?">
                                                <i class="fas fa-question-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">No students found matching your criteria</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($students->hasPages())
            <div class="card-footer bg-white">
                {{ $students->withQueryString()->links() }}
            </div>
            @endif
        </div>

        <!-- Hidden Promotion Form -->
        <form id="promotionForm" action="{{ route('hod.promotion.process-year') }}" method="POST" style="display: none;">
            @csrf
            <div id="selectedIdsContainer"></div>
        </form>
    </div>

    <!-- Conditions Modal -->
    <div class="modal fade" id="conditionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Promotion Conditions Not Met</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul id="conditionsList" class="list-unstyled"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printReportBtn">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        let selectedIds = new Set();
        
        function updateSelectedCount() {
            selectedIds.clear();
            $('.student-checkbox:checked').each(function() {
                selectedIds.add($(this).val());
            });
            
            let count = selectedIds.size;
            $('#selectedCount').text(count);
            $('#promoteBtn').prop('disabled', count === 0);
            
            $('#selectedIdsContainer').empty();
            selectedIds.forEach(id => {
                $('#selectedIdsContainer').append(`<input type="hidden" name="student_ids[]" value="${id}">`);
            });
            
            let total = $('.student-checkbox:visible').length;
            let checked = $('.student-checkbox:visible:checked').length;
            $('#selectAllCheckbox').prop('checked', checked === total && total > 0);
            $('#selectAllCheckbox').prop('indeterminate', checked > 0 && checked < total);
        }
        
        $('#selectAllCheckbox').change(function() {
            $('.student-checkbox:visible').prop('checked', $(this).is(':checked'));
            updateSelectedCount();
        });
        
        $('#selectAllBtn').click(function() {
            $('.student-checkbox:visible').prop('checked', true);
            updateSelectedCount();
        });
        
        $('#clearAllBtn').click(function() {
            $('.student-checkbox').prop('checked', false);
            updateSelectedCount();
        });
        
        $(document).on('change', '.student-checkbox', updateSelectedCount);
        
        $(document).on('click', '.view-student', function() {
            let id = $(this).data('id');
            window.location.href = '{{ route("hod.students.profile", "") }}/' + id;
        });
        
        $(document).on('click', '.view-conditions', function() {
            let conditions = $(this).data('conditions');
            let studentName = $(this).data('name');
            
            $('#modalStudentName').text(studentName);
            
            let list = $('#conditionsList');
            list.empty();
            
            if (conditions && conditions.length > 0) {
                conditions.forEach(function(condition) {
                    let icon = 'fa-times-circle';
                    if (condition.includes('GPA')) icon = 'fa-chart-line';
                    else if (condition.includes('Fee')) icon = 'fa-money-bill-wave';
                    else if (condition.includes('results')) icon = 'fa-file-alt';
                    else if (condition.includes('semester')) icon = 'fa-calendar-check';
                    
                    list.append(`<li class="mb-2 pb-2 border-bottom"><i class="fas ${icon} text-danger me-2"></i> ${condition}</li>`);
                });
            } else {
                list.append('<li class="text-muted">No specific conditions found</li>');
            }
            
            $('#conditionsModal').modal('show');
        });
        
        $('#printReportBtn').click(function() {
            let studentName = $('#modalStudentName').text();
            let conditions = $('#conditionsList').html();
            
            let printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Promotion Report - ${studentName}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 30px; }
                        @media print { .no-print { display: none; } }
                        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #dee2e6; padding-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <div class="report-header">
                        <h3>Promotion Eligibility Report</h3>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                        <h5>Student: ${studentName}</h5>
                    </div>
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">Conditions Not Met</div>
                        <div class="card-body"><ul>${conditions}</ul></div>
                    </div>
                    <div class="text-center mt-4 no-print">
                        <button class="btn btn-primary" onclick="window.print()">Print</button>
                        <button class="btn btn-secondary" onclick="window.close()">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
        
        $('#promoteBtn').click(function() {
            let count = selectedIds.size;
            if (count === 0) {
                Swal.fire('Error', 'Please select at least one student', 'error');
                return;
            }
            
            Swal.fire({
                title: 'Confirm Promotion',
                html: `<p>Are you sure you want to promote <strong>${count}</strong> student(s) to the next level?</p>
                       <div class="alert alert-warning mt-3">
                           <i class="fas fa-exclamation-triangle"></i> This action will update academic records.
                       </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Promote',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#promotionForm').submit();
                }
            });
        });
        
        // Auto-submit filters
        $('#filterForm select').change(function() {
            $('#filterForm').submit();
        });
        
        updateSelectedCount();
    });
    </script>
    @endpush

    @push('styles')
    <style>
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    </style>
    @endpush
@endsection