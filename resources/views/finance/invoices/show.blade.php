{{-- resources/views/finance/invoices/show.blade.php --}}
@extends('layouts.financecontroller')

@section('title', 'Invoice Details')

@push('styles')
<style>
    .invoice-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .invoice-header {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #e0e0e0;
    }

    .info-label {
        color: #7f8c8d;
        font-weight: 500;
    }

    .info-value {
        color: #2c3e50;
        font-weight: 600;
    }

    .control-number-box {
        background: #f8f9fa;
        border: 2px dashed #27ae60;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin: 20px 0;
    }

    .control-number-box .label {
        color: #7f8c8d;
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    .control-number-box .number {
        color: #27ae60;
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 2px;
        font-family: monospace;
    }

    .table th {
        background: #f8f9fa;
    }

    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Invoice Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View Invoice</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('finance.invoices.print', $invoice->id) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-2"></i>Print
            </a>
            <a href="{{ route('finance.invoices.download', $invoice->id) }}" class="btn btn-outline-success">
                <i class="fas fa-download me-2"></i>Download
            </a>
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-outline-finance">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Invoice Details -->
            <div class="invoice-card">
                <div class="invoice-header">
                    <h5 class="mb-0">INVOICE #{{ $invoice->invoice_number }}</h5>
                    <small>Issued: {{ $invoice->issue_date->format('d M Y') }}</small>
                </div>
                <div class="p-4">
                    <!-- Control Number -->
                    <div class="control-number-box">
                        <div class="label">Control Number</div>
                        <div class="number">{{ $invoice->control_number }}</div>
                    </div>

                    <!-- Student Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Student Information</h6>
                            <div class="info-row">
                                <span class="info-label">Name:</span>
                                <span class="info-value">{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Registration No:</span>
                                <span class="info-value">{{ $invoice->student->registration_number ?? '' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Programme:</span>
                                <span class="info-value">{{ $invoice->student->programme->name ?? '' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Invoice Information</h6>
                            <div class="info-row">
                                <span class="info-label">Invoice Type:</span>
                                <span class="info-value">{{ $invoice->getTypeNameAttribute() }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Academic Year:</span>
                                <span class="info-value">{{ $invoice->academicYear->name ?? '' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Due Date:</span>
                                <span class="info-value">{{ $invoice->due_date->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <h6 class="fw-bold mb-3">Invoice Items</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount (TZS)</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Total (TZS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ number_format($item->amount, 0) }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->total, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <th class="text-end">{{ number_format($invoice->total_amount, 0) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Paid Amount:</th>
                                <th class="text-end">{{ number_format($invoice->paid_amount, 0) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Balance:</th>
                                <th class="text-end">{{ number_format($invoice->balance, 0) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    @if($invoice->description)
                    <div class="mt-3">
                        <h6 class="fw-bold">Description</h6>
                        <p class="text-muted">{{ $invoice->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="invoice-card mb-3">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">Payment Summary</h6>
                </div>
                <div class="p-4">
                    <div class="info-row">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value">{{ number_format($invoice->total_amount, 0) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Paid Amount:</span>
                        <span class="info-value text-success">{{ number_format($invoice->paid_amount, 0) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Balance:</span>
                        <span class="info-value {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($invoice->balance, 0) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            @if($invoice->payment_status == 'paid')
                                <span class="badge-status badge-paid">Paid</span>
                            @elseif($invoice->payment_status == 'partial')
                                <span class="badge-status badge-partial">Partial</span>
                            @elseif($invoice->isOverdue())
                                <span class="badge-status badge-overdue">Overdue</span>
                            @else
                                <span class="badge-status badge-unpaid">Unpaid</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="invoice-card">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">Payment History</h6>
                </div>
                <div class="p-4">
                    @forelse($invoice->transactions as $transaction)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $transaction->transaction_type }}</div>
                            <small class="text-muted">{{ $transaction->transaction_date->format('d M Y H:i') }}</small>
                        </div>
                        <div class="text-success fw-bold">+{{ number_format($transaction->credit, 0) }}</div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">No payment transactions</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-outline-finance {
        background: transparent;
        color: #27ae60;
        border: 2px solid #27ae60;
        padding: 8px 20px;
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .btn-outline-finance:hover {
        background: #27ae60;
        color: white;
    }
</style>
@endpush