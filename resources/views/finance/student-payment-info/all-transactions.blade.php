@extends('layouts.financecontroller')

@section('title', 'All Transactions - ' . $student->registration_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">All Transactions</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.student-payment-info.search') }}" class="breadcrumb-item">Search</a>
                <a href="{{ route('finance.student-payment-info.show', $student->id) }}" class="breadcrumb-item">Payment Info</a>
                <span class="breadcrumb-item active">All Transactions</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.student-payment-info.print', $student->id) }}?academic_year_id={{ $selectedYearId }}" 
               class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="feather-printer"></i> Print
            </a>
        </div>
    </div>

    <!-- Filter Card - Clean -->
    <div class="card mb-3 border">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('finance.student-payment-info.all', $student->id) }}" class="d-flex gap-2 align-items-center">
                        <span class="text-muted">Filter by Year:</span>
                        <select class="form-select form-select-sm" name="academic_year_id" onchange="this.form.submit()" style="width: auto; min-width: 120px;">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-outline-secondary">Go</button></noscript>
                    </form>
                </div>
                <div class="col-md-7 text-md-end mt-2 mt-md-0">
                    <span class="badge bg-light text-dark p-2">Showing all transactions including hidden control numbers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border">
        <div class="card-header bg-transparent border-bottom py-2">
            <h5 class="mb-0 fw-semibold" style="font-size: 0.9rem;">Complete Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" style="font-size: 0.75rem; min-width: 800px;">
                    <thead style="background: transparent;">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Date</th>
                            <th>Academic Year</th>
                            <th>Control Number</th>
                            <th>Receipt</th>
                            <th>Fee Type</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allTransactions as $index => $transaction)
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td style="font-size: 0.7rem;">{{ $transaction['date']->format('d/m/y H:i') }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-dark" style="font-size: 0.65rem;">{{ $transaction['academic_year'] }}</span>
                            </td>
                            <td>
                                @if($transaction['receipt'] == 'INVOICE')
                                    <span class="badge bg-info bg-opacity-25 text-dark" style="font-size: 0.65rem;">{{ $transaction['control_number'] }}</span>
                                @else
                                    <span style="font-size: 0.7rem;">{{ $transaction['control_number'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction['receipt'] == 'INVOICE')
                                    <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.65rem;">INV</span>
                                @else
                                    <span class="badge bg-success bg-opacity-25 text-dark" style="font-size: 0.65rem;">PAY</span>
                                @endif
                            </td>
                            <td style="word-break: break-word; white-space: normal; max-width: 200px;">
                                {{ $transaction['fee_type'] }}
                            </td>
                            <td class="text-end fw-bold">{{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 0) : '-' }}</td>
                            <td class="text-end text-success fw-bold">{{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 0) : '-' }}</td>
                            <td class="text-end fw-bold {{ $transaction['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($transaction['balance'], 0) }}
                            </td>
                        </tr>
                        @endforeach

                        <!-- Closing Balance Row (clean, no background) -->
                        @if(count($allTransactions) > 0)
                        <tr class="fw-bold">
                            <td colspan="6" class="text-end pe-4">Closing Balance:</td>
                            <td class="text-end">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end {{ $allTransactions[count($allTransactions)-1]['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                TZS {{ number_format($allTransactions[count($allTransactions)-1]['balance'] ?? 0, 0) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
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

<style>
/* Clean table styles */
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
}
.badge.bg-secondary, .badge.bg-info, .badge.bg-warning, .badge.bg-success {
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
/* Responsive table scroll only on small screens */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
}
.page-title {
    font-size: 1.2rem;
}
/* Text colors */
.text-primary { color: #4361ee !important; }
.text-success { color: #10b981 !important; }
.text-danger { color: #ef4444 !important; }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush