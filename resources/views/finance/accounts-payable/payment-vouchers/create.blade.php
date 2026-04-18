@extends('layouts.financecontroller')

@section('title', 'Create Payment Voucher')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Payment Voucher</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.payment-vouchers.index') }}" class="text-muted">Payment Vouchers</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.accounts-payable.payment-vouchers.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <form id="voucherForm" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <!-- Main Voucher Info -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Payment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="supplier_id" id="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Supplier Invoice</label>
                                <select class="form-select form-select-sm" name="supplier_invoice_id" id="invoice_id">
                                    <option value="">Select Invoice (Optional)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="payment_date" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="payment_method" id="payment_method" required>
                                    <option value="">Select</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="mpesa" {{ old('payment_method') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="amount" 
                                       value="{{ old('amount') }}" step="100" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details (shown/hidden based on method) -->
                <div class="card border-0 shadow-sm mb-3" id="bankDetails" style="display: none;">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Bank Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Bank Name</label>
                                <input type="text" class="form-control form-control-sm" name="bank_name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Account Number</label>
                                <input type="text" class="form-control form-control-sm" name="bank_account">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Reference/Cheque No</label>
                                <input type="text" class="form-control form-control-sm" name="reference_number">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Description</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control form-control-sm" name="description" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Invoice Summary -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Invoice Summary</h6>
                    </div>
                    <div class="card-body" id="invoiceSummary">
                        <p class="text-muted small mb-0">Select an invoice to view details</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-semibold">Notes</h6>
            </div>
            <div class="card-body">
                <textarea class="form-control form-control-sm" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-sm btn-light" onclick="saveAsDraft()">Save as Draft</button>
            <button type="button" class="btn btn-sm btn-primary" onclick="submitForApproval()">Submit for Approval</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Toggle bank details based on payment method
document.getElementById('payment_method').addEventListener('change', function() {
    const method = this.value;
    const bankDetails = document.getElementById('bankDetails');
    
    if (method === 'bank_transfer' || method === 'cheque') {
        bankDetails.style.display = 'block';
    } else {
        bankDetails.style.display = 'none';
    }
});

// Load invoices when supplier is selected
document.getElementById('supplier_id').addEventListener('change', function() {
    const supplierId = this.value;
    const invoiceSelect = document.getElementById('invoice_id');
    
    if (supplierId) {
        fetch(`/finance/accounts-payable/api/suppliers/${supplierId}/invoices`)
            .then(response => response.json())
            .then(data => {
                invoiceSelect.innerHTML = '<option value="">Select Invoice</option>';
                data.forEach(invoice => {
                    invoiceSelect.innerHTML += `<option value="${invoice.id}" 
                        data-total="${invoice.total_amount}"
                        data-paid="${invoice.paid_amount}"
                        data-balance="${invoice.balance}"
                        data-number="${invoice.invoice_number}">
                        ${invoice.invoice_number} - TZS ${invoice.balance.toLocaleString()}
                    </option>`;
                });
            });
    }
});

// Show invoice summary when invoice is selected
document.getElementById('invoice_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const summary = document.getElementById('invoiceSummary');
    
    if (this.value) {
        const total = selected.dataset.total;
        const paid = selected.dataset.paid;
        const balance = selected.dataset.balance;
        const number = selected.dataset.number;
        
        summary.innerHTML = `
            <div class="small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Invoice:</span>
                    <span class="fw-semibold">${number}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total:</span>
                    <span>TZS ${Number(total).toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid:</span>
                    <span class="text-success">TZS ${Number(paid).toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Balance:</span>
                    <span class="fw-semibold text-danger">TZS ${Number(balance).toLocaleString()}</span>
                </div>
                <hr>
                <div class="text-muted small">
                    Max payment: TZS ${Number(balance).toLocaleString()}
                </div>
            </div>
        `;
        
        // Set max amount
        document.querySelector('[name="amount"]').max = balance;
    } else {
        summary.innerHTML = '<p class="text-muted small mb-0">Select an invoice to view details</p>';
    }
});

function submitForm(status) {
    const form = document.getElementById('voucherForm');
    const formData = new FormData(form);
    formData.append('status', status);
    
    fetch('{{ route("finance.accounts-payable.payment-vouchers.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create payment voucher');
    });
}

function saveAsDraft() {
    submitForm('draft');
}

function submitForApproval() {
    submitForm('pending');
}

// If invoice ID is passed in URL
@if(request('invoice_id'))
document.addEventListener('DOMContentLoaded', function() {
    // Set supplier and invoice from URL
    @if($invoice)
        // Will be handled by existing code
    @endif
});
@endif
</script>
@endpush
@endsection