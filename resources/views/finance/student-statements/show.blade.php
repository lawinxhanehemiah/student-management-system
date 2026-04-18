@extends('layouts.financecontroller')

@section('title', 'Student Statement')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Student Statement</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.student-statements.index') }}" class="breadcrumb-item">Student Statements</a>
                <span class="breadcrumb-item active">{{ $student->registration_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.student-statements.print', $student->id) }}?academic_year_id={{ $academicYearId }}" 
               class="btn btn-primary-light btn-wave" target="_blank">
                <i class="feather-printer"></i> Print
            </a>
            <a href="{{ route('finance.student-statements.download', $student->id) }}?academic_year_id={{ $academicYearId }}" 
               class="btn btn-success-light btn-wave">
                <i class="feather-download"></i> Download PDF
            </a>
            <button class="btn btn-info-light btn-wave" onclick="emailStatement()">
                <i class="feather-mail"></i> Email
            </button>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="card custom-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted">Student Name</label>
                    <h5>{{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}</h5>
                </div>
                <div class="col-md-2">
                    <label class="text-muted">Reg No</label>
                    <h5>{{ $student->registration_number }}</h5>
                </div>
                <div class="col-md-3">
                    <label class="text-muted">Programme</label>
                    <h5>{{ $student->programme->name ?? 'N/A' }}</h5>
                </div>
                <div class="col-md-2">
                    <label class="text-muted">Current Level</label>
                    <h5>Year {{ $student->current_level }}</h5>
                </div>
                <div class="col-md-2">
                    <label class="text-muted">Status</label><br>
                    <span class="badge bg-success">{{ ucfirst($student->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter by Academic Year -->
    <div class="card custom-card">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.student-statements.show', $student->id) }}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Academic Year</label>
                        <select class="form-select" name="academic_year_id" onchange="this.form.submit()">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="{{ route('finance.student-statements.show', $student->id) }}" class="btn btn-light">Clear Filter</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <!-- Invoices Table -->
    <div class="card custom-card mt-3">
        <div class="card-header">
            <div class="card-title">Invoices</div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('finance.invoices.show', $invoice->id) }}">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $invoice->invoice_type)) }}</td>
                            <td class="text-end">TZS {{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="text-end text-success">TZS {{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="text-end {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($invoice->balance, 2) }}
                            </td>
                            <td>
                                @if($invoice->payment_status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($invoice->payment_status == 'partial')
                                    <span class="badge bg-warning">Partial</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No invoices found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card custom-card mt-3">
        <div class="card-header">
            <div class="card-title">Payments</div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Control #</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                            <td>{{ ucwords($payment->payment_method) }}</td>
                            <td>{{ $payment->transaction_reference ?? $payment->receipt_number ?? 'N/A' }}</td>
                            <td>{{ $payment->control_number ?? 'N/A' }}</td>
                            <td class="text-end">TZS {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                @if($payment->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function emailStatement() {
    Swal.fire({
        title: 'Email Statement',
        html: `
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" id="email" class="form-control" value="{{ $student->user->email ?? '' }}">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const email = $('#email').val();
            if (!email) {
                Swal.showValidationMessage('Email is required');
                return false;
            }
            return { email };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("finance.student-statements.email", $student->id) }}',
                method: 'POST',
                data: {
                    email: result.value.email,
                    academic_year_id: '{{ $academicYearId }}',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to send email', 'error');
                }
            });
        }
    });
}
</script>
@endpush
@endsection