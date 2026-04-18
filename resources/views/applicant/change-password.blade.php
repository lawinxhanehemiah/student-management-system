@extends('layouts.applicant-ajira')

@section('title', 'Change Password')
@section('title-icon', 'fa-key')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('applicant.profile') }}">My Profile</a></li>
    <li class="breadcrumb-item active">Change Password</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="ajira-table">
            <div class="table-header">
                <h5><i class="fas fa-key me-2"></i> Change Password</h5>
            </div>
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('applicant.password.update') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small class="text-muted">Must be at least 8 characters</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('applicant.profile') }}" class="btn-ajira btn-ajira-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Profile
                        </a>
                        <button type="submit" class="btn-ajira btn-ajira-primary">
                            <i class="fas fa-save me-2"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection