@extends('layouts.financecontroller')

@section('title', 'Edit Requisition')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Requisition</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.requisitions.index') }}">Requisitions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.requisitions.show', $requisition->id) }}">{{ $requisition->requisition_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Editing this requisition will reset its status to draft and require re-submission.
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.procurement.requisitions.update', $requisition->id) }}" method="POST" id="requisitionForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Requisition Number</label>
                                <input type="text" class="form-control" value="{{ $requisition->requisition_number }}" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Title</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $requisition->title) }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" required>{{ old('description', $requisition->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Department</label>
                                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" 
                                            {{ old('department_id', $requisition->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Required Date</label>
                                <input type="date" name="required_date" class="form-control @error('required_date') is-invalid @enderror" 
                                       value="{{ old('required_date', $requisition->required_date->format('Y-m-d')) }}" required>
                                @error('required_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Priority</label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="low" {{ old('priority', $requisition->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $requisition->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $requisition->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $requisition->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Justification</label>
                                <textarea name="justification" class="form-control @error('justification') is-invalid @enderror" 
                                          rows="2">{{ old('justification', $requisition->justification) }}</textarea>
                                @error('justification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Items Required</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Est. Unit Price</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach($requisition->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][item_name]" 
                                                       class="form-control" value="{{ $item->item_name }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][description]" 
                                                       class="form-control" value="{{ $item->description }}">
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $index }}][quantity]" 
                                                       class="form-control quantity" value="{{ $item->quantity }}" min="1" required>
                                            </td>
                                            <td>
                                                <select name="items[{{ $index }}][unit]" class="form-select" required>
                                                    <option value="pcs" {{ $item->unit == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                                    <option value="box" {{ $item->unit == 'box' ? 'selected' : '' }}>Box</option>
                                                    <option value="kg" {{ $item->unit == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                                    <option value="liter" {{ $item->unit == 'liter' ? 'selected' : '' }}>Liter</option>
                                                    <option value="meter" {{ $item->unit == 'meter' ? 'selected' : '' }}>Meter</option>
                                                    <option value="set" {{ $item->unit == 'set' ? 'selected' : '' }}>Set</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[{{ $index }}][estimated_unit_price]" 
                                                       class="form-control unit-price" value="{{ $item->estimated_unit_price }}" min="0" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control item-total" 
                                                       value="{{ number_format($item->estimated_total, 2) }}" readonly>
                                            </td>
                                            <td>
                                                @if($loop->index >= 2)
                                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Grand Total:</th>
                                        <th id="grandTotal">{{ number_format($requisition->estimated_total, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItem">
                            <i class="fas fa-plus me-2"></i>Add Item
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Current Status
                                </h6>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $statusColors[$requisition->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                    </span>
                                </p>
                                <p><strong>Created:</strong> {{ $requisition->created_at->format('d/m/Y H:i') }}</p>
                                <p><strong>Last Updated:</strong> {{ $requisition->updated_at->format('d/m/Y H:i') }}</p>
                                <hr>
                                <p class="small text-muted">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Editing will reset approval status
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Requisition
                </button>
                <a href="{{ route('finance.procurement.requisitions.show', $requisition->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = {{ count($requisition->items) }};

$(document).ready(function() {
    $('#addItem').click(function() {
        addItem();
    });

    $(document).on('click', '.remove-item', function() {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert('At least one item is required');
        }
    });

    $(document).on('input', '.quantity, .unit-price', function() {
        calculateRowTotal($(this).closest('tr'));
        calculateGrandTotal();
    });
});

function addItem() {
    const newRow = `
        <tr>
            <td>
                <input type="text" name="items[${itemIndex}][item_name]" class="form-control" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][description]" class="form-control">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" value="1" min="1" required>
            </td>
            <td>
                <select name="items[${itemIndex}][unit]" class="form-select" required>
                    <option value="pcs">Pieces (pcs)</option>
                    <option value="box">Box</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="liter">Liter</option>
                    <option value="meter">Meter</option>
                    <option value="set">Set</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][estimated_unit_price]" 
                       class="form-control unit-price" value="0" min="0" required>
            </td>
            <td>
                <input type="text" class="form-control item-total" readonly value="0.00">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsBody').append(newRow);
    itemIndex++;
}

function calculateRowTotal(row) {
    const quantity = parseFloat(row.find('.quantity').val()) || 0;
    const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    const total = quantity * unitPrice;
    
    row.find('.item-total').val(total.toFixed(2));
}

function calculateGrandTotal() {
    let grandTotal = 0;
    $('#itemsBody tr').each(function() {
        const total = parseFloat($(this).find('.item-total').val()) || 0;
        grandTotal += total;
    });
    $('#grandTotal').text(grandTotal.toFixed(2));
}
</script>
@endpush
@endsection