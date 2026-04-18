@extends('layouts.financecontroller')

@section('title', 'Outstanding Invoices')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Outstanding Invoices</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <span class="breadcrumb-item active">Outstanding Invoices</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-primary-light btn-wave" data-bs-toggle="collapse" data-bs-target="#filters">
                <i class="feather-filter"></i> Filters
            </button>
            <a href="{{ route('finance.accounts-receivable.aging') }}" class="btn btn-info-light btn-wave">
                <i class="feather-clock"></i> Aging Report
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
                            <span class="d-block mb-2 fw-medium">Total Outstanding</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($totals['total_balance'] ?? 0, 2) }}</h3>
                            <small class="text-muted">{{ number_format($totals['total_invoices'] ?? 0) }} invoices</small>
                        </div>
                        <div class="avatar avatar-lg bg-primary-transparent">
                            <i class="feather-credit-card fs-3"></i>
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
                            <span class="d-block mb-2 fw-medium">Average Balance</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($totals['avg_balance'] ?? 0, 2) }}</h3>
                            <small class="text-muted">Per invoice</small>
                        </div>
                        <div class="avatar avatar-lg bg-info-transparent">
                            <i class="feather-bar-chart-2 fs-3"></i>
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
                            <span class="d-block mb-2 fw-medium">Highest Balance</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($totals['max_balance'] ?? 0, 2) }}</h3>
                            <small class="text-muted">Single invoice</small>
                        </div>
                        <div class="avatar avatar-lg bg-warning-transparent">
                            <i class="feather-trending-up fs-3"></i>
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
                            <span class="d-block mb-2 fw-medium">Collection Rate</span>
                            <h3 class="fw-semibold mb-2">--</h3>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="avatar avatar-lg bg-success-transparent">
                            <i class="feather-pie-chart fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="collapse {{ request()->hasAny(['student_id', 'reg_no', 'academic_year_id', 'invoice_type', 'date_from', 'aging_category']) ? 'show' : '' }}" id="filters">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Filter Invoices</div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.accounts-receivable.outstanding') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Registration No</label>
                            <input type="text" class="form-control" name="reg_no" value="{{ request('reg_no') }}" placeholder="Enter reg no">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Invoice Type</label>
                            <select class="form-select" name="invoice_type">
                                <option value="">All Types</option>
                                <option value="tuition" {{ request('invoice_type') == 'tuition' ? 'selected' : '' }}>Tuition</option>
                                <option value="registration" {{ request('invoice_type') == 'registration' ? 'selected' : '' }}>Registration</option>
                                <option value="repeat_module" {{ request('invoice_type') == 'repeat_module' ? 'selected' : '' }}>Repeat Module</option>
                                <option value="supplementary" {{ request('invoice_type') == 'supplementary' ? 'selected' : '' }}>Supplementary</option>
                                <option value="hostel" {{ request('invoice_type') == 'hostel' ? 'selected' : '' }}>Hostel</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Aging Category</label>
                            <select class="form-select" name="aging_category">
                                <option value="">All</option>
                                <option value="current" {{ request('aging_category') == 'current' ? 'selected' : '' }}>Current</option>
                                <option value="1_30_days" {{ request('aging_category') == '1_30_days' ? 'selected' : '' }}>1-30 Days</option>
                                <option value="31_60_days" {{ request('aging_category') == '31_60_days' ? 'selected' : '' }}>31-60 Days</option>
                                <option value="61_90_days" {{ request('aging_category') == '61_90_days' ? 'selected' : '' }}>61-90 Days</option>
                                <option value="90_plus_days" {{ request('aging_category') == '90_plus_days' ? 'selected' : '' }}>90+ Days</option>
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
                            <label class="form-label">Min Amount</label>
                            <input type="number" class="form-control" name="min_amount" value="{{ request('min_amount') }}" step="1000">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Amount</label>
                            <input type="number" class="form-control" name="max_amount" value="{{ request('max_amount') }}" step="1000">
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('finance.accounts-receivable.outstanding') }}" class="btn btn-light">Clear Filters</a>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Outstanding Invoices</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Type</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Days</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr class="{{ $invoice->days_overdue > 90 ? 'table-danger' : ($invoice->days_overdue > 60 ? 'table-warning' : '') }}">
                                    <td>
                                        <a href="#" class="fw-semibold">{{ $invoice->invoice_number }}</a>
                                        @if($invoice->control_number)
                                        <small class="d-block text-muted">{{ $invoice->control_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-primary-transparent">
                                                <span>{{ substr($invoice->student->user->first_name ?? 'S', 0, 1) }}</span>
                                            </div>
                                            <span>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $invoice->student->registration_number ?? '' }}</td>
                                    <td>
                                        <span class="badge bg-info-transparent">{{ str_replace('_', ' ', ucwords($invoice->invoice_type)) }}</span>
                                    </td>
                                    <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $daysOverdue = $invoice->due_date->diffInDays(now(), false);
                                        @endphp
                                        @if($daysOverdue > 0)
                                            <span class="badge bg-danger">{{ $daysOverdue }} days</span>
                                        @elseif($daysOverdue == 0)
                                            <span class="badge bg-warning">Due Today</span>
                                        @else
                                            <span class="badge bg-success">{{ abs($daysOverdue) }} days left</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td class="fw-semibold">{{ number_format($invoice->balance, 2) }}</td>
                                    <td>
                                        @if($invoice->collection_status == 'critical')
                                            <span class="badge bg-danger">Critical</span>
                                        @elseif($invoice->collection_status == 'follow_up')
                                            <span class="badge bg-warning">Follow Up</span>
                                        @else
                                            <span class="badge bg-success">Current</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 flex-wrap">
                                            <a href="#" class="btn btn-sm btn-icon btn-info-light" data-bs-toggle="tooltip" title="View">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-icon btn-primary-light" data-bs-toggle="tooltip" title="Print">
                                                <i class="feather-printer"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown">
                                                    <i class="feather-more-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            <i class="feather-mail me-2"></i> Send Reminder
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            <i class="feather-edit me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger write-off" href="#" data-id="{{ $invoice->id }}">
                                                            <i class="feather-alert-octagon me-2"></i> Write Off
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 150px;">
                                        <h5 class="mt-3">No Outstanding Invoices</h5>
                                        <p class="text-muted">All invoices have been paid</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#invoicesTable').DataTable({
        pageLength: 25,
        order: [[5, 'asc']],
        columnDefs: [
            { orderable: false, targets: [11] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search invoices..."
        }
    });

    // Write Off
    $('.write-off').click(function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('id');
        
        Swal.fire({
            title: 'Write Off Invoice',
            html: `
                <input type="number" id="amount" class="swal2-input" placeholder="Amount" step="1000">
                <textarea id="reason" class="swal2-textarea" placeholder="Reason for write off"></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Write Off',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const amount = $('#amount').val();
                const reason = $('#reason').val();
                
                if (!amount || !reason) {
                    Swal.showValidationMessage('Both amount and reason are required');
                    return false;
                }
                
                return { amount, reason };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("finance.accounts-receivable.write-off", "") }}/' + invoiceId,
                    method: 'POST',
                    data: {
                        amount: result.value.amount,
                        reason: result.value.reason,
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
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to write off', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection