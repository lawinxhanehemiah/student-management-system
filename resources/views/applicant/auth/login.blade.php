<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | MHCS</title>

<link rel="shortcut icon" href="{{ asset('assets/images/logo.webp') }}">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    
}

.login-card {
    width: 100%;
    max-width: 320px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border-radius: 20px;
}

.card-header {
    text-align: center;
    padding: 16px 16px 12px;
    border-bottom: 1px solid #f1f5f9;
    background: red;
    
}

.card-header img {
    width: 40px;
    height: 40px;
    margin-bottom: 6px;
}

.card-header h6 {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    line-height: 1.2;
}

.card-header small {
    font-size: 11px;
    color: #64748b;
    display: block;
    margin-top: 2px;
}

.card-body {
    padding: 16px;
    
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    display: block;
}

.form-control {
    font-size: 13px;
    padding: 7px 10px;
    height: 34px;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #fff;
    transition: all 0.15s;
}

.form-control:focus {
    border-color: #4e54c8;
    box-shadow: 0 0 0 2px rgba(78, 84, 200, 0.1);
    outline: none;
}

.input-group {
    position: relative;
    display: flex;
    align-items: stretch;
    width: 100%;
}

.input-group .form-control {
    border-right: none;
    border-radius: 5px 0 0 5px;
}

.input-group-text {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-left: none;
    padding: 0 10px;
    border-radius: 0 5px 5px 0;
    display: flex;
    align-items: center;
    font-size: 12px;
    color: #64748b;
}

.password-toggle {
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 0 10px;
    color: #64748b;
}

.password-toggle:hover {
    color: #4e54c8;
}

.btn-login {
    width: 100%;
    padding: 8px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 5px;
    background: rgb(200, 78, 78);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    margin-top: 8px;
    
}


.btn-login:hover {
    background: #3b40a0;
}

.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    margin-bottom: 12px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-check-input {
    width: 12px;
    height: 12px;
    margin: 0;
    border: 1px solid #cbd5e1;
}

.form-check-input:checked {
    background-color: #4e54c8;
    border-color: #4e54c8;
}

.form-check-label {
    font-size: 11px;
    color: #475569;
    cursor: pointer;
}

.forgot-link {
    font-size: 11px;
    color: #4e54c8;
    text-decoration: none;
}

.forgot-link:hover {
    text-decoration: underline;
    color: #3b40a0;
}

.register-link {
    font-size: 11px;
    color: #4e54c8;
    text-decoration: none;
    font-weight: 500;
}

.register-link:hover {
    text-decoration: underline;
    color: #3b40a0;
}

.alert {
    font-size: 11px;
    padding: 8px 10px;
    border-radius: 5px;
    margin-bottom: 12px;
    border: 1px solid transparent;
}

.alert-danger {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}

.alert-success {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}

.error-message {
    font-size: 10px;
    color: #dc2626;
    margin-top: 2px;
    display: block;
}

.mb-3 {
    margin-bottom: 10px !important;
}

.mt-4 {
    margin-top: 12px !important;
}

.text-center {
    text-align: center;
}

ul {
    margin-bottom: 0;
}

li {
    margin-bottom: 2px;
}

/* Responsive */
@media (max-width: 340px) {
    .login-card {
        max-width: 100%;
    }
    
    .card-body {
        padding: 12px;
    }
}
</style>
</head>

<body>
<div class="login-card">

<div class="card-header">
    <img src="{{ asset('assets/images/logo.webp') }}">
    <h6>Applicant Login</h6>
    <small>MHCS System</small>
</div>

<div class="card-body">

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('applicant.login.submit') }}" id="loginForm">
@csrf

<div class="mb-3">
    <label class="form-label">Email</label>
    <div class="input-group">
        <input type="email" 
               name="email" 
               class="form-control @error('email') is-invalid @enderror" 
               value="{{ old('email') }}" 
               placeholder="email@example.com"
               required 
               autofocus>
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
    </div>
    @error('email')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Password</label>
    <div class="input-group">
        <input type="password" 
               name="password" 
               id="password" 
               class="form-control @error('password') is-invalid @enderror" 
               placeholder="••••••••"
               required>
        <button type="button" class="input-group-text password-toggle" id="togglePassword">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    @error('password')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<div class="remember-forgot">
    <div class="form-check">
        <input class="form-check-input" 
               type="checkbox" 
               name="remember" 
               id="remember"
               {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    <a href="" class="forgot-link">Forgot password?</a>
</div>

<button type="submit" class="btn-login" id="submitBtn">
    <i class="fas fa-sign-in-alt me-1"></i> Login
</button>

<div class="text-center mt-4">
    <a href="{{ route('applicant.register') }}" class="register-link">
        <i class="fas fa-user-plus me-1"></i> Create account
    </a>
</div>

</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const toggleBtn = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    toggleBtn.addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        
        const icon = this.querySelector('i');
        if (type === 'password') {
            icon.className = 'fas fa-eye';
        } else {
            icon.className = 'fas fa-eye-slash';
        }
    });
    
    // Form submission loading state
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Logging in...';
    });
    
    // Auto-focus email field if empty
    const emailField = document.querySelector('input[name="email"]');
    if (!emailField.value) {
        emailField.focus();
    }
});
</script>
</body>
</html>