<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>st.maximillian | Change Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: rgb(252, 28, 77);
            --secondary: #ed1920;
            --accent: #10b981;
            --gray: #64748b;
            --shadow: 0 6px 24px rgba(0,0,0,0.14);
        }

        body {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            margin: 0;
            padding: 8px;
        }

        .password-container {
            max-width: 310px;
            width: 100%;
        }

        .password-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 20px 18px 14px;
            text-align: center;
            color: white;
        }

        .logo {
            width: 55px;
            height: 55px;
            background: white;
            border-radius: 50%;
            padding: 8px;
            margin: 0 auto 10px;
        }

        .logo img {
            width: 100%;
        }

        .form-area {
            padding: 16px 18px 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .form-control {
            font-size: 0.92rem;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(252, 28, 77, 0.1);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px 0 0 8px !important;
        }

        .btn-change {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            color: white;
            margin-top: 15px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(252, 28, 77, 0.3);
        }

        .btn-change:active {
            transform: translateY(0);
        }

        .btn-back {
            background: #f1f5f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--gray);
            margin-top: 10px;
            transition: all 0.3s;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: var(--primary);
        }

        .error-box {
            background: #fee;
            border: 1px solid #fcc;
            padding: 10px;
            font-size: 0.85rem;
            color: #b91c1c;
            margin-bottom: 15px;
            text-align: center;
            border-radius: 8px;
        }

        .success-box {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            padding: 10px;
            font-size: 0.85rem;
            color: #065f46;
            margin-bottom: 15px;
            text-align: center;
            border-radius: 8px;
        }

        .password-requirements {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .password-requirements ul {
            padding-left: 18px;
            margin-bottom: 0;
        }

        .password-requirements li {
            margin-bottom: 5px;
        }

        .requirement-met {
            color: var(--accent);
        }

        .requirement-not-met {
            color: #dc2626;
        }

        .password-strength {
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .strength-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 5px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .strength-weak {
            background: #dc2626;
        }

        .strength-medium {
            background: #f59e0b;
        }

        .strength-strong {
            background: var(--accent);
        }

        .strength-text {
            font-size: 0.8rem;
            font-weight: 600;
            text-align: right;
        }

        .toggle-password {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s;
        }

        .toggle-password:hover {
            background: #e2e8f0;
            color: var(--primary);
        }

        .footer {
            text-align: center;
            margin-top: 14px;
            font-size: 0.72rem;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="password-container">
    <div class="password-card">

        <div class="header">
            <div class="logo">
                <img src="{{ asset('/assets/images/logo.WEBP') }}" alt="Logo">
            </div>
            <strong>Change Password</strong><br>
            <small>St. Maximilliancolbe College</small>
        </div>

        <div class="form-area">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="success-box">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <i class="fas fa-exclamation-circle me-1"></i> {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}" id="passwordForm">
                @csrf

                <!-- Current Password (Optional - depends on your requirements) -->
                @if(isset($requireCurrent) && $requireCurrent)
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                        <input
                            type="password"
                            name="current_password"
                            class="form-control"
                            placeholder="Enter current password"
                            id="currentPassword"
                            required
                        >
                        <button type="button" class="btn toggle-password" data-target="currentPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                @endif

                <!-- New Password -->
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter new password"
                            id="newPassword"
                            required
                            minlength="8"
                        >
                        <button type="button" class="btn toggle-password" data-target="newPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="password-strength">
                        <div class="d-flex justify-content-between">
                            <span class="strength-text">Password Strength:</span>
                            <span class="strength-text" id="strengthText">Weak</span>
                        </div>
                        <div class="strength-bar">
                            <div class="strength-fill strength-weak" id="strengthBar"></div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm new password"
                            id="confirmPassword"
                            required
                            minlength="8"
                        >
                        <button type="button" class="btn toggle-password" data-target="confirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatch" class="mt-1" style="font-size: 0.8rem;"></div>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <small class="d-block mb-2"><strong>Password Requirements:</strong></small>
                    <ul>
                        <li id="reqLength"><i class="fas fa-circle me-1"></i> Minimum 8 characters</li>
                        <li id="reqUppercase"><i class="fas fa-circle me-1"></i> At least one uppercase letter</li>
                        <li id="reqLowercase"><i class="fas fa-circle me-1"></i> At least one lowercase letter</li>
                        <li id="reqNumber"><i class="fas fa-circle me-1"></i> At least one number</li>
                        <li id="reqSpecial"><i class="fas fa-circle me-1"></i> At least one special character</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-change" id="submitBtn" disabled>
                    <i class="fas fa-key me-2"></i> Change Password
                </button>

                <a href="{{ url()->previous() }}" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </form>

        </div>
    </div>
    
    <div class="footer">
        <small>&copy; {{ date('Y') }} St. Maximilliancolbe Health College. All rights reserved.</small>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');
    const passwordMatch = document.getElementById('passwordMatch');
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Check password strength
    function checkPasswordStrength(password) {
        let strength = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        // Update requirement indicators
        updateRequirementIndicator('reqLength', requirements.length);
        updateRequirementIndicator('reqUppercase', requirements.uppercase);
        updateRequirementIndicator('reqLowercase', requirements.lowercase);
        updateRequirementIndicator('reqNumber', requirements.number);
        updateRequirementIndicator('reqSpecial', requirements.special);

        // Calculate strength score
        if (requirements.length) strength += 20;
        if (requirements.uppercase) strength += 20;
        if (requirements.lowercase) strength += 20;
        if (requirements.number) strength += 20;
        if (requirements.special) strength += 20;

        // Update strength bar and text
        strengthBar.style.width = strength + '%';
        
        if (strength < 40) {
            strengthBar.className = 'strength-fill strength-weak';
            strengthText.textContent = 'Weak';
            strengthText.style.color = '#dc2626';
        } else if (strength < 80) {
            strengthBar.className = 'strength-fill strength-medium';
            strengthText.textContent = 'Medium';
            strengthText.style.color = '#f59e0b';
        } else {
            strengthBar.className = 'strength-fill strength-strong';
            strengthText.textContent = 'Strong';
            strengthText.style.color = '#10b981';
        }

        return strength;
    }

    function updateRequirementIndicator(elementId, met) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');
        if (met) {
            icon.className = 'fas fa-check-circle me-1 requirement-met';
            element.style.color = '#10b981';
        } else {
            icon.className = 'fas fa-times-circle me-1 requirement-not-met';
            element.style.color = '#dc2626';
        }
    }

    // Check if passwords match
    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value === confirmPassword.value) {
                passwordMatch.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Passwords match';
                passwordMatch.style.color = '#10b981';
                return true;
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> Passwords do not match';
                passwordMatch.style.color = '#dc2626';
                return false;
            }
        }
        return false;
    }

    // Enable/disable submit button based on validation
    function validateForm() {
        const strength = checkPasswordStrength(newPassword.value);
        const passwordsMatch = checkPasswordMatch();
        
        const isValid = newPassword.value.length >= 8 && 
                       passwordsMatch && 
                       strength >= 40; // At least medium strength
        
        submitBtn.disabled = !isValid;
        submitBtn.style.opacity = isValid ? '1' : '0.6';
        submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
    }

    // Event listeners
    newPassword.addEventListener('input', validateForm);
    confirmPassword.addEventListener('input', validateForm);

    // Initial validation
    validateForm();

    // Form submission handler
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        if (submitBtn.disabled) {
            e.preventDefault();
            alert('Please ensure all password requirements are met.');
        }
    });
});
</script>

</body>
</html>