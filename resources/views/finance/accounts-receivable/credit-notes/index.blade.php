@extends('layouts.financecontroller')

@section('title', 'Credit Notes')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Credit Notes</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <span class="breadcrumb-item active">Credit Notes</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.credit-notes.create') }}" class="btn btn-primary btn-wave">
                <i class="feather-plus"></i> New Credit Note
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Total Credit Notes</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['total_amount'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-primary-transparent">
                            <i class="feather-file-text fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Remaining Amount</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['remaining_amount'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-warning-transparent">
                            <i class="feather-dollar-sign fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Active Notes</span>
                            <h3 class="fw-semibold mb-2">{{ $summary['active_notes'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-success-transparent">
                            <i class="feather-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Fully Used</span>
                            <h3 class="fw-semibold mb-2">{{ $summary['fully_used'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar avatar-lg bg-secondary-transparent">
                            <i class="feather-check-square fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.credit-notes.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="partially_used" {{ request('status') == 'partially_used' ? 'selected' : '' }}>Partially Used</option>
                                    <option value="fully_used" {{ request('status') == 'fully_used' ? 'selected' : '' }}>Fully Used</option>
                                    <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Student</label>
                                <select class="form-select" name="student_id">
                                    <option value="">All</option>
                                    @foreach($students ?? [] as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->first_name }} {{ $student->user->last_name }} - {{ $student->registration_number }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <a href="{{ route('finance.credit-notes.index') }}" class="btn btn-light">Clear</a>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Notes Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Credit Notes List</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Credit Note #</th>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Invoice #</th>
                                    <th>Reason</th>
                                    <th>Amount</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                    <th>Expiry</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($creditNotes as $note)
                                <tr>
                                    <td>
                                        <a href="{{ route('finance.credit-notes.show', $note->id) }}" class="fw-semibold">
                                            {{ $note->credit_note_number }}
                                        </a>
                                    </td>
                                    <td>{{ $note->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-primary-transparent">
                                                <span>{{ substr($note->student->user->first_name ?? 'S', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <span>{{ $note->student->user->first_name ?? '' }} {{ $note->student->user->last_name ?? '' }}</span>
                                                <small class="d-block text-muted">{{ $note->student->registration_number ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#">{{ $note->invoice->invoice_number ?? '' }}</a>
                                    </td>
                                    <td>{{ Str::limit($note->reason, 30) }}</td>
                                    <td class="fw-semibold">{{ number_format($note->amount, 2) }}</td>
                                    <td>{{ number_format($note->remaining_amount, 2) }}</td>
                                    <td>
                                        @if($note->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($note->status == 'partially_used')
                                            <span class="badge bg-warning">Partially Used</span>
                                        @elseif($note->status == 'fully_used')
                                            <span class="badge bg-secondary">Fully Used</span>
                                        @elseif($note->status == 'void')
                                            <span class="badge bg-danger">Void</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($note->expiry_date)
                                            @if($note->expiry_date->isPast())
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                {{ $note->expiry_date->format('d/m/Y') }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('finance.credit-notes.show', $note->id) }}" class="btn btn-sm btn-icon btn-info-light" data-bs-toggle="tooltip" title="View">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="{{ route('finance.credit-notes.print', $note->id) }}" target="_blank" class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="tooltip" title="Print">
                                                <i class="feather-printer"></i>
                                            </a>
                                            <a href="{{ route('finance.credit-notes.download', $note->id) }}" class="btn btn-sm btn-icon btn-success-light" data-bs-toggle="tooltip" title="Download PDF">
                                                <i class="feather-download"></i>
                                            </a>
                                            @if(in_array($note->status, ['active', 'partially_used']))
                                            <button class="btn btn-sm btn-icon btn-warning-light apply-credit" 
                                                    data-id="{{ $note->id }}" 
                                                    data-number="{{ $note->credit_note_number }}"
                                                    data-remaining="{{ $note->remaining_amount }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Apply Credit">
                                                <i class="feather-check-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-danger-light void-credit" 
                                                    data-id="{{ $note->id }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Void">
                                                <i class="feather-x-circle"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 150px;">
                                        <h5 class="mt-3">No Credit Notes Found</h5>
                                        <p class="text-muted">Create your first credit note to get started</p>
                                        <a href="{{ route('finance.credit-notes.create') }}" class="btn btn-primary">
                                            <i class="feather-plus"></i> Create Credit Note
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $creditNotes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Apply Credit Note
    $('.apply-credit').click(function() {
        const creditNoteId = $(this).data('id');
        const remainingAmount = $(this).data('remaining');
        
        // Load invoices for application
        $.get('{{ route("finance.credit-notes.get-invoices") }}', function(response) {
            let invoiceOptions = '<option value="">Select Invoice</option>';
            response.invoices.forEach(invoice => {
                invoiceOptions += `<option value="${invoice.id}">${invoice.invoice_number} - Balance: ${invoice.balance}</option>`;
            });
            
            Swal.fire({
                title: 'Apply Credit Note',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Select Invoice</label>
                        <select id="targetInvoice" class="form-select">${invoiceOptions}</select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (Max: ${remainingAmount})</label>
                        <input type="number" id="applyAmount" class="form-control" max="${remainingAmount}" step="1000">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Apply',
                preConfirm: () => {
                    const invoiceId = $('#targetInvoice').val();
                    const amount = $('#applyAmount').val();
                    
                    if (!invoiceId || !amount) {
                        Swal.showValidationMessage('Please select invoice and enter amount');
                        return false;
                    }
                    
                    if (amount > remainingAmount) {
                        Swal.showValidationMessage('Amount cannot exceed remaining credit');
                        return false;
                    }
                    
                    return { invoiceId, amount };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("finance.credit-notes.apply", "") }}/' + creditNoteId,
                        method: 'POST',
                        data: {
                            target_invoice_id: result.value.invoiceId,
                            amount: result.value.amount,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
    
    // Void Credit Note
    $('.void-credit').click(function() {
        const creditNoteId = $(this).data('id');
        
        Swal.fire({
            title: 'Void Credit Note',
            text: 'Are you sure you want to void this credit note? This action cannot be undone.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Reason for voiding...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, void it!'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                $.ajax({
                    url: '{{ route("finance.credit-notes.void", "") }}/' + creditNoteId,
                    method: 'POST',
                    data: {
                        reason: result.value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Voided!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection