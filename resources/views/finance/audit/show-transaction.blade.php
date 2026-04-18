@extends('layouts.financecontroller')

@section('title', 'Transaction Log Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Transaction Log Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.audit.transaction-logs') }}">Transaction Logs</a></li>
                <li class="breadcrumb-item active">Transaction Details</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.audit.transaction-logs') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Transaction Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Transaction #</th>
                        <td><strong>{{ $log->transaction_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>Date/Time</th>
                        <td>{{ $log->transaction_date->format('d M Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Transaction Type</th>
                        <td>
                            <span class="badge bg-{{ 
                                $log->transaction_type == 'payment_received' ? 'success' : 
                                ($log->transaction_type == 'invoice_created' ? 'info' : 
                                ($log->transaction_type == 'journal_posted' ? 'primary' : 'secondary')) 
                            }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $log->transaction_type)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td class="fw-bold">{{ number_format($log->amount, 2) }} {{ $log->currency }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-{{ 
                                $log->status == 'completed' ? 'success' : 
                                ($log->status == 'pending' ? 'warning' : 'danger') 
                            }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $log->description }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Reference Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Reference Type</th>
                        <td>{{ class_basename($log->reference_type) }}</td>
                    </tr>
                    <tr>
                        <th>Reference ID</th>
                        <td>#{{ $log->reference_id }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>User Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">User</th>
                        <td><strong>{{ $log->user_name }}</strong></td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($reference)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Related {{ class_basename($reference) }}</h5>
            </div>
            <div class="card-body">
                @if(class_basename($reference) == 'Payment')
                    <p>Payment #: {{ $reference->payment_number }}</p>
                    <p>Amount: {{ number_format($reference->amount, 2) }}</p>
                @elseif(class_basename($reference) == 'Invoice')
                    <p>Invoice #: {{ $reference->invoice_number }}</p>
                    <p>Amount: {{ number_format($reference->total_amount, 2) }}</p>
                @endif
                <a href="#" class="btn btn-sm btn-primary">View Reference</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection