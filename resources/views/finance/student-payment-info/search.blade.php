@extends('layouts.financecontroller')

@section('title', 'Student Payment Info')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Student Payment Information</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <span class="breadcrumb-item active">Search Student</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Find Student</div>
                </div>
                <div class="card-body">
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- 🔴 FIXED: Form inatumia route sahihi ya POST -->
                    <form action="{{ route('finance.student-payment-info.search.post') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label required">Registration Number</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('registration_number') is-invalid @enderror" 
                                   name="registration_number" 
                                   value="{{ old('registration_number') }}"
                                   placeholder="e.g., 02.002.09.2026"
                                   required>
                            @error('registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter the student's registration number</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="feather-search"></i> View Payment Info
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted">Or browse recent students</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            
                            <a href="{{ route('finance.all-payments.index') }}" class="btn btn-sm btn-outline-secondary">
                                All Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection