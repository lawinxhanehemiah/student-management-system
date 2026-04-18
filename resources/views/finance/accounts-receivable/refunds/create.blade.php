@extends('layouts.financecontroller')

@section('title', 'Create Refund Request')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Create Refund Request</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <a href="{{ route('finance.refunds.index') }}" class="breadcrumb-item">Refunds</a>
                <span class="breadcrumb-item active">Create</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Refund Request Details</div>
                </div>
                <div class="card-body">
                    <form id="refundForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Select Payment</label>
                                <select class="form-select" id="payment_id" name="payment_id" required>
                                    <option value="">Choose payment...</option>
                                    @foreach($payments as $payment)
                                    <option value="{{ $payment->id }}" 
                                            data-student="{{ $payment->student->user->first_name }} {{ $payment->student->user->last_name }}"
                                            data-reg="{{ $payment->student->registration_number }}"
                                            data-amount="{{ $payment->amount }}"
                                            data-method="{{ $payment->payment_method }}"
                                            data-refunded="{{ $payment->refunds->whereIn('status', ['approved', 'processed'])->sum('amount') }}"
                                            data-invoice="{{ $payment->payable->invoice_number ?? '' }}">
                                        {{ $payment->payment_number }} - {{ $payment->student->user->first_name }} {{ $payment->student->user->last_name }} ({{ number_format($payment->amount, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Refund Method</label>
                                <select class="form-select" id="refund_method" name="refund_method" required>
                                    <option value="">Select method...</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="1000" min="0" required>
                                </div>
                                <small class="text-muted" id="amountHelp"></small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Refund Reason</label>
                                <select class="form-select" id="refund_reason" name="refund_reason" required>
                                    <option value="">Select reason...</option>
                                    <option value="overpayment">Overpayment</option>
                                    <option value="duplicate_payment">Duplicate Payment</option>
                                    <option value="incorrect_amount">Incorrect Amount</option>
                                    <option value="service_cancellation">Service Cancellation</option>
                                    <option value="student_withdrawal">Student Withdrawal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <!-- Bank Transfer Fields -->
                            <div class="col-md-6 mb-3 bank-field" style="display: none;">
                                <label class="form-label required">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name">
                            </div>
                            
                            <div class="col-md-6 mb-3 bank-field" style="display: none;">
                                <label class="form-label required">Bank Account Number</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account">
                            </div>
                            
                            <!-- M-Pesa Field -->
                            <div class="col-md-6 mb-3 mpesa-field" style="display: none;">
                                <label class="form-label required">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="e.g., 0712345678">
                            </div>
                            
                            <!-- Cheque Field -->
                            <div class="col-md-6 mb-3 cheque-field" style="display: none;">
                                <label class="form-label required">Cheque Number</label>
                                <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional details..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Payment Info Card -->
                        <div class="card bg-light mt-3" id="paymentInfo" style="display: none;">
                            <div class="card-body">
                                <h6 class="fw-semibold">Selected Payment Details</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="text-muted">Student:</span>
                                        <span class="fw-semibold" id="paymentStudent"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted">Reg No:</span>
                                        <span class="fw-semibold" id="paymentReg"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Invoice:</span>
                                        <span class="fw-semibold" id="paymentInvoice"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Amount:</span>
                                        <span class="fw-semibold" id="paymentAmount"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Previously Refunded:</span>
                                        <span class="fw-semibold" id="paymentRefunded"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('finance.refunds.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Refund Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Show payment info when payment selected
    $('#payment_id').change(function() {
        const selected = $(this).find('option:selected');
        
        if (selected.val()) {
            const studentName = selected.data('student');
            const studentReg = selected.data('reg');
            const amount = selected.data('amount');
            const refunded = selected.data('refunded') || 0;
            const invoice = selected.data('invoice');
            const available = amount - refunded;
            
            $('#paymentStudent').text(studentName);
            $('#paymentReg').text(studentReg);
            $('#paymentInvoice').text(invoice);
            $('#paymentAmount').text('TZS ' + Number(amount).toLocaleString());
            $('#paymentRefunded').text('TZS ' + Number(refunded).toLocaleString());
            $('#paymentInfo').show();
            
            // Set max amount
            $('#amount').attr('max', available);
            $('#amountHelp').text('Maximum refundable: TZS ' + Number(available).toLocaleString());
        } else {
            $('#paymentInfo').hide();
            $('#amount').attr('max', '');
            $('#amountHelp').text('');
        }
    });
    
    // Toggle refund method fields
    $('#refund_method').change(function() {
        const method = $(this).val();
        
        $('.bank-field, .mpesa-field, .cheque-field').hide();
        $('.bank-field input, .mpesa-field input, .cheque-field input').prop('required', false);
        
        if (method === 'bank_transfer') {
            $('.bank-field').show();
            $('#bank_name, #bank_account').prop('required', true);
        } else if (method === 'mpesa') {
            $('.mpesa-field').show();
            $('#phone_number').prop('required', true);
        } else if (method === 'cheque') {
            $('.cheque-field').show();
            $('#cheque_number').prop('required', true);
        }
    });
    
    // Form submission
    $('#refundForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            payment_id: $('#payment_id').val(),
            refund_method: $('#refund_method').val(),
            amount: $('#amount').val(),
            refund_reason: $('#refund_reason').val(),
            description: $('#description').val(),
            bank_name: $('#bank_name').val(),
            bank_account: $('#bank_account').val(),
            phone_number: $('#phone_number').val(),
            cheque_number: $('#cheque_number').val(),
            _token: '{{ csrf_token() }}'
        };
        
        // Validate
        if (!formData.payment_id) {
            Swal.fire('Error', 'Please select a payment', 'error');
            return;
        }
        
        if (!formData.refund_method) {
            Swal.fire('Error', 'Please select refund method', 'error');
            return;
        }
        
        if (!formData.amount || formData.amount <= 0) {
            Swal.fire('Error', 'Please enter a valid amount', 'error');
            return;
        }
        
        if (!formData.refund_reason) {
            Swal.fire('Error', 'Please select a reason', 'error');
            return;
        }
        
        const selectedPayment = $('#payment_id').find('option:selected');
        const amount = selectedPayment.data('amount');
        const refunded = selectedPayment.data('refunded') || 0;
        const available = amount - refunded;
        
        if (formData.amount > available) {
            Swal.fire('Error', 'Amount exceeds available refundable amount', 'error');
            return;
        }
        
        // Validate method-specific fields
        if (formData.refund_method === 'bank_transfer') {
            if (!formData.bank_name || !formData.bank_account) {
                Swal.fire('Error', 'Please fill in bank details', 'error');
                return;
            }
        } else if (formData.refund_method === 'mpesa') {
            if (!formData.phone_number) {
                Swal.fire('Error', 'Please enter phone number', 'error');
                return;
            }
            // Validate phone format
            const phone = formData.phone_number.replace(/\D/g, '');
            if (!phone.match(/^(0|255)?[67]\d{8}$/)) {
                Swal.fire('Error', 'Invalid phone number format', 'error');
                return;
            }
        } else if (formData.refund_method === 'cheque') {
            if (!formData.cheque_number) {
                Swal.fire('Error', 'Please enter cheque number', 'error');
                return;
            }
        }
        
        // Submit
        $.ajax({
            url: '{{ route("finance.refunds.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'View Refund'
                    }).then(() => {
                        window.location.href = '{{ route("finance.refunds.index") }}';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    for (let field in errors) {
                        errorMessage += errors[field].join('\n') + '\n';
                    }
                    Swal.fire('Validation Error', errorMessage, 'error');
                } else {
                    Swal.fire('Error', 'Failed to create refund request', 'error');
                }
            }
        });
    });
});
</script>
@endpush
@endsection