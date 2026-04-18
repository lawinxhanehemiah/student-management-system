@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-book-open text-primary"></i> 
            Programmes Management
        </h1>
        <a href="{{ route('superadmin.programmes.create') }}" class="btn btn-primary shadow-sm">
            <i class="feather-plus-circle"></i> Add New Programme
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="feather-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="feather-alert-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <a href="{{ route('superadmin.programmes.index') }}" 
                   class="btn btn-outline-primary {{ request()->routeIs('superadmin.programmes.index') ? 'active' : '' }}">
                    <i class="feather-list"></i> All Programmes
                </a>
                <a href="{{ route('superadmin.programmes.create') }}" 
                   class="btn btn-outline-primary {{ request()->routeIs('superadmin.programmes.create') ? 'active' : '' }}">
                    <i class="feather-plus-circle"></i> Add Programme
                </a>
                <a href="{{ route('superadmin.fee-management.settings') }}" 
                   class="btn btn-outline-success">
                    <i class="feather-dollar-sign"></i> Fee Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="feather-filter"></i> Search Programmes
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Search by name or code...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="study_mode" class="form-control">
                        <option value="">All Study Modes</option>
                        @foreach(['Full Time', 'Part Time', 'Evening', 'Weekend'] as $mode)
                            <option value="{{ $mode }}" {{ request('study_mode') == $mode ? 'selected' : '' }}>
                                {{ $mode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Programmes Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="feather-book"></i> Programmes List ({{ $programmes->total() }})
            </h6>
            <div class="text-success">
                <i class="feather-dollar-sign"></i> 
                Total Programmes with Fees: <strong>{{ \App\Models\Programme::has('fees')->count() }}</strong>
            </div>
        </div>
        <div class="card-body">
            @if($programmes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Programme Name</th>
                                <th>Study Mode</th>
                                <th class="text-center">Seats</th>
                                <th class="text-center">Fees</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="180">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programmes as $programme)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $programme->code }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $programme->name }}</span>
                                        <small class="text-muted">
                                            <i class="feather-calendar"></i> 
                                            Created: {{ $programme->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td>{{ $programme->study_mode }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $programme->hasAvailableSeats() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $programme->available_seats_formatted }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($programme->fees()->count() > 0)
                                        <span class="badge bg-success">
                                            <i class="feather-check"></i> {{ $programme->fees()->count() }} fees
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="feather-alert-circle"></i> No fees
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($programme->is_active && $programme->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- QUICK ADD FEE BUTTON (MOST IMPORTANT) -->
                                        <a href="{{ route('superadmin.programmes.fees.create', $programme) }}" 
                                           class="btn btn-success btn-sm" 
                                           title="Quick Add Fee for {{ $programme->code }}"
                                           data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="feather-plus"></i> Add Fee
                                        </a>
                                        
                                        <!-- VIEW FEES BUTTON -->
                                        <a href="{{ route('superadmin.programmes.fees.index', $programme) }}" 
                                           class="btn btn-info btn-sm" 
                                           title="View All Fees"
                                           data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="feather-dollar-sign"></i>
                                        </a>
                                        
                                        <!-- EDIT PROGRAMME BUTTON -->
                                        <a href="{{ route('superadmin.programmes.edit', $programme) }}" 
                                           class="btn btn-warning btn-sm" 
                                           title="Edit Programme"
                                           data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="feather-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($programmes->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $programmes->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State with Call to Action -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="feather-book-open text-muted" style="font-size: 72px;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No Programmes Found</h4>
                    <p class="text-muted mb-4">Start by adding your first programme and then set up fees for it.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('superadmin.programmes.create') }}" class="btn btn-primary btn-lg">
                            <i class="feather-plus-circle"></i> Add First Programme
                        </a>
                        <a href="{{ route('superadmin.fee-management.settings') }}" class="btn btn-outline-success btn-lg">
                            <i class="feather-dollar-sign"></i> Configure Fee Settings
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats Section -->
    @if($programmes->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Programmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $programmes->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-book-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Programmes with Fees
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Programme::has('fees')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Available Seats
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $programmes->sum('available_seats') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Programmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $programmes->where('is_active', true)->where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Quick add fee confirmation
        $('.btn-success[title*="Quick Add Fee"]').click(function(e) {
            var programmeCode = $(this).attr('title').replace('Quick Add Fee for ', '');
            if (!confirm('Add fee for programme: ' + programmeCode + '?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection