@extends('layouts.financecontroller')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('accountant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.control-numbers.index') }}">Control Numbers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Generate Control Number</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-2">Generate Control Number</h4>
            <p class="text-muted mb-0">Create new control number for tuition, supplementary, repeat module, or other fees</p>
        </div>
        <div>
            <a href="{{ route('finance.control-numbers.index') }}" class="btn btn-outline-secondary">
                <i class="feather-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Generation Type Cards -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Generation Type</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="form-check card-radio p-4 border rounded-3 h-100 {{ old('generation_type') == 'student' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                <input class="form-check-input" type="radio" name="generation_type" 
                                       id="typeStudent" value="student" 
                                       {{ old('generation_type') == 'student' ? 'checked' : '' }} required>
                                <label class="form-check-label ms-2 w-100" for="typeStudent">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                            <i class="feather-user text-primary fs-4"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-semibold mb-1">Generate for Student</h6>
                                            <p class="text-muted small mb-0">Generate control number from registered student records</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check card-radio p-4 border rounded-3 h-100 {{ old('generation_type') == 'manual' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                <input class="form-check-input" type="radio" name="generation_type" 
                                       id="typeManual" value="manual" 
                                       {{ old('generation_type') == 'manual' ? 'checked' : '' }} required>
                                <label class="form-check-label ms-2 w-100" for="typeManual">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-icon bg-secondary bg-opacity-10 p-3 rounded-circle me-3">
                                            <i class="feather-edit text-secondary fs-4"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-semibold mb-1">Manual Entry</h6>
                                            <p class="text-muted small mb-0">Enter payer details manually</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('generation_type')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('finance.control-numbers.store') }}" method="POST" id="generateForm">
        @csrf

        <!-- Student Selection Section -->
        <div id="studentSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Select Student</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="row g-2 mb-4">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="feather-search"></i>
                                    </span>
                                    <input type="text" id="studentSearchInput" class="form-control form-control-lg" 
                                           placeholder="Search by Name, Registration Number or Phone...">
                                    <button class="btn btn-primary" type="button" id="searchButton">
                                        <i class="feather-search me-1"></i>Search
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn">
                                        <i class="feather-x"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Filters -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <select id="searchFilter" class="form-select">
                                    <option value="all">All Fields</option>
                                    <option value="name">Name Only</option>
                                    <option value="reg_no">Registration Number</option>
                                    <option value="phone">Phone Number</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <span id="searchResultsCount" class="badge bg-info p-2 fs-12">
                                    Total: {{ count($students) }} Students
                                </span>
                            </div>
                            <div class="col-md-4 text-end">
                                <span id="selectedCount" class="text-muted"></span>
                            </div>
                        </div>

                        <!-- Student Select Dropdown -->
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Select Student <span class="text-danger">*</span></label>
                                <select name="student_id" id="studentSelect" class="form-select" size="6" style="height: auto; min-height: 240px; width: 100%;">
                                    <option value="">-- Select Student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student['id'] }}" 
                                            data-reg="{{ $student['registration_number'] }}"
                                            data-name="{{ $student['full_name'] }}"
                                            data-programme="{{ $student['programme'] }}"
                                            data-programme-id="{{ $student['programme_id'] }}"
                                            data-level="{{ $student['current_level'] ?? 1 }}"
                                            data-phone="{{ $student['phone'] ?? 'N/A' }}"
                                            data-email="{{ $student['email'] ?? 'N/A' }}"
                                            class="student-option"
                                            {{ old('student_id') == $student['id'] ? 'selected' : '' }}>
                                            {{ $student['registration_number'] }} - {{ $student['full_name'] }} 
                                            ({{ $student['programme'] }}) - {{ $student['phone'] ?? 'No Phone' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted mt-1 d-block">
                                    <i class="feather-info"></i> 
                                    Type to search, or scroll to browse. {{ count($students) }} students total.
                                </small>
                            </div>
                        </div>

                        <!-- Student Info Preview -->
                        <div id="studentInfo" class="mt-4 p-3 bg-light rounded-3 border" style="display: none;"></div>

                        <!-- Navigation Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="prevStudentBtn">
                                        <i class="feather-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="nextStudentBtn">
                                        Next <i class="feather-chevron-right"></i>
                                    </button>
                                </div>
                                <span class="ms-2 text-muted" id="studentNavInfo">1 of {{ count($students) }}</span>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-sm btn-success" id="quickSelectCurrentStudent">
                                    <i class="feather-user-check"></i> Select Current
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clearStudentSelection">
                                    <i class="feather-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Entry Section -->
        <div id="manualSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="{{ old('full_name') }}" placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="registration_number" class="form-control" 
                                       value="{{ old('registration_number') }}" placeholder="Enter registration number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email') }}" placeholder="Enter email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="{{ old('phone') }}" placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Details -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Fee Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fee Type <span class="text-danger">*</span></label>
                                <select name="fee_type" id="feeType" class="form-select" required>
                                    <option value="">-- Select Fee Type --</option>
                                    @foreach($feeTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('fee_type') == $key ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Category <span class="text-danger">*</span></label>
                                <select name="payment_category" id="paymentCategory" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="tuition_fee" {{ old('payment_category') == 'tuition_fee' ? 'selected' : '' }}>Ada ya Masomo (Tuition Fee)</option>
                                    <option value="supplementary_fee" {{ old('payment_category') == 'supplementary_fee' ? 'selected' : '' }}>Supplementary Fee</option>
                                    <option value="repeat_fee" {{ old('payment_category') == 'repeat_fee' ? 'selected' : '' }}>Repeat Module Fee</option>
                                    <option value="programme_fee" {{ old('payment_category') == 'programme_fee' ? 'selected' : '' }}>Programme Fee (Full)</option>
                                    <option value="hostel_fee" {{ old('payment_category') == 'hostel_fee' ? 'selected' : '' }}>Hostel Accommodation</option>
                                    <option value="application_fee" {{ old('payment_category') == 'application_fee' ? 'selected' : '' }}>Application Fee</option>
                                    <option value="registration_fee" {{ old('payment_category') == 'registration_fee' ? 'selected' : '' }}>Registration Fee</option>
                                    <option value="other_fee" {{ old('payment_category') == 'other_fee' ? 'selected' : '' }}>Other Fee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tuition Fee Section -->
        <div id="tuitionFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="mb-0 text-primary">
                            <i class="feather-book-open me-2"></i>Ada ya Masomo (Tuition Fee)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Programme</label>
                                <select name="programme_id" id="tuitionProgrammeSelect" class="form-select">
                                    <option value="">-- Select Programme --</option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}" 
                                            {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->code }} - {{ $programme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Academic Year</label>
                                <select name="academic_year_id" id="tuitionAcademicYearSelect" class="form-select">
                                    <option value="">-- Select Year --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" 
                                            {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tuition Type</label>
                                <select name="tuition_type" id="tuitionTypeSelect" class="form-select">
                                    <option value="full_year" {{ old('tuition_type') == 'full_year' ? 'selected' : '' }}>Mwaka Mzima (Full Year)</option>
                                    <option value="semester_1" {{ old('tuition_type') == 'semester_1' ? 'selected' : '' }}>Semester 1</option>
                                    <option value="semester_2" {{ old('tuition_type') == 'semester_2' ? 'selected' : '' }}>Semester 2</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <input type="text" id="tuitionAmount" class="form-control bg-white" 
                                           readonly placeholder="0.00">
                                    <span class="input-group-text">TZS</span>
                                </div>
                            </div>
                        </div>
                        <div id="tuitionFeeBreakdown" class="mt-3 p-3 bg-white rounded border" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplementary Fee Section -->
        <div id="supplementaryFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info bg-opacity-10">
                        <h5 class="mb-0 text-info">
                            <i class="feather-plus-circle me-2"></i>Supplementary Fee
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Select Course</label>
                                <select name="supplementary_fee_id" id="supplementaryFeeSelect" class="form-select">
                                    <option value="">-- Select Course --</option>
                                    @foreach($supplementaryFees as $fee)
                                        <option value="{{ $fee->id }}" 
                                            data-programme="{{ $fee->programme_id }}"
                                            data-course="{{ $fee->course_id }}"
                                            data-amount="{{ $fee->amount_per_unit }}"
                                            data-course-name="{{ $fee->course->name ?? '' }}"
                                            data-course-code="{{ $fee->course->code ?? '' }}"
                                            {{ old('supplementary_fee_id') == $fee->id ? 'selected' : '' }}>
                                            {{ $fee->course->code ?? '' }} - {{ $fee->course->name ?? '' }} 
                                            ({{ number_format($fee->amount_per_unit) }} TZS/unit)
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="course_id" id="supplementaryCourseId">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Number of Units</label>
                                <select name="units" id="supplementaryUnitsSelect" class="form-select">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('units', 1) == $i ? 'selected' : '' }}>
                                            {{ $i }} Unit{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <input type="text" id="supplementaryAmount" class="form-control bg-white" 
                                           readonly placeholder="0.00">
                                    <span class="input-group-text">TZS</span>
                                </div>
                            </div>
                        </div>
                        <div id="supplementaryInfo" class="mt-3 p-3 bg-white rounded border" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Repeat Module Fee Section -->
        <div id="repeatFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0 text-warning">
                            <i class="feather-repeat me-2"></i>Repeat Module Fee
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Select Course</label>
                                <select name="repeat_fee_id" id="repeatFeeSelect" class="form-select">
                                    <option value="">-- Select Course --</option>
                                    @foreach($repeatFees as $fee)
                                        <option value="{{ $fee->id }}" 
                                            data-programme="{{ $fee->programme_id }}"
                                            data-course="{{ $fee->course_id }}"
                                            data-amount="{{ $fee->amount_per_unit }}"
                                            data-course-name="{{ $fee->course->name ?? '' }}"
                                            data-course-code="{{ $fee->course->code ?? '' }}"
                                            {{ old('repeat_fee_id') == $fee->id ? 'selected' : '' }}>
                                            {{ $fee->course->code ?? '' }} - {{ $fee->course->name ?? '' }} 
                                            ({{ number_format($fee->amount_per_unit) }} TZS/unit)
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="course_id" id="repeatCourseId">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Number of Units</label>
                                <select name="units" id="repeatUnitsSelect" class="form-select">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('units', 1) == $i ? 'selected' : '' }}>
                                            {{ $i }} Unit{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <input type="text" id="repeatAmount" class="form-control bg-white" 
                                           readonly placeholder="0.00">
                                    <span class="input-group-text">TZS</span>
                                </div>
                            </div>
                        </div>
                        <div id="repeatInfo" class="mt-3 p-3 bg-white rounded border" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programme Fee Section (Legacy) -->
        <div id="programmeFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Programme Fee Details (Full)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Programme</label>
                                <select name="programme_id" id="programmeSelect" class="form-select">
                                    <option value="">-- Select Programme --</option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}" 
                                            {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->code }} - {{ $programme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Academic Year</label>
                                <select name="academic_year_id" id="academicYearSelect" class="form-select">
                                    <option value="">-- Select Year --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" 
                                            {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <input type="text" id="programmeAmount" class="form-control bg-white" 
                                           readonly placeholder="Auto-calc">
                                    <span class="input-group-text">TZS</span>
                                </div>
                            </div>
                        </div>
                        <div id="feeBreakdown" class="mt-3 p-3 bg-light rounded" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hostel Fee Section -->
        <div id="hostelFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Hostel Fee Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hostel</label>
                                <select name="hostel_id" id="hostelSelect" class="form-select">
                                    <option value="">-- Select Hostel --</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}" 
                                            {{ old('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                            {{ $hostel->name }} - {{ $hostel->type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (TZS)</label>
                                <input type="number" name="hostel_amount" class="form-control" 
                                       value="{{ old('hostel_amount') }}" placeholder="Enter hostel fee amount" min="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Fee Section -->
        <div id="otherFeeSection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Other Fee Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" 
                                       value="{{ old('amount') }}" placeholder="Enter amount" min="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Purpose Description</label>
                                <input type="text" name="purpose_description" class="form-control" 
                                       value="{{ old('purpose_description') }}" placeholder="Describe purpose of payment">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Number Settings -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Control Number Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Expiry Period <span class="text-danger">*</span></label>
                                <select name="expiry_days" class="form-select" required>
                                    <option value="365" {{ old('expiry_days', 365) == 365 ? 'selected' : '' }}>Mwaka 1 (365 Days)</option>
                                    <option value="270" {{ old('expiry_days') == 270 ? 'selected' : '' }}>9 Months (270 Days)</option>
                                    <option value="180" {{ old('expiry_days') == 180 ? 'selected' : '' }}>6 Months (180 Days)</option>
                                    <option value="90" {{ old('expiry_days') == 90 ? 'selected' : '' }}>90 Days</option>
                                    <option value="60" {{ old('expiry_days') == 60 ? 'selected' : '' }}>60 Days</option>
                                    <option value="30" {{ old('expiry_days') == 30 ? 'selected' : '' }}>30 Days</option>
                                    <option value="14" {{ old('expiry_days') == 14 ? 'selected' : '' }}>14 Days</option>
                                    <option value="7" {{ old('expiry_days') == 7 ? 'selected' : '' }}>7 Days</option>
                                </select>
                                <small class="text-muted"><i class="feather-info"></i> Default: Mwaka 1 (365 days)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Require Approval</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="require_approval" 
                                           id="requireApproval" value="1" {{ old('require_approval') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requireApproval">Yes, require approval before payment</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div id="summarySection" class="row mt-4" style="display: none;">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="feather-file-text me-2"></i>Control Number Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Reference Number:</th>
                                        <td><span id="summaryRef" class="fw-bold text-primary">STMC-YYYY-XXXXX</span></td>
                                    </tr>
                                    <tr>
                                        <th>Payer:</th>
                                        <td><span id="summaryPayer"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Fee Type:</th>
                                        <td><span id="summaryFeeType"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Category:</th>
                                        <td><span id="summaryCategory"></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="40%">Total Amount:</th>
                                        <td><span id="summaryAmount" class="fw-bold text-success h5">0 TZS</span></td>
                                    </tr>
                                    <tr>
                                        <th>Expiry Date:</th>
                                        <td><span id="summaryExpiry"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="badge bg-info">Pending</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="feather-hash me-2"></i>Generate Control Number
                </button>
                <a href="{{ route('finance.control-numbers.index') }}" class="btn btn-outline-secondary px-5 ms-2">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .card-radio {
        cursor: pointer;
        transition: all 0.2s;
    }
    .card-radio:hover {
        border-color: #9b59b6 !important;
        background-color: rgba(155, 89, 182, 0.05);
    }
    .card-radio input[type="radio"] {
        float: left;
        margin-top: 5px;
    }
    .card-radio.border-success {
        border-color: #9b59b6 !important;
        border-width: 2px !important;
    }
    .avatar-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .border-primary {
        border-left: 4px solid #9b59b6 !important;
    }
    .border-info {
        border-left: 4px solid #0dcaf0 !important;
    }
    .border-warning {
        border-left: 4px solid #ffc107 !important;
    }
    #studentSelect {
        max-height: 250px;
        overflow-y: auto;
    }
    #studentSelect option {
        padding: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    #studentSelect option:hover {
        background-color: rgba(155, 89, 182, 0.1);
    }
    #studentSelect option:checked {
        background-color: #9b59b6;
        color: white;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .page-header-breadcrumb {
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .badge.bg-info {
        background-color: #9b59b6 !important;
    }
    .btn-primary {
        background-color: #9b59b6;
        border-color: #9b59b6;
    }
    .btn-primary:hover {
        background-color: #8e44ad;
        border-color: #8e44ad;
    }
    .text-primary {
        color: #9b59b6 !important;
    }
    .border-success {
        border-color: #9b59b6 !important;
    }
    .bg-success {
        background-color: #9b59b6 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // ============ STUDENT SEARCH & FILTER ============
        let allStudents = [];
        let filteredStudents = [];
        let currentStudentIndex = 0;

        // Initialize student data
        function initStudentData() {
            allStudents = [];
            $('#studentSelect option.student-option').each(function() {
                let $this = $(this);
                allStudents.push({
                    element: $this,
                    value: $this.val(),
                    text: $this.text(),
                    reg: $this.data('reg'),
                    name: $this.data('name'),
                    programme: $this.data('programme'),
                    programmeId: $this.data('programme-id'),
                    level: $this.data('level'),
                    phone: $this.data('phone'),
                    email: $this.data('email')
                });
            });
            filteredStudents = [...allStudents];
            updateResultsCount();
            updateNavInfo();
        }

        // Search function
        function searchStudents() {
            let searchTerm = $('#studentSearchInput').val().toLowerCase().trim();
            let filterType = $('#searchFilter').val();
            
            if (searchTerm === '') {
                filteredStudents = [...allStudents];
            } else {
                filteredStudents = allStudents.filter(student => {
                    if (filterType === 'all') {
                        return (student.name && student.name.toLowerCase().includes(searchTerm)) ||
                               (student.reg && student.reg.toLowerCase().includes(searchTerm)) ||
                               (student.phone && student.phone.toLowerCase().includes(searchTerm));
                    } else if (filterType === 'name') {
                        return student.name && student.name.toLowerCase().includes(searchTerm);
                    } else if (filterType === 'reg_no') {
                        return student.reg && student.reg.toLowerCase().includes(searchTerm);
                    } else if (filterType === 'phone') {
                        return student.phone && student.phone.toLowerCase().includes(searchTerm);
                    }
                    return false;
                });
            }
            
            // Update dropdown
            $('#studentSelect').empty();
            $('#studentSelect').append('<option value="">-- Select Student --</option>');
            
            if (filteredStudents.length === 0) {
                $('#studentSelect').append('<option value="" disabled>No students found</option>');
                $('#studentNavInfo').text('0 of 0');
            } else {
                filteredStudents.forEach((student, index) => {
                    let option = $('<option>')
                        .val(student.value)
                        .attr('data-reg', student.reg)
                        .attr('data-name', student.name)
                        .attr('data-programme', student.programme)
                        .attr('data-programme-id', student.programmeId)
                        .attr('data-level', student.level)
                        .attr('data-phone', student.phone)
                        .attr('data-email', student.email)
                        .attr('class', 'student-option')
                        .attr('data-index', index)
                        .text(student.text);
                    $('#studentSelect').append(option);
                });
                currentStudentIndex = 0;
                updateNavInfo();
            }
            
            updateResultsCount();
        }

        // Update results count
        function updateResultsCount() {
            $('#searchResultsCount').text(`Found: ${filteredStudents.length} of ${allStudents.length} Students`);
        }

        // Update navigation info
        function updateNavInfo() {
            if (filteredStudents.length > 0) {
                $('#studentNavInfo').text(`${currentStudentIndex + 1} of ${filteredStudents.length}`);
            } else {
                $('#studentNavInfo').text('0 of 0');
            }
        }

        // Select student by index
        function selectStudentByIndex(index) {
            if (filteredStudents.length > 0 && index >= 0 && index < filteredStudents.length) {
                currentStudentIndex = index;
                let student = filteredStudents[index];
                $('#studentSelect').val(student.value).trigger('change');
                updateNavInfo();
            }
        }

        // ============ EVENT LISTENERS ============

        // Initialize on page load
        setTimeout(initStudentData, 500);

        // Search on input with debounce
        let searchTimeout;
        $('#studentSearchInput').on('keyup', function(e) {
            clearTimeout(searchTimeout);
            if (e.key === 'Enter') {
                searchStudents();
            } else {
                searchTimeout = setTimeout(searchStudents, 300);
            }
        });

        // Search button click
        $('#searchButton').click(function() {
            searchStudents();
        });

        // Filter change
        $('#searchFilter').change(function() {
            searchStudents();
        });

        // Clear search
        $('#clearSearchBtn').click(function() {
            $('#studentSearchInput').val('');
            $('#searchFilter').val('all');
            searchStudents();
        });

        // Previous student
        $('#prevStudentBtn').click(function() {
            if (filteredStudents.length > 0) {
                let newIndex = currentStudentIndex - 1;
                if (newIndex < 0) newIndex = filteredStudents.length - 1;
                selectStudentByIndex(newIndex);
            }
        });

        // Next student
        $('#nextStudentBtn').click(function() {
            if (filteredStudents.length > 0) {
                let newIndex = currentStudentIndex + 1;
                if (newIndex >= filteredStudents.length) newIndex = 0;
                selectStudentByIndex(newIndex);
            }
        });

        // Select current student
        $('#quickSelectCurrentStudent').click(function() {
            selectStudentByIndex(currentStudentIndex);
        });

        // Clear selection
        $('#clearStudentSelection').click(function() {
            $('#studentSelect').val('');
            $('#studentInfo').hide();
        });

        // ============ TOGGLE GENERATION TYPE ============
        $('input[name="generation_type"]').change(function() {
            if ($(this).val() === 'student') {
                $('#studentSection').show();
                $('#manualSection').hide();
                
                $('[name="full_name"]').prop('disabled', true);
                $('[name="registration_number"]').prop('disabled', true);
                $('[name="email"]').prop('disabled', true);
                $('[name="phone"]').prop('disabled', true);
                $('[name="student_id"]').prop('disabled', false);
            } else {
                $('#studentSection').hide();
                $('#manualSection').show();
                
                $('[name="full_name"]').prop('disabled', false);
                $('[name="registration_number"]').prop('disabled', false);
                $('[name="email"]').prop('disabled', false);
                $('[name="phone"]').prop('disabled', false);
                $('[name="student_id"]').prop('disabled', true);
            }
        }).trigger('change');

        // ============ TOGGLE PAYMENT CATEGORY ============
        $('#paymentCategory').change(function() {
            let category = $(this).val();
            
            // Hide all sections
            $('#tuitionFeeSection').hide();
            $('#supplementaryFeeSection').hide();
            $('#repeatFeeSection').hide();
            $('#programmeFeeSection').hide();
            $('#hostelFeeSection').hide();
            $('#otherFeeSection').hide();
            $('#summarySection').hide();
            
            // Show selected section
            if (category === 'tuition_fee') {
                $('#tuitionFeeSection').show();
            } else if (category === 'supplementary_fee') {
                $('#supplementaryFeeSection').show();
            } else if (category === 'repeat_fee') {
                $('#repeatFeeSection').show();
            } else if (category === 'programme_fee') {
                $('#programmeFeeSection').show();
            } else if (category === 'hostel_fee') {
                $('#hostelFeeSection').show();
            } else if (category === 'other_fee') {
                $('#otherFeeSection').show();
            }
            
            updateSummary();
        });

        // ============ STUDENT SELECTION CHANGE ============
        $('#studentSelect').change(function() {
            let selected = $(this).find('option:selected');
            if (selected.val()) {
                let reg = selected.data('reg');
                let name = selected.data('name');
                let programme = selected.data('programme');
                let programmeId = selected.data('programme-id');
                let level = selected.data('level');
                let phone = selected.data('phone');
                let email = selected.data('email');
                
                $('#studentInfo').show().html(`
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="feather-user text-primary fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-2 fw-bold">${name}</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Reg No:</small>
                                    <span class="fw-semibold">${reg}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Programme:</small>
                                    <span class="fw-semibold">${programme}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Phone:</small>
                                    <span class="fw-semibold">${phone}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Level:</small>
                                    <span class="fw-semibold">Level ${level}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                // Auto-select programme if tuition fee
                if ($('#paymentCategory').val() === 'tuition_fee') {
                    $('#tuitionProgrammeSelect').val(programmeId).trigger('change');
                }
                
                updateSummary();
            } else {
                $('#studentInfo').hide();
            }
        });

        // ============ TUITION FEE CALCULATION ============
        function fetchTuitionFee() {
            let programmeId = $('#tuitionProgrammeSelect').val();
            let academicYearId = $('#tuitionAcademicYearSelect').val();
            let tuitionType = $('#tuitionTypeSelect').val();
            
            if (programmeId && academicYearId) {
                $.ajax({
                    url: '/finance/api/programme-fee',
                    method: 'GET',
                    data: {
                        programme_id: programmeId,
                        academic_year_id: academicYearId,
                        tuition_type: tuitionType
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#tuitionAmount').val(response.amount.toLocaleString());
                            
                            let breakdown = `
                                <div class="border-bottom pb-2 mb-2">
                                    <strong class="text-primary">${response.fee_type}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Tuition Fee:</span>
                                    <span class="fw-semibold">${response.amount.toLocaleString()} TZS</span>
                                </div>
                            `;
                            
                            if (response.fees.registration_fee > 0) {
                                breakdown += `
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Registration Fee:</span>
                                        <span class="fw-semibold">${response.fees.registration_fee.toLocaleString()} TZS</span>
                                    </div>
                                `;
                            }
                            
                            breakdown += `
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                    <span class="fw-bold">Total Amount:</span>
                                    <span class="fw-bold text-success">${response.fees.total.toLocaleString()} TZS</span>
                                </div>
                            `;
                            
                            $('#tuitionFeeBreakdown').show().html(breakdown);
                            updateSummary();
                        }
                    }
                });
            }
        }

        $('#tuitionProgrammeSelect, #tuitionAcademicYearSelect, #tuitionTypeSelect').change(fetchTuitionFee);

        // ============ SUPPLEMENTARY FEE CALCULATION ============
        function fetchSupplementaryFee() {
            let feeId = $('#supplementaryFeeSelect').val();
            let units = $('#supplementaryUnitsSelect').val();
            
            if (feeId) {
                $.ajax({
                    url: '/finance/api/supplementary-repeat-fee',
                    method: 'GET',
                    data: {
                        fee_id: feeId,
                        fee_type: 'supplementary',
                        units: units
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#supplementaryAmount').val(response.amount.toLocaleString());
                            $('#supplementaryCourseId').val(response.course_id);
                            
                            $('#supplementaryInfo').show().html(`
                                <div class="border-bottom pb-2 mb-2">
                                    <strong class="text-info">Supplementary Fee Details</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Course:</span>
                                    <span class="fw-semibold">${response.course_code} - ${response.course_name}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Rate per Unit:</span>
                                    <span class="fw-semibold">${response.fee_per_unit.toLocaleString()} TZS</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Units:</span>
                                    <span class="fw-semibold">${response.units}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                    <span class="fw-bold">Total Amount:</span>
                                    <span class="fw-bold text-success">${response.amount.toLocaleString()} TZS</span>
                                </div>
                            `);
                            updateSummary();
                        }
                    }
                });
            } else {
                $('#supplementaryInfo').hide();
                $('#supplementaryAmount').val('');
            }
        }

        $('#supplementaryFeeSelect, #supplementaryUnitsSelect').change(fetchSupplementaryFee);

        // ============ REPEAT FEE CALCULATION ============
        function fetchRepeatFee() {
            let feeId = $('#repeatFeeSelect').val();
            let units = $('#repeatUnitsSelect').val();
            
            if (feeId) {
                $.ajax({
                    url: '/finance/api/supplementary-repeat-fee',
                    method: 'GET',
                    data: {
                        fee_id: feeId,
                        fee_type: 'repeat',
                        units: units
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#repeatAmount').val(response.amount.toLocaleString());
                            $('#repeatCourseId').val(response.course_id);
                            
                            $('#repeatInfo').show().html(`
                                <div class="border-bottom pb-2 mb-2">
                                    <strong class="text-warning">Repeat Module Fee Details</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Course:</span>
                                    <span class="fw-semibold">${response.course_code} - ${response.course_name}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Rate per Unit:</span>
                                    <span class="fw-semibold">${response.fee_per_unit.toLocaleString()} TZS</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Units:</span>
                                    <span class="fw-semibold">${response.units}</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                    <span class="fw-bold">Total Amount:</span>
                                    <span class="fw-bold text-success">${response.amount.toLocaleString()} TZS</span>
                                </div>
                            `);
                            updateSummary();
                        }
                    }
                });
            } else {
                $('#repeatInfo').hide();
                $('#repeatAmount').val('');
            }
        }

        $('#repeatFeeSelect, #repeatUnitsSelect').change(fetchRepeatFee);

        // ============ PROGRAMME FEE (LEGACY) ============
        function fetchProgrammeFee() {
            let programmeId = $('#programmeSelect').val();
            let academicYearId = $('#academicYearSelect').val();
            
            if (programmeId && academicYearId) {
                $.ajax({
                    url: '/finance/api/programme-fee',
                    method: 'GET',
                    data: {
                        programme_id: programmeId,
                        academic_year_id: academicYearId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#programmeAmount').val(response.amount.toLocaleString());
                            
                            $('#feeBreakdown').show().html(`
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Registration Fee:</span>
                                    <span class="fw-semibold">${response.fees.registration_fee?.toLocaleString() ?? 0} TZS</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Semester 1 Fee:</span>
                                    <span class="fw-semibold">${response.fees.semester_1_fee?.toLocaleString() ?? 0} TZS</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Semester 2 Fee:</span>
                                    <span class="fw-semibold">${response.fees.semester_2_fee?.toLocaleString() ?? 0} TZS</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                    <span class="fw-bold">Total Amount:</span>
                                    <span class="fw-bold text-success">${response.amount.toLocaleString()} TZS</span>
                                </div>
                            `);
                            updateSummary();
                        }
                    }
                });
            }
        }

        $('#programmeSelect, #academicYearSelect').change(fetchProgrammeFee);

        // ============ UPDATE SUMMARY ============
        function updateSummary() {
            let category = $('#paymentCategory').val();
            let payer = '';
            let feeType = '';
            let amount = 0;
            
            // Get payer name
            if ($('input[name="generation_type"]:checked').val() === 'student') {
                payer = $('#studentSelect option:selected').data('name') || '';
            } else {
                payer = $('[name="full_name"]').val() || 'Manual Entry';
            }
            
            // Get amount and fee type based on category
            if (category === 'tuition_fee') {
                amount = parseFloat($('#tuitionAmount').val().replace(/,/g, '')) || 0;
                feeType = $('#tuitionTypeSelect option:selected').text();
            } else if (category === 'supplementary_fee') {
                amount = parseFloat($('#supplementaryAmount').val().replace(/,/g, '')) || 0;
                feeType = 'Supplementary Fee';
            } else if (category === 'repeat_fee') {
                amount = parseFloat($('#repeatAmount').val().replace(/,/g, '')) || 0;
                feeType = 'Repeat Module Fee';
            } else if (category === 'programme_fee') {
                amount = parseFloat($('#programmeAmount').val().replace(/,/g, '')) || 0;
                feeType = 'Programme Fee';
            } else if (category === 'hostel_fee') {
                amount = parseFloat($('[name="hostel_amount"]').val()) || 0;
                feeType = 'Hostel Fee';
            } else if (category === 'other_fee') {
                amount = parseFloat($('[name="amount"]').val()) || 0;
                feeType = $('[name="fee_type"] option:selected').text() || 'Other Fee';
            }
            
            // Calculate expiry date
            let expiryDays = parseInt($('[name="expiry_days"]').val()) || 365;
            let expiryDate = new Date();
            expiryDate.setDate(expiryDate.getDate() + expiryDays);
            
            // Update summary
            $('#summaryRef').text('STMC-' + new Date().getFullYear() + '-.....');
            $('#summaryPayer').text(payer || 'Not specified');
            $('#summaryFeeType').text(feeType);
            $('#summaryCategory').text($('#paymentCategory option:selected').text());
            $('#summaryAmount').text(amount.toLocaleString() + ' TZS');
            $('#summaryExpiry').text(expiryDate.toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric'
            }));
            
            if (category && amount > 0) {
                $('#summarySection').show();
            } else {
                $('#summarySection').hide();
            }
        }

        // Update summary on changes
        $('[name="expiry_days"], [name="full_name"], [name="hostel_amount"], [name="amount"]').change(updateSummary);
        $('[name="fee_type"]').change(updateSummary);
        
        // Initial trigger
        setTimeout(updateSummary, 500);
    });
</script>
@endpush
@endsection