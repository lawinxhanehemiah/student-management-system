@extends('layouts.financecontroller')

@section('title', 'Create Goods Received Note')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Create Goods Received Note</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.grn.index') }}" class="text-muted">Goods Received Notes</a> > 
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('finance.accounts-payable.grn.index') }}" class="btn btn-sm btn-light">
            <i class="feather-arrow-left"></i> Back
        </a>
    </div>

    <form id="grnForm" method="POST">
        @csrf
        
        <!-- GRN Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-semibold">Goods Received Note Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Purchase Order <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="purchase_order_id" id="purchase_order_id" required>
                            <option value="">Select Purchase Order</option>
                            @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}" {{ request('purchase_order_id') == $po->id ? 'selected' : '' }}
                                    data-supplier="{{ $po->supplier_id }}">
                                {{ $po->po_number }} - {{ $po->supplier->name }} ({{ $po->order_date->format('d/m/Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Receipt Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="receipt_date" 
                               value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Delivery Note #</label>
                        <input type="text" class="form-control form-control-sm" name="delivery_note_number" 
                               value="{{ old('delivery_note_number') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Received By</label>
                        <input type="text" class="form-control form-control-sm" name="received_by" 
                               value="{{ old('received_by', Auth::user()->name) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Received Items</h6>
                <span class="badge bg-info" id="selectedPO">No PO selected</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="itemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 35%">Description</th>
                                <th style="width: 10%">Ordered</th>
                                <th style="width: 10%">Prev Received</th>
                                <th style="width: 10%">Balance</th>
                                <th style="width: 10%">Received</th>
                                <th style="width: 10%">Accepted</th>
                                <th style="width: 10%">Rejected</th>
                                <th style="width: 15%">Rejection Reason</th>
                                <th style="width: 10%">Batch No</th>
                                <th style="width: 10%">Expiry</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <p class="text-muted small mb-0">Select a purchase order to load items</p>
                                </td>
                            </tr>
                        </tbody>
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
            <button type="button" class="btn btn-sm btn-primary" onclick="submitGRN()">
                Create Goods Received Note
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let items = [];

// Load PO items when PO is selected
document.getElementById('purchase_order_id').addEventListener('change', function() {
    const poId = this.value;
    const selectedOption = this.options[this.selectedIndex];
    
    if (poId) {
        document.getElementById('selectedPO').innerText = 'PO: ' + selectedOption.text.split(' - ')[0];
        
        // Fetch PO items
        fetch(`/finance/accounts-payable/api/purchase-orders/${poId}/items`)
            .then(response => response.json())
            .then(data => {
                items = data;
                renderItems();
            });
    } else {
        document.getElementById('selectedPO').innerText = 'No PO selected';
        document.getElementById('itemsBody').innerHTML = `
            <tr>
                <td colspan="12" class="text-center py-4">
                    <p class="text-muted small mb-0">Select a purchase order to load items</p>
                </td>
            </tr>
        `;
    }
});

function renderItems() {
    const tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    
    items.forEach((item, index) => {
        const balance = item.quantity - item.received_quantity;
        const row = document.createElement('tr');
        row.id = `item_${index}`;
        
        row.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td>
                ${item.description}
                <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.id}">
            </td>
            <td class="text-end">${item.quantity}</td>
            <td class="text-end">${item.received_quantity}</td>
            <td class="text-end fw-bold" id="balance_${index}">${balance}</td>
            <td>
                <input type="number" class="form-control form-control-sm item-received" 
                       name="items[${index}][quantity_received]" 
                       value="${balance}" min="0" max="${balance}" step="0.01"
                       onchange="updateAccepted(${index})" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm item-accepted" 
                       name="items[${index}][quantity_accepted]" 
                       value="${balance}" min="0" max="${balance}" step="0.01"
                       onchange="updateRejected(${index})" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm item-rejected" 
                       name="items[${index}][quantity_rejected]" value="0" min="0" step="0.01"
                       onchange="updateAcceptedFromRejected(${index})" readonly>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${index}][rejection_reason]" 
                       placeholder="Reason if rejected">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="items[${index}][batch_number]" 
                       placeholder="Batch/Lot">
            </td>
            <td>
                <input type="date" class="form-control form-control-sm" 
                       name="items[${index}][expiry_date]">
            </td>
            <td class="text-center">
                <span class="text-success" id="status_${index}">✓</span>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function updateAccepted(index) {
    const received = parseFloat(document.querySelector(`[name="items[${index}][quantity_received]"]`).value) || 0;
    const balance = parseFloat(document.getElementById(`balance_${index}`).innerText);
    const accepted = document.querySelector(`[name="items[${index}][quantity_accepted]"]`);
    const rejected = document.querySelector(`[name="items[${index}][quantity_rejected]"]`);
    
    if (received > balance) {
        alert('Received quantity cannot exceed balance');
        document.querySelector(`[name="items[${index}][quantity_received]"]`).value = balance;
        return;
    }
    
    accepted.value = received;
    rejected.value = 0;
    
    document.getElementById(`status_${index}`).innerHTML = '✓';
}

function updateRejected(index) {
    const accepted = parseFloat(document.querySelector(`[name="items[${index}][quantity_accepted]"]`).value) || 0;
    const received = parseFloat(document.querySelector(`[name="items[${index}][quantity_received]"]`).value) || 0;
    const rejected = document.querySelector(`[name="items[${index}][quantity_rejected]"]`);
    
    if (accepted > received) {
        alert('Accepted quantity cannot exceed received quantity');
        document.querySelector(`[name="items[${index}][quantity_accepted]"]`).value = received;
        rejected.value = 0;
    } else {
        rejected.value = received - accepted;
    }
}

function updateAcceptedFromRejected(index) {
    const received = parseFloat(document.querySelector(`[name="items[${index}][quantity_received]"]`).value) || 0;
    const rejected = parseFloat(document.querySelector(`[name="items[${index}][quantity_rejected]"]`).value) || 0;
    const accepted = document.querySelector(`[name="items[${index}][quantity_accepted]"]`);
    
    if (rejected > received) {
        alert('Rejected quantity cannot exceed received quantity');
        document.querySelector(`[name="items[${index}][quantity_rejected]"]`).value = 0;
        accepted.value = received;
    } else {
        accepted.value = received - rejected;
    }
}

function submitGRN() {
    const form = document.getElementById('grnForm');
    const formData = new FormData(form);
    
    // Validate
    const poId = document.getElementById('purchase_order_id').value;
    if (!poId) {
        alert('Please select a purchase order');
        return;
    }
    
    fetch('{{ route("finance.accounts-payable.grn.store") }}', {
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
        alert('Failed to create GRN');
    });
}

// If PO ID is passed in URL
@if(request('purchase_order_id'))
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.getElementById('purchase_order_id').value = '{{ request("purchase_order_id") }}';
        document.getElementById('purchase_order_id').dispatchEvent(new Event('change'));
    }, 500);
});
@endif
</script>
@endpush
@endsection