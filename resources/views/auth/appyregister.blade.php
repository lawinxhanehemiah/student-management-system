@extends('layouts.guest')

@section('title', 'Create Applicant Account')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Applicant Registration
                    </h3>
                    <p class="mb-0 mt-2">Create your account to start application</p>
                </div>
                
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('applicant.register') }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-12 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-graduation-cap fa-4x text-primary"></i>
                                </div>
                                <h4 class="text-primary">Join Our Institution</h4>
                                <p class="text-muted">Fill in your details to create an applicant account</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email Address *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">This will be your login username</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Create Applicant Account
                            </button>
                            
                            <div class="text-center mt-3">
                                <p class="mb-0">
                                    Already have an account? 
                                    <a href="{{ route('login') }}" class="text-primary fw-semibold">Login here</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light text-center py-3">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <i class="fas fa-lock text-success me-1"></i>
                            <small>Secure Registration</small>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <i class="fas fa-gift text-primary me-1"></i>
                            <small>Free Application</small>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-clock text-warning me-1"></i>
                            <small>5-Minute Process</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Application Terms</h6>
                <ul>
                    <li>You can only submit one application per academic year</li>
                    <li>All information provided must be accurate and truthful</li>
                    <li>Application is free of charge</li>
                    <li>Documents must be authentic and verifiable</li>
                    <li>Submission deadline must be observed</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection