{{-- resources/views/finance/invoices/index.blade.php --}}
@extends('layouts.financecontroller')

@section('title', 'All Invoices')

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        border-left: 4px solid #27ae60;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .stats-card .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }

    .stats-card .stats-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-card .stats-icon {
        font-size: 2.5rem;
        color: rgba(39, 174, 96, 0.2);
    }

    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table th {
        background: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table td {
        vertical-align: middle;
    }

    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }

    .badge-paid {
        background: #d4edda;
        color: #155724;
    }

    .badge-unpaid {
        background: #f8d7da;
        color: #721c24;
    }

    .badge-partial {
        background: #fff3cd;
        color: #856404;
    }

    .badge-overdue {
        background: #f8d7da;
        color: #721c24;
        font-weight: 600;
    }

    .badge-repeat {
        background: #3498db;
        color: white;
    }

    .badge-supplementary {
        background: #e67e22;
        color: white;
    }

    .badge-hostel {
        background: #17a2b8;
        color: white;
    }

    .btn-action {
        padding: 5px 10px;
        border-radius: 5px;
        color: white;
        text-decoration: none;
        display: inline-block;
        margin: 0 2px;
        border: none;
        cursor: pointer;
    }

    .btn-view { background: #3498db; }
    .btn-print { background: #95a5a6; }
    .btn-download { background: #27ae60; }

    .btn-action:hover {
        opacity: 0.8;
        color: white;
    }

    .btn-finance {
        background: #27ae60;
        color: white;
        border: none;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-finance:hover {
        background: #219a52;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(39, 174, 96, 0.2);
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
        color: white;
    }

    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 8px 12px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #27ae60;
        box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        outline: none;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .pagination {
        justify-content: center;
    }

    .control-number-badge {
        background: #e9ecef;
        color: #2c3e50;
        padding: 3px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">All Invoices</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoices</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.invoices.create') }}" class="btn-finance">
            <i class="fas fa-plus-circle me-2"></i>Generate New Invoice
        </a>
    </div>

   
    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('finance.invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Invoice Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="repeat_module" {{ request('type') == 'repeat_module' ? 'selected' : '' }}>🔁 Repeat Module</option>
                    <option value="supplementary" {{ request('type') == 'supplementary' ? 'selected' : '' }}>➕ Supplementary</option>
                    <option value="hostel" {{ request('type') == 'hostel' ? 'selected' : '' }}>🏠 Hostel</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Registration No</label>
                <input type="text" name="reg_no" class="form-control" value="{{ request('reg_no') }}" placeholder="Enter reg no">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn-finance">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="{{ route('finance.invoices.index') }}" class="btn-secondary">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Control No</th>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Type</th>
                            <th>Amount (TZS)</th>
                            <th>Paid (TZS)</th>
                            <th>Balance (TZS)</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $invoice->invoice_number }}</strong>
                            </td>
                            <td>
                                <span class="control-number-badge">{{ $invoice->control_number }}</span>
                            </td>
                            <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                            <td>{{ $invoice->student->registration_number ?? '' }}</td>
                            <td>
                                @if($invoice->invoice_type == 'repeat_module')
                                    <span class="badge-status badge-repeat">🔁 Repeat</span>
                                @elseif($invoice->invoice_type == 'supplementary')
                                    <span class="badge-status badge-supplementary">➕ Supplementary</span>
                                @elseif($invoice->invoice_type == 'hostel')
                                    <span class="badge-status badge-hostel">🏠 Hostel</span>
                                @else
                                    <span class="badge-status badge-secondary">{{ $invoice->invoice_type }}</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($invoice->total_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($invoice->paid_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($invoice->balance, 0) }}</td>
                            <td>
                                @if($invoice->payment_status == 'paid')
                                    <span class="badge-status badge-paid">Paid</span>
                                @elseif($invoice->payment_status == 'partial')
                                    <span class="badge-status badge-partial">Partial</span>
                                @elseif($invoice->isOverdue())
                                    <span class="badge-status badge-overdue">Overdue</span>
                                @else
                                    <span class="badge-status badge-unpaid">Unpaid</span>
                                @endif
                            </td>
                            <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                            <td>
                                {{ $invoice->due_date->format('d/m/Y') }}
                                @if($invoice->isOverdue())
                                    <br><small class="text-danger">Overdue</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('finance.invoices.show', $invoice->id) }}" 
                                       class="btn-action btn-view" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.invoices.print', $invoice->id) }}" 
                                       class="btn-action btn-print" 
                                       target="_blank"
                                       title="Print Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="{{ route('finance.invoices.download', $invoice->id) }}" 
                                       class="btn-action btn-download" 
                                       title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Invoices Found</h5>
                                <p class="text-muted mb-3">Get started by generating your first invoice</p>
                                <a href="{{ route('finance.invoices.create') }}" class="btn-finance">
                                    <i class="fas fa-plus-circle me-2"></i>Generate First Invoice
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
            <div class="mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Optional: Add tooltips for better UX
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips if needed
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush