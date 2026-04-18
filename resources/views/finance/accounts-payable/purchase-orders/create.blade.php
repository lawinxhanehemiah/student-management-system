@extends('layouts.financecontroller')

@section('title', 'Create Purchase Order')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Purchase Order</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}" class="text-muted">Purchase Orders</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.accounts-payable.purchase-orders.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <form id="poForm" method="POST">
        @csrf
        
        <!-- Supplier Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-semibold">Supplier Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" 
                                name="supplier_id" id="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->supplier_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Order Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm @error('order_date') is-invalid @enderror" 
                               name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                        @error('order_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Expected Delivery</label>
                        <input type="date" class="form-control form-control-sm @error('expected_delivery_date') is-invalid @enderror" 
                               name="expected_delivery_date" value="{{ old('expected_delivery_date') }}">
                        @error('expected_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Payment Terms <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm @error('payment_terms') is-invalid @enderror" 
                                name="payment_terms" required>
                            <option value="cash" {{ old('payment_terms') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_7" {{ old('payment_terms') == 'credit_7' ? 'selected' : '' }}>Credit 7 Days</option>
                            <option value="credit_15" {{ old('payment_terms') == 'credit_15' ? 'selected' : '' }}>Credit 15 Days</option>
                            <option value="credit_30" {{ old('payment_terms') == 'credit_30' ? 'selected' : '' }}>Credit 30 Days</option>
                            <option value="credit_60" {{ old('payment_terms') == 'credit_60' ? 'selected' : '' }}>Credit 60 Days</option>
                        </select>
                        @error('payment_terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Order Items</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                    <i class="feather-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="itemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 35%">Description</th>
                                <th style="width: 10%">Quantity</th>
                                <th style="width: 10%">Unit</th>
                                <th style="width: 12%">Unit Price</th>
                                <th style="width: 8%">Tax %</th>
                                <th style="width: 8%">Discount %</th>
                                <th style="width: 12%">Total</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items will be added here dynamically -->
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="7" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold" id="subtotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end">Tax Total:</td>
                                <td class="text-end" id="taxTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end">Discount Total:</td>
                                <td class="text-end" id="discountTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold text-primary" id="grandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shipping & Billing -->
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Shipping Address</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control form-control-sm" name="shipping_address" rows="3">{{ old('shipping_address') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0 fw-semibold">Billing Address</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control form-control-sm" name="billing_address" rows="3">{{ old('billing_address') }}</textarea>
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
let itemIndex = 0;

function addItem(data = null) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = `item_${itemIndex}`;
    
    row.innerHTML = `
        <td class="text-center">${itemIndex + 1}</td>
        <td>
            <input type="text" class="form-control form-control-sm" 
                   name="items[${itemIndex}][description]" 
                   value="${data?.description || ''}" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-qty" 
                   name="items[${itemIndex}][quantity]" 
                   value="${data?.quantity || 1}" min="0.01" step="0.01" 
                   onchange="calculateRow(${itemIndex})" required>
        </td>
        <td>
            <select class="form-select form-select-sm" name="items[${itemIndex}][unit]">
                <option value="pcs" ${data?.unit == 'pcs' ? 'selected' : ''}>Pcs</option>
                <option value="kg" ${data?.unit == 'kg' ? 'selected' : ''}>Kg</option>
                <option value="box" ${data?.unit == 'box' ? 'selected' : ''}>Box</option>
                <option value="ltr" ${data?.unit == 'ltr' ? 'selected' : ''}>Liter</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-price" 
                   name="items[${itemIndex}][unit_price]" 
                   value="${data?.unit_price || 0}" min="0" step="100" 
                   onchange="calculateRow(${itemIndex})" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-tax" 
                   name="items[${itemIndex}][tax_rate]" 
                   value="${data?.tax_rate || 0}" min="0" max="100" step="0.1" 
                   onchange="calculateRow(${itemIndex})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-discount" 
                   name="items[${itemIndex}][discount_rate]" 
                   value="${data?.discount_rate || 0}" min="0" max="100" step="0.1" 
                   onchange="calculateRow(${itemIndex})">
        </td>
        <td class="text-end fw-bold item-total" id="total_${itemIndex}">0</td>
        <td>
            <button type="button" class="btn btn-sm btn-icon btn-light text-danger" onclick="removeItem(${itemIndex})">
                <i class="feather-trash-2"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    if (data) calculateRow(itemIndex);
    itemIndex++;
    calculateTotals();
}

function removeItem(index) {
    document.getElementById(`item_${index}`).remove();
    calculateTotals();
}

function calculateRow(index) {
    const qty = parseFloat(document.querySelector(`[name="items[${index}][quantity]"]`).value) || 0;
    const price = parseFloat(document.querySelector(`[name="items[${index}][unit_price]"]`).value) || 0;
    const tax = parseFloat(document.querySelector(`[name="items[${index}][tax_rate]"]`).value) || 0;
    const discount = parseFloat(document.querySelector(`[name="items[${index}][discount_rate]"]`).value) || 0;
    
    const subtotal = qty * price;
    const discountAmount = subtotal * (discount / 100);
    const afterDiscount = subtotal - discountAmount;
    const taxAmount = afterDiscount * (tax / 100);
    const total = afterDiscount + taxAmount;
    
    document.getElementById(`total_${index}`).innerText = total.toLocaleString();
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let taxTotal = 0;
    let discountTotal = 0;
    let grandTotal = 0;
    
    for (let i = 0; i < itemIndex; i++) {
        const row = document.getElementById(`item_${i}`);
        if (row) {
            const qty = parseFloat(document.querySelector(`[name="items[${i}][quantity]"]`).value) || 0;
            const price = parseFloat(document.querySelector(`[name="items[${i}][unit_price]"]`).value) || 0;
            const tax = parseFloat(document.querySelector(`[name="items[${i}][tax_rate]"]`).value) || 0;
            const discount = parseFloat(document.querySelector(`[name="items[${i}][discount_rate]"]`).value) || 0;
            
            const itemSubtotal = qty * price;
            const itemDiscount = itemSubtotal * (discount / 100);
            const afterDiscount = itemSubtotal - itemDiscount;
            const itemTax = afterDiscount * (tax / 100);
            
            subtotal += itemSubtotal;
            discountTotal += itemDiscount;
            taxTotal += itemTax;
        }
    }
    
    grandTotal = subtotal - discountTotal + taxTotal;
    
    document.getElementById('subtotal').innerText = subtotal.toLocaleString();
    document.getElementById('taxTotal').innerText = taxTotal.toLocaleString();
    document.getElementById('discountTotal').innerText = discountTotal.toLocaleString();
    document.getElementById('grandTotal').innerText = grandTotal.toLocaleString();
}

function submitForm(action) {
    const form = document.getElementById('poForm');
    const formData = new FormData(form);
    
    if (itemIndex === 0) {
        alert('Please add at least one item');
        return;
    }
    
    // Add action to determine status
    formData.append('action', action);
    
    fetch('{{ route("finance.accounts-payable.purchase-orders.store") }}', {
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
        alert('Failed to create purchase order');
    });
}

function saveAsDraft() {
    submitForm('draft');
}

function submitForApproval() {
    submitForm('pending_approval');
}

// Add sample item for testing
window.onload = function() {
    addItem({
        description: 'Sample Item',
        quantity: 1,
        unit_price: 100000
    });
};
</script>
@endpush
@endsection