<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | MHCS</title>

<link rel="shortcut icon" href="{{ asset('assets/images/logo.webp') }}">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
body{
    background:#f3f4f6;
    font-family:system-ui;
    min-height:100vh;
    display:flex;
    align-items:center;
    margin:0;
}

.registration-container{
    width:100%;
    max-width:320px;
    margin:auto;
}

.card{
    border-radius:6px;
    border:1px solid #e5e7eb;
}

.card-header{
    padding:10px;
    text-align:center;
    border-bottom:1px solid #e5e7eb;
}

.card-header img{
    width:34px;
    margin-bottom:4px;
}

.card-header h6{
    font-size:.95rem;
    margin:0;
    font-weight:600;
}

.card-header small{
    font-size:.65rem;
    color:#6b7280;
}

.card-body{
    padding:12px;
}

.form-label{
    font-size:.65rem;
    margin-bottom:2px;
    font-weight:600;
}

.form-control{
    font-size:.75rem;
    padding:5px 8px;
    border-radius:4px;
}

.mb-2{margin-bottom:6px!important;}
.mb-3{margin-bottom:8px!important;}

.btn{
    padding:6px;
    font-size:.75rem;
    border-radius:4px;
}

.form-check-label{
    font-size:.65rem;
}

a{
    font-size:.7rem;
    text-decoration:none;
}

.alert{
    font-size:.7rem;
    padding:6px;
    border-radius:4px;
}
</style>
</head>

<body>
<div class="registration-container">
<div class="card">

<div class="card-header">
    <img src="{{ asset('assets/images/logo.webp') }}">
    <h6>Create Account</h6>
    <small>Applicant Registration</small>
</div>

<div class="card-body">

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('applicant.register.submit') }}">
@csrf

<div class="row">
    <div class="col-6 mb-2">
        <label class="form-label">First</label>
        <input class="form-control" name="first_name" placeholder="Lawi" required>
    </div>
    <div class="col-6 mb-2">
        <label class="form-label">Last</label>
        <input class="form-control" name="last_name" placeholder="Nehemiah" required>
    </div>
</div>

<div class="mb-2">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" placeholder="lawinxhanehemiah@gmail.com" required>
</div>

<div class="mb-2">
    <label class="form-label">Phone</label>
    <input class="form-control" name="phone" placeholder="0712699596">
</div>

<div class="mb-2">
    <label class="form-label">Password</label>
    <input type="password" class="form-control" name="password" required>
</div>

<div class="mb-3">
    <label class="form-label">Confirm</label>
    <input type="password" class="form-control" name="password_confirmation" required>
</div>

<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" required>
    <label class="form-check-label">Accept terms</label>
</div>

<button class="btn btn-primary w-100">
    Create Account
</button>

<div class="text-center mt-2">
    <a href="{{ route('applicant.login') }}">Login instead</a>
</div>

</form>
</div>
</div>
</div>
</body>
</html>
