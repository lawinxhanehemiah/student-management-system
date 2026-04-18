<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>st.maximillian | Login</title>

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

        .login-container {
            max-width: 310px;
            width: 100%;
        }

        .login-card {
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
        }

        .btn-login {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 8px;
            padding: 9px;
            font-size: 0.95rem;
            font-weight: 600;
            color: white;
            margin-top: 10px;
        }

        .error-box {
            background: #fee;
            border: 1px solid #fcc;
            padding: 6px;
            font-size: 0.8rem;
            color: #b91c1c;
            margin-bottom: 10px;
            text-align: center;
            border-radius: 6px;
        }
        .forgot {
            text-align: center;
            margin-top: 12px;
            font-size: 0.8rem;
            text-decoration: none;
            
        }

        .footer {
            text-align: center;
            margin-top: 14px;
            font-size: 0.72rem;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="header">
            <div class="logo">
                <img src="{{ asset('/assets/images/logo.WEBP') }}" alt="Logo">
            </div>
            <strong>St. Maximilliancolbe</strong><br>
            <small>Health College • MHCS Login</small>
        </div>

        <div class="form-area">

            {{-- Laravel errors --}}
            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Laravel Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Username / Registration -->
                <div class="mb-2">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input
                            type="text"
                            name="login"
                            class="form-control"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                   <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
            </form>
            <div class="forgot">
                <a href="#" class="text-muted">Forgot Password</a>
            </div>

        </div>
    </div>
    
</div>

</body>
</html>
