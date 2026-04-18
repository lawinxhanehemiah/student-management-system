{{-- resources/views/finance/invoices/create.blade.php --}}
@extends('layouts.financecontroller')

@section('title', 'Generate Invoice - Repeat/Supplementary/Hostel')

@push('styles')
<style>
    /* Finance specific styles for invoice generation */
    .finance-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .finance-card:hover {
        box-shadow: 0 5px 30px rgba(39, 174, 96, 0.15);
    }

    .finance-card .card-header {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        border-radius: 15px 15px 0 0 !important;
        padding: 20px 25px;
        border: none;
    }

    .finance-card .card-header h4 {
        color: white;
        font-weight: 600;
        margin: 0;
        font-size: 1.3rem;
    }

    .finance-card .card-header p {
        color: rgba(255,255,255,0.9);
        margin: 5px 0 0 0;
        font-size: 0.9rem;
    }

    .finance-badge {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .student-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #27ae60;
        margin-top: 20px;
    }

    .student-info-item {
        padding: 8px 0;
        border-bottom: 1px dashed #e0e0e0;
    }

    .student-info-item:last-child {
        border-bottom: none;
    }

    .student-info-label {
        color: #7f8c8d;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .student-info-value {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1rem;
    }

    .fee-display-box {
        background: white;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        height: 100%;
    }

    .fee-display-box.repeat {
        border-top: 3px solid #3498db;
    }

    .fee-display-box.supplementary {
        border-top: 3px solid #e67e22;
    }

    .fee-display-box.hostel {
        border-top: 3px solid #17a2b8;
    }

    .fee-display-box .fee-label {
        color: #7f8c8d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .fee-display-box .fee-amount {
        color: #2c3e50;
        font-weight: 700;
        font-size: 1.5rem;
        margin: 10px 0;
    }

    .fee-display-box .fee-status {
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
    }

    .fee-status.configured {
        background: #d4edda;
        color: #155724;
    }

    .fee-status.not-configured {
        background: #f8d7da;
        color: #721c24;
    }

    .form-control:focus, .form-select:focus {
        border-color: #27ae60;
        box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
    }

    .btn-finance {
        background: #27ae60;
        color: white;
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .btn-finance:hover {
        background: #219a52;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
    }

    .btn-finance:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-outline-finance {
        background: transparent;
        color: #27ae60;
        border: 2px solid #27ae60;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-outline-finance:hover {
        background: #27ae60;
        color: white;
    }

    .search-box {
        position: relative;
    }

    .search-box .form-control {
        padding-left: 45px;
        height: 50px;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }

    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #27ae60;
        font-size: 1.2rem;
    }

    .stats-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #27ae60;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .stats-card .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }

    .stats-card .stats-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-card .stats-icon {
        font-size: 2.5rem;
        color: rgba(39, 174, 96, 0.2);
    }

    .invoice-preview-modal .modal-content {
        border-radius: 15px;
        border: none;
    }

    .invoice-preview-modal .modal-header {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 20px 25px;
    }

    .invoice-preview-modal .modal-header .close {
        color: white;
        opacity: 1;
    }

    .invoice-preview-modal .modal-body {
        padding: 30px;
    }

    .control-number-display {
        background: #f8f9fa;
        border: 2px dashed #27ae60;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin: 20px 0;
    }

    .control-number-display .label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .control-number-display .number {
        color: #27ae60;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 2px;
        font-family: monospace;
    }

    .amount-display {
        background: #27ae60;
        color: white;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .warning-message {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .success-message {
        background: #d4edda;
        border-left: 4px solid #27ae60;
        color: #155724;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .info-message {
        background: #d1ecf1;
        border-left: 4px solid #17a2b8;
        color: #0c5460;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .timeline-step {
        display: flex;
        margin-bottom: 30px;
        position: relative;
    }

    .timeline-step .step-number {
        width: 40px;
        height: 40px;
        background: #27ae60;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .timeline-step .step-content {
        flex-grow: 1;
    }

    .timeline-step .step-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .timeline-step .step-description {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .timeline-step::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 50px;
        bottom: -30px;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-step:last-child::after {
        display: none;
    }

    .payment-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .payment-status.paid {
        background: #d4edda;
        color: #155724;
    }

    .payment-status.partial {
        background: #fff3cd;
        color: #856404;
    }

    .payment-status.unpaid {
        background: #f8d7da;
        color: #721c24;
    }

    .payment-status.overdue {
        background: #f8d7da;
        color: #721c24;
        font-weight: 600;
    }

    .info-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-section h6 {
        color: #27ae60;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        color: #2c3e50;
    }

    .info-item i {
        width: 24px;
        color: #27ae60;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Generate Invoice</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Generate New Invoice</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.invoices.index') }}" class="btn-outline-finance">
            <i class="fas fa-list me-2"></i>View All Invoices
        </a>
    </div>

    
    <!-- Main Generate Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="finance-card card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4><i class="fas fa-file-invoice me-2"></i>Generate Invoice</h4>
                            <p class="mb-0">Enter student registration number to search and generate invoice</p>
                        </div>
                        <span class="finance-badge">Finance Controller</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Search Section -->
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" 
                                       class="form-control" 
                                       id="registration_number" 
                                       placeholder="Enter registration number (e.g., SMC2024/001)" 
                                       autocomplete="off"
                                       value="{{ request('reg_no', '') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button class="btn-finance w-100" id="searchBtn" style="height: 50px;">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Format: 02.0401.03.2026 or any valid registration number
                            </small>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" style="display: none;" class="text-center my-5">
                        <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Searching for student...</p>
                    </div>

                    <!-- No Student Found Message -->
                    <div id="noStudentMessage" style="display: none; color: red;" class="info-message">
                        <i class="fas fa-info-circle me-2"></i>
                        No student found with this registration number. Please verify and try again.
                    </div>

                    <!-- Student Details Section (Hidden initially) -->
                    <div id="studentDetailsSection" style="display: none;">
                        <!-- Step 1: Student Information -->
                        <div class="timeline-step mt-4">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <div class="step-title">Student Information</div>
                                <div class="step-description">Confirm student details before generating invoice</div>
                            </div>
                        </div>

                        <div class="student-info-card">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Full Name:</span>
                                        <span class="student-info-value" id="displayName">-</span>
                                    </div>
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Registration No:</span>
                                        <span class="student-info-value" id="displayRegNo">-</span>
                                    </div>
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Programme:</span>
                                        <span class="student-info-value" id="displayProgramme">-</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Current Level:</span>
                                        <span class="student-info-value" id="displayLevel">-</span>
                                    </div>
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Semester:</span>
                                        <span class="student-info-value" id="displaySemester">-</span>
                                    </div>
                                    <div class="student-info-item d-flex justify-content-between">
                                        <span class="student-info-label">Academic Year:</span>
                                        <span class="student-info-value" id="displayAcademicYear">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Fee Configuration -->
                        <div class="timeline-step mt-4">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <div class="step-title">Fee Configuration</div>
                                <div class="step-description">System will automatically use configured fee amounts</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="fee-display-box repeat">
                                    <div class="fee-label">
                                        <i class="fas fa-redo-alt me-1 text-primary"></i>
                                        Repeat Module
                                    </div>
                                    <div class="fee-amount" id="repeatFeeAmount">TZS 0</div>
                                    <div class="fee-status" id="repeatFeeStatus">Not Configured</div>
                                    <input type="hidden" id="repeatFeeValue" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-display-box supplementary">
                                    <div class="fee-label">
                                        <i class="fas fa-plus-circle me-1 text-warning"></i>
                                        Supplementary
                                    </div>
                                    <div class="fee-amount" id="supplementaryFeeAmount">TZS 0</div>
                                    <div class="fee-status" id="supplementaryFeeStatus">Not Configured</div>
                                    <input type="hidden" id="supplementaryFeeValue" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-display-box hostel">
                                    <div class="fee-label">
                                        <i class="fas fa-bed me-1 text-info"></i>
                                        Hostel
                                    </div>
                                    <div class="fee-amount" id="hostelFeeAmount">TZS 0</div>
                                    <div class="fee-status" id="hostelFeeStatus">Not Configured</div>
                                    <input type="hidden" id="hostelFeeValue" value="0">
                                </div>
                            </div>
                        </div>

                        <!-- Warning for existing invoices -->
                        <div id="existingInvoiceWarning" style="display: none;" class="warning-message">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="warningMessage"></span>
                        </div>

                        <!-- Step 3: Generate Invoice -->
                        <div class="timeline-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <div class="step-title">Generate Invoice</div>
                                <div class="step-description">Fill invoice details and generate</div>
                            </div>
                        </div>

                        <form id="generateInvoiceForm">
                            @csrf
                            <input type="hidden" name="student_id" id="student_id">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag me-1 text-success"></i>
                                        Invoice Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="invoice_type" name="invoice_type" required>
                                        <option value="">Select Invoice Type</option>
                                        <option value="repeat">🔁 Repeat Module</option>
                                        <option value="supplementary">➕ Supplementary</option>
                                        <option value="hostel">🏠 Hostel Fee</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-1 text-success"></i>
                                        Academic Year <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                                {{ $year->name }} {{ $year->is_active ? '(Current)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-money-bill-wave me-1 text-success"></i>
                                        Amount (Auto-filled)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">TZS</span>
                                        <input type="text" 
                                               class="form-control bg-light" 
                                               id="amount_display" 
                                               readonly 
                                               placeholder="Select invoice type">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Amount is automatically set from configured fees
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-check me-1 text-success"></i>
                                        Due Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="due_date" 
                                           name="due_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           value="{{ date('Y-m-d', strtotime('+365 days')) }}"
                                           required>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Due date: 1 year from today ({{ date('d M Y', strtotime('+365 days')) }})
                                    </small>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-align-left me-1 text-success"></i>
                                    Description (Optional)
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="2" 
                                          placeholder="Enter any additional notes or description"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn-finance btn-lg" id="generateBtn">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    Generate Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        

<!-- Success Modal -->
<div class="modal fade invoice-preview-modal" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Invoice Generated Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="control-number-display">
                        <div class="label">Control Number</div>
                        <div class="number" id="modalControlNumber">-</div>
                        <button class="btn btn-sm btn-outline-success mt-2" onclick="copyControlNumber()">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Invoice Number:</td>
                                <td class="fw-bold" id="modalInvoiceNumber">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Student Name:</td>
                                <td class="fw-bold" id="modalStudentName">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Registration No:</td>
                                <td class="fw-bold" id="modalStudentRegNo">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Invoice Type:</td>
                                <td class="fw-bold" id="modalInvoiceType">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Amount:</td>
                                <td class="fw-bold amount-display" id="modalAmount">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Issue Date:</td>
                                <td class="fw-bold" id="modalIssueDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Due Date:</td>
                                <td class="fw-bold" id="modalDueDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td><span class="payment-status unpaid" id="modalStatus">Unpaid</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="success-message mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Control number has been generated. Student can use this number to make payment.
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="printInvoiceBtn" class="btn btn-outline-secondary" target="_blank">
                    <i class="fas fa-print me-2"></i>Print
                </a>
                <a href="#" id="downloadInvoiceBtn" class="btn btn-outline-success">
                    <i class="fas fa-download me-2"></i>Download PDF
                </a>
                <button type="button" class="btn-finance" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Existing Invoice Warning Modal -->
<div class="modal fade" id="existingInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Existing Unpaid Invoice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="existingInvoiceModalMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="proceedGenerateBtn">Generate Anyway</button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Invoice page loaded');
    
    // Get elements
    const searchBtn = document.getElementById('searchBtn');
    const regNoInput = document.getElementById('registration_number');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const studentSection = document.getElementById('studentDetailsSection');
    const noStudentMsg = document.getElementById('noStudentMessage');
    const generateBtn = document.getElementById('generateBtn');
    const invoiceType = document.getElementById('invoice_type');
    const amountDisplay = document.getElementById('amount_display');
    const studentId = document.getElementById('student_id');
    const academicYear = document.getElementById('academic_year_id');
    const repeatFeeValue = document.getElementById('repeatFeeValue');
    const suppFeeValue = document.getElementById('supplementaryFeeValue');
    const hostelFeeValue = document.getElementById('hostelFeeValue');
    const repeatFeeAmount = document.getElementById('repeatFeeAmount');
    const suppFeeAmount = document.getElementById('supplementaryFeeAmount');
    const hostelFeeAmount = document.getElementById('hostelFeeAmount');
    const repeatFeeStatus = document.getElementById('repeatFeeStatus');
    const suppFeeStatus = document.getElementById('supplementaryFeeStatus');
    const hostelFeeStatus = document.getElementById('hostelFeeStatus');
    const existingWarning = document.getElementById('existingInvoiceWarning');
    const warningMessage = document.getElementById('warningMessage');
    
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Search button click
    searchBtn.addEventListener('click', function() {
        searchStudent();
    });
    
    // Enter key on input
    regNoInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchStudent();
        }
    });
    
    // Invoice type change
    invoiceType.addEventListener('change', function() {
        updateAmountDisplay();
    });
    
    // Format number helper
    function formatNumber(num, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    }
    
    // Update amount display based on invoice type
    function updateAmountDisplay() {
        const type = invoiceType.value;
        let amount = 0;
        
        if (type === 'repeat') {
            amount = parseFloat(repeatFeeValue.value) || 0;
            amountDisplay.value = 'TZS ' + formatNumber(amount, 0);
        } else if (type === 'supplementary') {
            amount = parseFloat(suppFeeValue.value) || 0;
            amountDisplay.value = 'TZS ' + formatNumber(amount, 0);
        } else if (type === 'hostel') {
            amount = parseFloat(hostelFeeValue.value) || 0;
            amountDisplay.value = 'TZS ' + formatNumber(amount, 0);
        } else {
            amountDisplay.value = '';
        }
        
        // Enable/disable generate button
        if (type && amount > 0) {
            generateBtn.disabled = false;
        } else {
            generateBtn.disabled = true;
        }
    }
    
    // Search student function
    function searchStudent() {
        const regNo = regNoInput.value.trim();
        
        if (!regNo) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Field',
                text: 'Please enter registration number',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        // Show loading, hide others
        loadingSpinner.style.display = 'block';
        studentSection.style.display = 'none';
        noStudentMsg.style.display = 'none';
        
        // Make AJAX request
        fetch('{{ route("finance.invoices.get-student") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                registration_number: regNo
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.style.display = 'none';
            
            if (data.success) {
                // Populate student details
                studentId.value = data.student.id;
                document.getElementById('displayName').textContent = data.student.name;
                document.getElementById('displayRegNo').textContent = data.student.registration_number;
                document.getElementById('displayProgramme').textContent = data.student.programme;
                document.getElementById('displayLevel').textContent = 'Level ' + data.student.level;
                document.getElementById('displaySemester').textContent = 'Semester ' + data.student.semester;
                
                // Get academic year name
                const academicYearName = data.student.academic_year_name || 
                    (academicYear.options[academicYear.selectedIndex]?.text || 'N/A');
                document.getElementById('displayAcademicYear').textContent = academicYearName;
                
                // Set academic year
                if (data.student.academic_year_id) {
                    academicYear.value = data.student.academic_year_id;
                }
                
                // Set fee amounts
                const repeatFee = data.student.repeat_fee || 0;
                const suppFee = data.student.supplementary_fee || 0;
                const hostelFee = data.student.hostel_fee || 0;
                
                repeatFeeValue.value = repeatFee;
                suppFeeValue.value = suppFee;
                hostelFeeValue.value = hostelFee;
                
                repeatFeeAmount.textContent = 'TZS ' + formatNumber(repeatFee, 0);
                suppFeeAmount.textContent = 'TZS ' + formatNumber(suppFee, 0);
                hostelFeeAmount.textContent = 'TZS ' + formatNumber(hostelFee, 0);
                
                // Update fee status
                updateFeeStatus(repeatFeeStatus, repeatFee);
                updateFeeStatus(suppFeeStatus, suppFee);
                updateFeeStatus(hostelFeeStatus, hostelFee);
                
                // Show student section
                studentSection.style.display = 'block';
                
                // Enable generate button if any fee is configured
                if (repeatFee > 0 || suppFee > 0 || hostelFee > 0) {
                    generateBtn.disabled = false;
                }
                
                // Check existing invoices
                checkExistingInvoices(data.student.id);
                
            } else {
                noStudentMsg.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingSpinner.style.display = 'none';
            noStudentMsg.style.display = 'block';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to search student',
                timer: 3000,
                showConfirmButton: false
            });
        });
    }
    
    function updateFeeStatus(element, value) {
        if (value > 0) {
            element.textContent = 'Configured';
            element.className = 'fee-status configured';
        } else {
            element.textContent = 'Not Configured';
            element.className = 'fee-status not-configured';
        }
    }
    
    // Check for existing unpaid invoices
    function checkExistingInvoices(studentId) {
        const academicYearId = academicYear.value;
        
        // Check repeat
        fetch('{{ route("finance.invoices.verify-fee") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                student_id: studentId,
                academic_year_id: academicYearId,
                invoice_type: 'repeat'
            })
        })
        .then(response => response.json())
        .then(data => {
            window.hasUnpaidRepeat = data.has_unpaid || false;
        });
        
        // Check supplementary
        fetch('{{ route("finance.invoices.verify-fee") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                student_id: studentId,
                academic_year_id: academicYearId,
                invoice_type: 'supplementary'
            })
        })
        .then(response => response.json())
        .then(data => {
            window.hasUnpaidSupplementary = data.has_unpaid || false;
        });
        
        // Check hostel
        fetch('{{ route("finance.invoices.verify-fee") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                student_id: studentId,
                academic_year_id: academicYearId,
                invoice_type: 'hostel'
            })
        })
        .then(response => response.json())
        .then(data => {
            window.hasUnpaidHostel = data.has_unpaid || false;
        });
    }
    
    // Generate invoice form submit
    document.getElementById('generateInvoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const type = invoiceType.value;
        
        if (!type) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Type',
                text: 'Please select invoice type',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        let amount = 0;
        if (type === 'repeat') amount = parseFloat(repeatFeeValue.value);
        else if (type === 'supplementary') amount = parseFloat(suppFeeValue.value);
        else if (type === 'hostel') amount = parseFloat(hostelFeeValue.value);
        
        if (amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Fee Not Configured',
                text: 'Fee amount not configured for this student',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }
        
        // Check for existing unpaid invoice
        let hasUnpaid = false;
        if (type === 'repeat') hasUnpaid = window.hasUnpaidRepeat;
        else if (type === 'supplementary') hasUnpaid = window.hasUnpaidSupplementary;
        else if (type === 'hostel') hasUnpaid = window.hasUnpaidHostel;
        
        if (hasUnpaid) {
            document.getElementById('existingInvoiceModalMessage').textContent = 
                `Student already has an unpaid ${type} invoice. Are you sure you want to generate another one?`;
            new bootstrap.Modal(document.getElementById('existingInvoiceModal')).show();
            
            document.getElementById('proceedGenerateBtn').onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('existingInvoiceModal')).hide();
                submitGenerateForm();
            };
        } else {
            submitGenerateForm();
        }
    });
    
    // Submit form to generate invoice
    function submitGenerateForm() {
        const formData = {
            student_id: studentId.value,
            academic_year_id: academicYear.value,
            invoice_type: invoiceType.value,
            description: document.getElementById('description').value,
            due_date: document.getElementById('due_date').value
        };
        
        // Show loading
        Swal.fire({
            title: 'Generating Invoice',
            text: 'Please wait...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit
        fetch('{{ route("finance.invoices.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                // Populate modal
                document.getElementById('modalControlNumber').textContent = data.invoice.control_number;
                document.getElementById('modalInvoiceNumber').textContent = data.invoice.invoice_number;
                document.getElementById('modalStudentName').textContent = data.invoice.student_name;
                document.getElementById('modalStudentRegNo').textContent = data.invoice.student_reg_no;
                
                let typeDisplay = data.invoice.type;
                if (typeDisplay === 'repeat') typeDisplay = 'Repeat Module';
                else if (typeDisplay === 'supplementary') typeDisplay = 'Supplementary';
                else if (typeDisplay === 'hostel') typeDisplay = 'Hostel';
                document.getElementById('modalInvoiceType').textContent = typeDisplay;
                
                document.getElementById('modalAmount').textContent = 'TZS ' + data.invoice.amount;
                document.getElementById('modalIssueDate').textContent = data.invoice.issue_date;
                document.getElementById('modalDueDate').textContent = data.invoice.due_date;
                
                // Set print and download URLs
                document.getElementById('printInvoiceBtn').href = data.print_url;
                document.getElementById('downloadInvoiceBtn').href = data.download_url;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('successModal')).show();
                
                // Reset form
                invoiceType.value = '';
                amountDisplay.value = '';
                document.getElementById('description').value = '';
                
                // Load recent activity
                loadRecentActivity();
                
                // Update stats
                loadStatistics();
                
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate invoice',
                timer: 3000,
                showConfirmButton: false
            });
        });
    }
    
    // Load statistics
    function loadStatistics() {
        fetch('{{ route("finance.invoices.statistics") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('todayCount').textContent = data.today.count;
                    document.getElementById('weekAmount').textContent = formatNumber(data.week.amount, 0);
                    document.getElementById('monthAmount').textContent = formatNumber(data.month.amount, 0);
                    document.getElementById('pendingCount').textContent = data.pending.count;
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }
    
    // Load recent activity
    function loadRecentActivity() {
        fetch('{{ route("finance.invoices.recent") }}')
            .then(response => response.json())
            .then(data => {
                const recentDiv = document.getElementById('recentActivity');
                
                if (data.success && data.activities.length > 0) {
                    let html = '';
                    data.activities.forEach(activity => {
                        let badgeClass = 'primary';
                        let icon = 'redo-alt';
                        
                        if (activity.type === 'supplementary') {
                            badgeClass = 'warning';
                            icon = 'plus-circle';
                        } else if (activity.type === 'hostel') {
                            badgeClass = 'info';
                            icon = 'bed';
                        }
                        
                        html += `
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <div class="flex-shrink-0 me-3">
                                    <span class="badge bg-${badgeClass} rounded-pill p-2">
                                        <i class="fas fa-${icon}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${activity.invoice_number}</div>
                                    <small class="text-muted">${activity.student_name} - TZS ${formatNumber(activity.amount, 0)}</small>
                                </div>
                                <small class="text-muted">${activity.time_ago}</small>
                            </div>
                        `;
                    });
                    recentDiv.innerHTML = html;
                } else {
                    recentDiv.innerHTML = '<p class="text-muted text-center py-3">No recent activity</p>';
                }
            })
            .catch(error => {
                console.error('Error loading activity:', error);
                document.getElementById('recentActivity').innerHTML = '<p class="text-muted text-center py-3">Failed to load activity</p>';
            });
    }
    
    // Copy control number
    window.copyControlNumber = function() {
        const controlNumber = document.getElementById('modalControlNumber').textContent;
        navigator.clipboard.writeText(controlNumber).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Control number copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    };
    
    // Auto-search if registration number in URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlRegNo = urlParams.get('reg_no');
    if (urlRegNo) {
        regNoInput.value = urlRegNo;
        searchStudent();
    }
    
    // Load initial data
    loadStatistics();
    loadRecentActivity();
});
</script>
@endpush