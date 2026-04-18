@extends('layouts.financecontroller')

@section('title', 'Create Credit Note')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Create Credit Note</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <a href="{{ route('finance.credit-notes.index') }}" class="breadcrumb-item">Credit Notes</a>
                <span class="breadcrumb-item active">Create</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Credit Note Details</div>
                </div>
                <div class="card-body">
                    <form id="creditNoteForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Select Invoice</label>
                                <select class="form-select" id="invoice_id" name="invoice_id" required>
                                    <option value="">Choose invoice...</option>
                                    @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" 
                                            data-student="{{ $invoice->student->user->first_name }} {{ $invoice->student->user->last_name }}"
                                            data-reg="{{ $invoice->student->registration_number }}"
                                            data-amount="{{ $invoice->total_amount }}"
                                            data-paid="{{ $invoice->paid_amount }}"
                                            data-balance="{{ $invoice->balance }}">
                                        {{ $invoice->invoice_number }} - {{ $invoice->student->user->first_name }} {{ $invoice->student->user->last_name }} ({{ number_format($invoice->balance, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Reason for Credit Note</label>
                                <select class="form-select" id="reason" name="reason" required>
                                    <option value="">Select reason...</option>
                                    <option value="overpayment">Overpayment</option>
                                    <option value="invoice_error">Invoice Error</option>
                                    <option value="service_cancellation">Service Cancellation</option>
                                    <option value="discount">Discount Adjustment</option>
                                    <option value="refund">Refund Request</option>
                                    <option value="other">Other</option>
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
                                <label class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ now()->addYear()->format('Y-m-d') }}">
                                <small class="text-muted">Leave empty for 1 year default</small>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional details..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Student Info Card -->
                        <div class="card bg-light mt-3" id="studentInfo" style="display: none;">
                            <div class="card-body">
                                <h6 class="fw-semibold">Selected Invoice Details</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="text-muted">Student:</span>
                                        <span class="fw-semibold" id="studentName"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted">Reg No:</span>
                                        <span class="fw-semibold" id="studentReg"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Invoice Total:</span>
                                        <span class="fw-semibold" id="invoiceTotal"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Paid:</span>
                                        <span class="fw-semibold" id="invoicePaid"></span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted">Balance:</span>
                                        <span class="fw-semibold" id="invoiceBalance"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('finance.credit-notes.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Credit Note</button>
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
    // Show student info when invoice selected
    $('#invoice_id').change(function() {
        const selected = $(this).find('option:selected');
        
        if (selected.val()) {
            const studentName = selected.data('student');
            const studentReg = selected.data('reg');
            const total = selected.data('amount');
            const paid = selected.data('paid');
            const balance = selected.data('balance');
            
            $('#studentName').text(studentName);
            $('#studentReg').text(studentReg);
            $('#invoiceTotal').text('TZS ' + Number(total).toLocaleString());
            $('#invoicePaid').text('TZS ' + Number(paid).toLocaleString());
            $('#invoiceBalance').text('TZS ' + Number(balance).toLocaleString());
            $('#studentInfo').show();
            
            // Set max amount
            $('#amount').attr('max', balance);
            $('#amountHelp').text('Maximum amount: TZS ' + Number(balance).toLocaleString());
        } else {
            $('#studentInfo').hide();
            $('#amount').attr('max', '');
            $('#amountHelp').text('');
        }
    });
    
    // Form submission
    $('#creditNoteForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            invoice_id: $('#invoice_id').val(),
            reason: $('#reason').val(),
            amount: $('#amount').val(),
            expiry_date: $('#expiry_date').val(),
            description: $('#description').val(),
            _token: '{{ csrf_token() }}'
        };
        
        // Validate
        if (!formData.invoice_id) {
            Swal.fire('Error', 'Please select an invoice', 'error');
            return;
        }
        
        if (!formData.reason) {
            Swal.fire('Error', 'Please select a reason', 'error');
            return;
        }
        
        if (!formData.amount || formData.amount <= 0) {
            Swal.fire('Error', 'Please enter a valid amount', 'error');
            return;
        }
        
        const maxAmount = $('#invoice_id').find('option:selected').data('balance');
        if (formData.amount > maxAmount) {
            Swal.fire('Error', 'Amount cannot exceed invoice balance', 'error');
            return;
        }
        
        // Submit
        $.ajax({
            url: '{{ route("finance.credit-notes.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Print Credit Note',
                        cancelButtonText: 'Back to List'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(response.print_url, '_blank');
                        }
                        window.location.href = '{{ route("finance.credit-notes.index") }}';
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
                    Swal.fire('Error', 'Failed to create credit note', 'error');
                }
            }
        });
    });
});
</script>
@endpush
@endsection