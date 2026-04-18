@extends('layouts.superadmin')

@section('title', 'User Details - St. Maximilliancolbe')

@section('content')
<div class="container-fluid">
    <!-- Success & Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('credentials'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-key mr-2"></i> New Credentials Generated</h5>
            <p class="mb-1"><strong>Username:</strong> {{ session('credentials')['username'] }}</p>
            <p class="mb-0"><strong>Password:</strong> {{ session('credentials')['password'] }}</p>
            <p class="mt-2 mb-0 text-muted small">
                <i class="fas fa-info-circle mr-1"></i>
                User will be forced to change password on next login
            </p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user mr-2"></i>User Details
        </h1>
        <div>
            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-warning btn-icon-split mr-2">
                <span class="icon text-white-50">
                    <i class="fas fa-edit"></i>
                </span>
                <span class="text">Edit User</span>
            </a>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span class="text">Back to Users</span>
            </a>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('superadmin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $user->first_name }} {{ $user->last_name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-id-card mr-2"></i>User Profile
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('superadmin.users.edit', $user->id) }}">
                                <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                Edit User
                            </a>
                            @if(Route::has('superadmin.users.reset-password.show'))
                            <a class="dropdown-item" href="{{ route('superadmin.users.reset-password.show', $user->id) }}">
                                <i class="fas fa-key fa-sm fa-fw mr-2 text-warning"></i>
                                Reset Password
                            </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete()">
                                <i class="fas fa-trash fa-sm fa-fw mr-2"></i>
                                Delete User
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <div class="mb-4">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile Photo" 
                                 class="img-fluid rounded-circle border border-4 border-primary" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center border border-4 border-primary" 
                                 style="width: 150px; height: 150px;">
                                <span class="text-white display-4">{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- User Name and Status -->
                    <h4 class="font-weight-bold text-gray-800">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</h4>
                    <p class="mb-2">
                        @php
                            $role = $user->roles->first();
                            $roleName = $role ? $role->name : 'No Role';
                        @endphp
                        <span class="badge badge-primary px-3 py-2">
                            <i class="fas fa-user-tag mr-1"></i> {{ $roleName }}
                        </span>
                    </p>
                    
                    <!-- Status Badge -->
                    <p class="mb-4">
                        @if($user->status == 'active')
                            <span class="badge badge-success px-3 py-2">
                                <i class="fas fa-check-circle mr-1"></i> Active
                            </span>
                        @elseif($user->status == 'inactive')
                            <span class="badge badge-secondary px-3 py-2">
                                <i class="fas fa-pause-circle mr-1"></i> Inactive
                            </span>
                        @else
                            <span class="badge badge-warning px-3 py-2">
                                <i class="fas fa-exclamation-circle mr-1"></i> Suspended
                            </span>
                        @endif
                    </p>
                    
                    <!-- Contact Info -->
                    <div class="text-left border-top pt-3">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-light rounded-circle p-2 mr-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-gray-600 small">Email Address</div>
                                    <div class="font-weight-bold">{{ $user->email ?? 'Not provided' }}</div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-light rounded-circle p-2 mr-3">
                                    <i class="fas fa-phone text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-gray-600 small">Phone Number</div>
                                    <div class="font-weight-bold">{{ $user->phone ?? 'Not provided' }}</div>
                                </div>
                            </div>
                            
                            @if($user->registration_number)
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-light rounded-circle p-2 mr-3">
                                    <i class="fas fa-id-card text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-gray-600 small">Registration Number</div>
                                    <div class="font-weight-bold">{{ $user->registration_number }}</div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 mr-3">
                                    <i class="fas fa-user-circle text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-gray-600 small">User Type</div>
                                    <div class="font-weight-bold">{{ ucfirst($user->user_type) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div>
                                <div class="text-gray-600 small">Member Since</div>
                                <div class="font-weight-bold">{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 small">Last Updated</div>
                                <div class="font-weight-bold">{{ $user->updated_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="mailto:{{ $user->email }}" class="btn btn-outline-primary btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <span>Send Email</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            @if($user->phone)
                            <a href="tel:{{ $user->phone }}" class="btn btn-outline-success btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="fas fa-phone fa-2x mb-2"></i>
                                <span>Call User</span>
                            </a>
                            @else
                            <button class="btn btn-outline-secondary btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3" disabled>
                                <i class="fas fa-phone fa-2x mb-2"></i>
                                <span>No Phone</span>
                            </button>
                            @endif
                        </div>
                        <div class="col-6">
                            @if(Route::has('superadmin.users.reset-password.show'))
                            <a href="{{ route('superadmin.users.reset-password.show', $user->id) }}" class="btn btn-outline-warning btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="fas fa-key fa-2x mb-2"></i>
                                <span>Reset Password</span>
                            </a>
                            @else
                            <button class="btn btn-outline-secondary btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3" onclick="alert('Reset password feature coming soon')">
                                <i class="fas fa-key fa-2x mb-2"></i>
                                <span>Reset Password</span>
                            </button>
                            @endif
                        </div>
                        <div class="col-6">
                            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-outline-info btn-block h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                <i class="fas fa-edit fa-2x mb-2"></i>
                                <span>Edit Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-xl-8 col-lg-7">
            <!-- Personal Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user-circle mr-2"></i>Personal Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Full Name</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Gender</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ ucfirst($user->gender ?? 'Not specified') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Email Address</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->email ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Phone Number</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">User Type</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0">
                                    <span class="badge badge-info px-3 py-2">{{ ucfirst($user->user_type) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Account Status</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0">
                                    @if($user->status == 'active')
                                        <span class="badge badge-success px-3 py-2">Active</span>
                                    @elseif($user->status == 'inactive')
                                        <span class="badge badge-secondary px-3 py-2">Inactive</span>
                                    @else
                                        <span class="badge badge-warning px-3 py-2">Suspended</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Registration Number</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->registration_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-primary">Must Change Password</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0">
                                    @if($user->must_change_password)
                                        <span class="badge badge-warning px-3 py-2">Yes</span>
                                        <small class="text-muted d-block mt-1">User must change password on next login</small>
                                    @else
                                        <span class="badge badge-success px-3 py-2">No</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="font-weight-bold text-primary">Created At</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-plus text-primary mr-3 fa-lg"></i>
                                <div>
                                    <p class="mb-0 font-weight-bold">{{ $user->created_at->format('F d, Y') }}</p>
                                    <small class="text-muted">{{ $user->created_at->format('h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold text-primary">Last Updated</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check text-primary mr-3 fa-lg"></i>
                                <div>
                                    <p class="mb-0 font-weight-bold">{{ $user->updated_at->format('F d, Y') }}</p>
                                    <small class="text-muted">{{ $user->updated_at->format('h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Information (if applicable) -->
            @if($user->student)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user-graduate mr-2"></i>Student Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Programme</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->student->programme->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Course</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->student->course->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Study Mode</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ ucfirst(str_replace('_', ' ', $user->student->study_mode)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Intake</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">
                                    <span class="badge badge-success px-3 py-2">{{ $user->student->intake }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Guardian Name</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->student->guardian_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-success">Guardian Phone</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0 font-weight-bold">{{ $user->student->guardian_phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-success">Student Status</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-0">
                                    <span class="badge {{ $user->student->status == 'active' ? 'badge-success' : 'badge-secondary' }} px-3 py-2">
                                        {{ ucfirst($user->student->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Application Information (if applicable) -->
            @if($user->applications && $user->applications->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i>Application Information
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($user->applications as $application)
                    <div class="card border-left-warning mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Application Number</label>
                                    <p class="h5 font-weight-bold">{{ $application->application_number }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Application Status</label>
                                    <p class="mb-0">
                                        @if($application->status == 'approved')
                                            <span class="badge badge-success px-3 py-2">Approved</span>
                                        @elseif($application->status == 'rejected')
                                            <span class="badge badge-danger px-3 py-2">Rejected</span>
                                        @else
                                            <span class="badge badge-warning px-3 py-2">{{ ucfirst($application->status) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Programme Applied</label>
                                    <p class="font-weight-bold">{{ $application->programme->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Course Applied</label>
                                    <p class="font-weight-bold">{{ $application->course->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Intake</label>
                                    <p class="font-weight-bold">{{ $application->intake }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold text-warning">Study Mode</label>
                                    <p class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $application->study_mode)) }}</p>
                                </div>
                                <div class="col-md-12">
                                    <label class="font-weight-bold text-warning">Application Stage</label>
                                    <p class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $application->application_stage)) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- System Information Card -->
            <div class="card shadow">
                <div class="card-header py-3 bg-gradient-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-cogs mr-2"></i>System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-secondary">Assigned Roles</label>
                            <div class="card bg-light border-0 p-3 min-h-100">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($user->roles as $role)
                                        <span class="badge badge-primary px-3 py-2">
                                            <i class="fas fa-user-shield mr-1"></i> {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($user->roles->count() == 0)
                                        <span class="badge badge-secondary px-3 py-2">No Roles Assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-secondary">Permissions</label>
                            <div class="card bg-light border-0 p-3 min-h-100">
                                @php
                                    $permissions = $user->getAllPermissions();
                                @endphp
                                @if($permissions->count() > 0)
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($permissions->take(5) as $permission)
                                            <span class="badge badge-info px-3 py-1">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                    @if($permissions->count() > 5)
                                        <small class="text-muted">
                                            <i class="fas fa-ellipsis-h mr-1"></i>
                                            +{{ $permissions->count() - 5 }} more permissions
                                        </small>
                                    @endif
                                @else
                                    <span class="badge badge-secondary px-3 py-2">No Direct Permissions</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-secondary">Last Login</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 mr-3">
                                    <i class="fas fa-sign-in-alt fa-lg text-secondary"></i>
                                </div>
                                <div>
                                    @if($user->last_login_at)
                                        <p class="mb-0 font-weight-bold">{{ \Carbon\Carbon::parse($user->last_login_at)->format('F d, Y') }}</p>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($user->last_login_at)->format('h:i A') }}</small>
                                    @else
                                        <p class="mb-0 font-weight-bold text-muted">Never logged in</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-secondary">Login IP</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 mr-3">
                                    <i class="fas fa-network-wired fa-lg text-secondary"></i>
                                </div>
                                <div>
                                    <p class="mb-0 font-weight-bold">{{ $user->last_login_ip ?? 'N/A' }}</p>
                                    <small class="text-muted">Last known IP address</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    }
</script>

<style>
    .min-h-100 {
        min-height: 100px;
    }
    .bg-gradient-primary {
        background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(90deg, #36b9cc 0%, #258391 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(90deg, #1cc88a 0%, #13855c 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(90deg, #f6c23e 0%, #dda20a 100%);
    }
    .bg-gradient-secondary {
        background: linear-gradient(90deg, #858796 0%, #60616f 100%);
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>
@endpush