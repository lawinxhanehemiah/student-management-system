@extends('layouts.financecontroller')

@section('title', 'Create Supplier Invoice')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Supplier Invoice</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.invoices.index') }}" class="text-muted">Supplier Invoices</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.accounts-payable.invoices.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <form id="invoiceForm" method="POST">
        @csrf
        
        <!-- Invoice Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-semibold">Invoice Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" 
                                name="supplier_id" id="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Purchase Order</label>
                        <select class="form-select form-select-sm" name="purchase_order_id" id="purchase_order_id">
                            <option value="">None (Direct Invoice)</option>
                            @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}" {{ request('purchase_order_id') == $po->id ? 'selected' : '' }}
                                    data-supplier="{{ $po->supplier_id }}">
                                {{ $po->po_number }} - {{ $po->supplier->name }} ({{ $po->order_date->format('d/m/Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">GRN</label>
                        <select class="form-select form-select-sm" name="goods_received_note_id" id="grn_id">
                            <option value="">None</option>
                            @if($grn)
                            <option value="{{ $grn->id }}" selected>{{ $grn->grn_number }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="invoice_date" 
                               value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="due_date" 
                               value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Supplier Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="supplier_invoice_number" 
                               value="{{ old('supplier_invoice_number') }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Invoice Items</h6>
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
                                <th style="width: 40%">Description</th>
                                <th style="width: 10%">Quantity</th>
                                <th style="width: 12%">Unit Price</th>
                                <th style="width: 8%">Tax %</th>
                                <th style="width: 8%">Discount %</th>
                                <th style="width: 12%">Total</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items will be added here -->
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold" id="subtotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">Tax Total:</td>
                                <td class="text-end" id="taxTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold text-primary" id="grandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
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
            <button type="button" class="btn btn-sm btn-primary" onclick="submitForm()">
                Create Invoice
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = 0;

// Load GRNs when PO is selected
document.getElementById('purchase_order_id').addEventListener('change', function() {
    const poId = this.value;
    if (poId) {
        // Load GRNs for this PO
        fetch(`/api/purchase-orders/${poId}/grns`)
            .then(response => response.json())
            .then(data => {
                const grnSelect = document.getElementById('grn_id');
                grnSelect.innerHTML = '<option value="">None</option>';
                data.forEach(grn => {
                    grnSelect.innerHTML += `<option value="${grn.id}">${grn.grn_number}</option>`;
                });
            });
        
        // Load PO items
        fetch(`/api/purchase-orders/${poId}/items`)
            .then(response => response.json())
            .then(data => {
                // Clear existing items
                document.getElementById('itemsBody').innerHTML = '';
                itemIndex = 0;
                
                // Add items from PO
                data.forEach(item => {
                    addItem({
                        description: item.description,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        tax_rate: item.tax_rate,
                        po_item_id: item.id
                    });
                });
            });
    }
});

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
            ${data?.po_item_id ? `<input type="hidden" name="items[${itemIndex}][purchase_order_item_id]" value="${data.po_item_id}">` : ''}
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-qty" 
                   name="items[${itemIndex}][quantity]" 
                   value="${data?.quantity || 1}" min="0.01" step="0.01" 
                   onchange="calculateRow(${itemIndex})" required>
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
            taxTotal += itemTax;
        }
    }
    
    grandTotal = subtotal + taxTotal;
    
    document.getElementById('subtotal').innerText = subtotal.toLocaleString();
    document.getElementById('taxTotal').innerText = taxTotal.toLocaleString();
    document.getElementById('grandTotal').innerText = grandTotal.toLocaleString();
}

function submitForm() {
    const form = document.getElementById('invoiceForm');
    const formData = new FormData(form);
    
    if (itemIndex === 0) {
        alert('Please add at least one item');
        return;
    }
    
    fetch('{{ route("finance.accounts-payable.invoices.store") }}', {
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
        alert('Failed to create invoice');
    });
}

// Initialize if coming from GRN
@if($grn)
    // Load PO items from GRN
    setTimeout(() => {
        @foreach($grn->purchaseOrder->items as $item)
        addItem({
            description: '{{ $item->description }}',
            quantity: {{ $item->quantity }},
            unit_price: {{ $item->unit_price }},
            tax_rate: {{ $item->tax_rate }},
            po_item_id: {{ $item->id }}
        });
        @endforeach
    }, 500);
@endif
</script>
@endpush
@endsection