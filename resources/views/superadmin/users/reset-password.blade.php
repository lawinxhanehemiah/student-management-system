@extends('layouts.superadmin')

@section('title', 'Reset Password - ' . $user->first_name . ' ' . $user->last_name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key mr-2"></i>Reset Password
        </h1>
        <div>
            <a href="{{ route('superadmin.users.show', $user->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to User
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Password Reset
                    </h6>
                </div>
                <div class="card-body">
                    
                    <!-- User Info -->
                    <div class="text-center mb-4">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                 class="rounded-circle mb-3" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                <span class="text-white h4">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <h5 class="font-weight-bold">{{ $user->first_name }} {{ $user->last_name }}</h5>
                        <p class="text-muted">
                            @php
                                $role = $user->roles->first();
                                $roleName = $role ? $role->name : 'User';
                            @endphp
                            <span class="badge badge-primary">{{ $roleName }}</span>
                        </p>
                    </div>

                    <!-- Warning Message -->
                    <div class="alert alert-warning mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-circle mr-2"></i>Warning
                        </h6>
                        <p class="mb-0">
                            This will reset the password to default credentials. 
                            User will be forced to change password on next login.
                        </p>
                    </div>

                    <!-- Default Credentials -->
                    <div class="card border-left-success mb-4">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success mb-3">
                                <i class="fas fa-key mr-2"></i>New Default Credentials
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold">Username</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $defaultCredentials['username'] }}" 
                                               readonly
                                               id="usernameField">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="copyUsername()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold">Password</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $defaultCredentials['password'] }}" 
                                               readonly
                                               id="passwordField">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="copyPassword()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ $defaultCredentials['message'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation Form -->
                    <form action="{{ route('superadmin.users.reset-password', $user->id) }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_reset" name="confirm_reset" required>
                                <label class="custom-control-label text-danger font-weight-bold" for="confirm_reset">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    I confirm that I want to reset this user's password
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('superadmin.users.show', $user->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-danger" id="resetBtn" disabled>
                                <i class="fas fa-key mr-2"></i>Reset Password
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- SIMPLE JAVASCRIPT WITHOUT @push --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/Disable submit button
    const confirmCheckbox = document.getElementById('confirm_reset');
    const resetBtn = document.getElementById('resetBtn');
    
    if (confirmCheckbox && resetBtn) {
        confirmCheckbox.addEventListener('change', function() {
            resetBtn.disabled = !this.checked;
        });
    }
    
    // Form submission confirmation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to reset this user\'s password?')) {
                e.preventDefault();
            }
        });
    }
});

// Copy functions
function copyUsername() {
    const field = document.getElementById('usernameField');
    copyToClipboard(field.value, 'Username copied to clipboard');
}

function copyPassword() {
    const field = document.getElementById('passwordField');
    copyToClipboard(field.value, 'Password copied to clipboard');
}

// Global copy function (should be defined in layout)
function copyToClipboard(text, message) {
    // Create temporary input
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    
    // Select and copy
    tempInput.select();
    tempInput.setSelectionRange(0, 99999);
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: message || 'Text copied to clipboard',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                alert(message || 'Copied to clipboard!');
            }
        }
    } catch (err) {
        console.error('Copy failed:', err);
        alert('Failed to copy to clipboard');
    }
    
    // Clean up
    document.body.removeChild(tempInput);
}
</script>