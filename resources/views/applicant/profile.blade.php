@extends('layouts.applicant-ajira')

@section('title', 'My Profile')
@section('title-icon', 'fa-user-circle')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="ajira-table">
            <div class="table-header">
                <h5><i class="fas fa-user-edit me-2"></i> Edit Profile</h5>
            </div>
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Please fix the following errors:
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('applicant.profile.update') }}">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('applicant.dashboard') }}" class="btn-ajira btn-ajira-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn-ajira btn-ajira-primary">
                            <i class="fas fa-save me-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Account Info -->
        <div class="ajira-table mb-4">
            <div class="table-header">
                <h5><i class="fas fa-info-circle me-2"></i> Account Information</h5>
            </div>
            <div class="p-3">
                <div class="text-center mb-3">
                    <div class="mb-3">
                        <div style="width: 80px; height: 80px; background: var(--ajira-blue); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: bold;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                        <span>Member Since</span>
                        <span class="text-muted">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                        <span>Account Status</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                        <span>Last Login</span>
                        <span class="text-muted">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="ajira-table">
            <div class="table-header">
                <h5><i class="fas fa-lock me-2"></i> Security</h5>
            </div>
            <div class="p-3 text-center">
                <a href="{{ route('applicant.password') }}" class="btn-ajira btn-ajira-primary w-100">
                    <i class="fas fa-key me-2"></i> Change Password
                </a>
            </div>
        </div>
    </div>
</div>
@endsection