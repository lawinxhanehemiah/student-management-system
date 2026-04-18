@extends('layouts.admission')

@section('content')
<div class="container-fluid px-2 px-md-4">
    <!-- Page Header - Compact -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 mb-md-4 gap-2">
        <h1 class="h4 mb-0 text-gray-800">
            <i class="feather-users me-2"></i> Registered Students
        </h1>
        <div>
            <a href="{{ route('admission.students.register') }}" class="btn btn-sm btn-primary me-2">
                <i class="feather-user-plus me-1"></i> New Registration
            </a>
            <a href="{{ route('admission.students.export', 'csv') }}" class="btn btn-sm btn-success">
                <i class="feather-download me-1"></i> Export
            </a>
        </div>
    </div>

    <!-- Filters Card - Compact -->
    <div class="card shadow mb-3">
        <div class="card-header py-2 bg-white">
            <h6 class="m-0 font-weight-bold text-primary small">
                <i class="feather-filter me-1"></i> Filter Students
            </h6>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admission.students.index') }}" id="filterForm">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold mb-1">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Name, Reg No, Email, Phone..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Programme</label>
                        <select name="programme_id" class="form-select form-select-sm">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                                <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                    {{ $programme->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Intake</label>
                        <select name="intake" class="form-select form-select-sm">
                            <option value="">All Intakes</option>
                            <option value="March" {{ request('intake') == 'March' ? 'selected' : '' }}>March</option>
                            <option value="September" {{ request('intake') == 'September' ? 'selected' : '' }}>September</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Academic Year</label>
                        <select name="academic_year_id" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="feather-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admission.students.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-refresh-ccw me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table - Compact with Pagination -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0" style="min-width: 700px;">
                    <thead class="bg-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                        <tr class="small">
                            <th width="40">#</th>
                            <th>Reg No</th>
                            <th>Full Name</th>
                            <th>Programme</th>
                            <th>Intake</th>
                            <th>Level/Semester</th>
                            <th>Status</th>
                            <th width="60">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr class="align-middle small">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="fw-semibold">{{ $student->registration_number }}</span>
                            </td>
                            <td>
                                {{ $student->user->first_name ?? '' }} 
                                {{ $student->user->middle_name ?? '' }} 
                                {{ $student->user->last_name ?? '' }}
                            </td>
                            <td>{{ $student->programme->name ?? 'N/A' }}</td>
                            <td>{{ $student->intake }} {{ date('Y', strtotime($student->created_at)) }}</td>
                            <td>Year {{ $student->current_level }}, Sem {{ $student->current_semester }}</td>
                            <td>
                                @if($student->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($student->status == 'suspended')
                                    <span class="badge bg-warning">Suspended</span>
                                @elseif($student->status == 'graduated')
                                    <span class="badge bg-info">Graduated</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($student->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admission.students.show', $student->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="feather-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="feather-users display-4 text-muted d-block mb-3"></i>
                                <h6 class="text-muted">No students found</h6>
                                <a href="{{ route('admission.students.register') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="feather-user-plus me-1"></i> Register New Student
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination for Large Data -->
            <div class="card-footer bg-white py-2">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="small text-muted">
                        Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} 
                        of {{ $students->total() }} entries
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select id="perPage" class="form-select form-select-sm w-auto" style="width: auto;">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="small text-muted">per page</span>
                        <div>
                            {{ $students->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    
    /* Badge styling */
    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    
    /* Row hover effect */
    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    
    /* Compact cards on mobile */
    @media (max-width: 576px) {
        .card-body {
            padding: 0.5rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Per Page Change Handler - for large data pagination
    $('#perPage').change(function() {
        const perPage = $(this).val();
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    });
});
</script>
@endpush
@endsection