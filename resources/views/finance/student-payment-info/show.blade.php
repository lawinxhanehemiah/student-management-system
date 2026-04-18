@extends('layouts.financecontroller')

@section('title', 'Payment Info - ' . $student->registration_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Payment Information</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.student-payment-info.search') }}" class="breadcrumb-item">Search</a>
                <span class="breadcrumb-item active">{{ $student->registration_number }}</span>
            </div>
        </div>
        <div class="btn-list">
    <a href="{{ route('finance.student-payment-info.all', $student->id) }}?academic_year_id={{ $selectedYearId }}" 
       class="btn btn-outline-secondary btn-sm">
        <i class="feather-eye"></i> View All
    </a>
    <a href="{{ route('finance.student-payment-info.print', $student->id) }}?academic_year_id={{ $selectedYearId }}" 
       class="btn btn-outline-secondary btn-sm" target="_blank">
        <i class="feather-printer"></i> Print
    </a>
    <!-- Ongeza button hii -->
    <a href="{{ route('finance.payment-adjustments.create', $student->id) }}" 
       class="btn btn-primary btn-sm">
        <i class="feather-plus-circle"></i> Request Adjustment
    </a>
</div>
        
    </div>

    <!-- Info Alert (Note) - Clean version -->
    <div class="alert alert-transparent border border-info text-info py-2 mb-3 small" style="background: transparent;">
        <i class="feather-info me-1"></i> 
        Note: Click "View All" Link below to view all hidden invoice & control numbers (eg. transcript or RM invoice). 
        If control number reject the payment click the "activate" button bellow to re-use it and extend its expire date.
    </div>

    <!-- Filter Card - Years (no background) -->
    <div class="card mb-3 border">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="fw-semibold">Filter By Year</span>
                        <div class="d-flex gap-2 flex-wrap">
                            @php
                                $yearsWithData = collect($academicYears)->filter(function($year) use ($transactions) {
                                    return collect($transactions)->contains('academic_year', $year->name);
                                });
                            @endphp
                            @forelse($yearsWithData as $year)
                                <a href="{{ route('finance.student-payment-info.show', [$student->id, 'academic_year_id' => $year->id]) }}" 
                                   class="btn {{ $selectedYearId == $year->id ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">
                                    {{ $year->name }}
                                </a>
                            @empty
                                <span class="text-muted">No years with data</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card: Current Year, Last Login, Today, Student Name -->
    <div class="card mb-3 border">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex gap-4 flex-wrap">
                        <span><strong>Current Year:</strong> <span class="text-primary">{{ $selectedYear->name ?? 'N/A' }}</span></span>
                        <span><strong>Last Login:</strong> {{ $lastLogin ?? \Carbon\Carbon::now()->format('d M, Y H:i:s') }}</span>
                        <span><strong>Today:</strong> {{ now()->format('d M, Y') }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <strong>{{ $student->user->first_name }} {{ $student->user->last_name }}</strong>
                    <div class="small text-muted">{{ $student->registration_number }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border">
        <div class="card-header bg-transparent border-bottom py-2">
            <h5 class="mb-0 fw-semibold" style="font-size: 0.9rem;">Academic Year: {{ $selectedYear->name ?? 'All Years' }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" style="font-size: 0.75rem; min-width: 800px;">
                    <thead style="background: transparent;">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Academic Year</th>
                            <th>Control Number</th>
                            <th>Receipt</th>
                            <th>Fee Type</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Installment</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $transaction)
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-dark" style="font-size: 0.65rem;">{{ $transaction['academic_year'] }}</span>
                            </td>
                            <td>
                                @if($transaction['receipt'] == 'INVOICE')
                                    <span class="badge bg-info bg-opacity-25 text-dark" style="font-size: 0.7rem;">{{ $transaction['control_number'] }}</span>
                                @else
                                    <span style="font-size: 0.7rem;">{{ $transaction['control_number'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction['receipt'] == 'INVOICE')
                                    <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.65rem;">INVOICE</span>
                                @else
                                    <span class="badge bg-success bg-opacity-25 text-dark" style="font-size: 0.65rem;">PAYMENT</span>
                                @endif
                            </td>
                            <td style="word-break: break-word; white-space: normal; max-width: 200px;">
                                {{ $transaction['fee_type'] }}
                            </td>
                            <td class="text-end fw-bold">{{ number_format($transaction['debit'], 0) }}</td>
                            <td class="text-end">{{ number_format($transaction['installment'], 0) }}</td>
                            <td class="text-end text-success fw-bold">{{ number_format($transaction['credit'], 0) }}</td>
                            <td class="text-end fw-bold {{ $transaction['balance'] > 0 ? 'text-danger' : ($transaction['balance'] < 0 ? 'text-warning' : 'text-success') }}">
                                {{ number_format($transaction['balance'], 0) }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No transactions found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top py-2">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="badge bg-light text-dark p-2 me-2">
                        <strong>Total Debit:</strong> TZS {{ number_format($totalDebit, 0) }}
                    </span>
                    <span class="badge bg-light text-dark p-2">
                        <strong>Total Credit:</strong> TZS {{ number_format($totalCredit, 0) }}
                    </span>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <strong>Closing Balance:</strong>
                    <span class="fw-bold fs-5 {{ $closingBalance > 0 ? 'text-danger' : ($closingBalance < 0 ? 'text-warning' : 'text-success') }}">
                        TZS {{ number_format($closingBalance, 0) }}
                    </span>
                    @if($closingBalance < 0)
                        <span class="badge bg-warning bg-opacity-25 text-dark ms-2">Overpayment</span>
                    @elseif($closingBalance == 0)
                        <span class="badge bg-success bg-opacity-25 text-dark ms-2">Fully Paid</span>
                    @else
                        <span class="badge bg-danger bg-opacity-25 text-dark ms-2">Outstanding</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

        <!-- View All Records Link (clean) -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-start gap-3">
                <a href="{{ route('finance.student-payment-info.all', $student->id) }}?academic_year_id={{ $selectedYearId }}" 
                   class="btn btn-link text-primary p-0 text-decoration-none">
                    <i class="feather-eye"></i> View All Records
                </a>
                <a href="{{ route('finance.payment-adjustments.create', $student->id) }}" 
                   class="btn btn-link text-primary p-0 text-decoration-none">
                    <i class="feather-plus-circle"></i> Request Adjustment
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="row mt-4">
        <div class="col-12 text-center text-muted">
            <small>College Website | Copyright © {{ date('Y') }} St. Maximiliancolbe College. All rights reserved.</small>
        </div>
    </div>
</div>

<!-- rest of styles and scripts -->

    <!-- Footer -->
    <div class="row mt-4">
        <div class="col-12 text-center text-muted">
            <small>College Website | Copyright © {{ date('Y') }} St. Maximiliancolbe College. All rights reserved.</small>
        </div>
    </div>
</div>

<style>
/* Clean table styles - no heavy backgrounds */
.table {
    margin-bottom: 0;
}
.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6 !important;
    vertical-align: middle;
    padding: 0.3rem 0.4rem !important;
}
.table thead th {
    background: transparent !important;
    color: #212529 !important;
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 2px solid #dee2e6 !important;
}
.table tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}
/* Badges with subtle background */
.badge {
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    font-size: 0.65rem;
    background-color: rgba(0,0,0,0.05) !important;
    color: #212529 !important;
}
.badge.bg-secondary, .badge.bg-info, .badge.bg-warning, .badge.bg-success, .badge.bg-danger {
    background-color: rgba(0,0,0,0.05) !important;
    color: #212529 !important;
}
/* Card clean */
.card {
    border-radius: 8px;
    box-shadow: none;
    border: 1px solid #e9ecef;
}
.card-header, .card-footer {
    background: transparent !important;
    border-color: #e9ecef;
}
.btn-sm {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
}
.alert-transparent {
    background: transparent !important;
    border-left: 4px solid #0dcaf0;
}
/* Keep text colors for status */
.text-primary { color: #4361ee !important; }
.text-success { color: #10b981 !important; }
.text-danger { color: #ef4444 !important; }
.text-warning { color: #f59e0b !important; }
/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
}
.page-title {
    font-size: 1.2rem;
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush