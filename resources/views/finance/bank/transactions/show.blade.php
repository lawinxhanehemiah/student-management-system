@extends('layouts.financecontroller')

@section('title', 'Transaction Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Transaction Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.transactions.index') }}">Transactions</a></li>
                <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.bank.transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <!-- Transaction Information -->
        <div class="card">
            <div class="card-header">
                <h5>Transaction Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-{{ 
                        $transaction->transaction_type == 'deposit' ? 'arrow-down text-success' : 
                        ($transaction->transaction_type == 'withdrawal' ? 'arrow-up text-danger' : 
                        ($transaction->transaction_type == 'transfer' ? 'exchange-alt text-info' : 'circle')) 
                    }} fa-4x"></i>
                    <h4 class="mt-2">{{ strtoupper(str_replace('_', ' ', $transaction->transaction_type)) }}</h4>
                    {!! $transaction->status_badge !!}
                </div>

                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Transaction Number:</span>
                        <span class="value fw-bold">{{ $transaction->transaction_number }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Date:</span>
                        <span class="value">{{ $transaction->transaction_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Bank Account:</span>
                        <span class="value">
                            <a href="{{ route('finance.bank.accounts.show', $transaction->bankAccount->id) }}">
                                {{ $transaction->bankAccount->bank_name }} - {{ $transaction->bankAccount->account_name }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Amount:</span>
                        <span class="value fw-bold text-{{ 
                            $transaction->transaction_type == 'deposit' ? 'success' : 
                            ($transaction->transaction_type == 'withdrawal' ? 'danger' : 'info') 
                        }}">
                            {{ $transaction->transaction_type == 'deposit' ? '+' : '-' }}
                            {{ number_format($transaction->amount, 2) }}
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Balance Before:</span>
                        <span class="value">{{ number_format($transaction->balance_before, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Balance After:</span>
                        <span class="value fw-bold">{{ number_format($transaction->balance_after, 2) }}</span>
                    </div>

                    @if($transaction->reference_type)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Linked To:</span>
                            <span class="value">
                                @if($transaction->reference_type == 'App\Models\Payment')
                                    <a href="{{ route('finance.payments.show', $transaction->reference_id) }}">
                                        Payment #{{ $transaction->reference_id }}
                                    </a>
                                @else
                                    {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($transaction->metadata['reference'] ?? false)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Reference:</span>
                            <span class="value">{{ $transaction->metadata['reference'] }}</span>
                        </div>
                    @endif

                    @if($transaction->metadata['payee'] ?? false)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Payee:</span>
                            <span class="value">{{ $transaction->metadata['payee'] }}</span>
                        </div>
                    @endif

                    @if($transaction->metadata['payment_method'] ?? false)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Payment Method:</span>
                            <span class="value">{{ ucfirst($transaction->metadata['payment_method']) }}</span>
                        </div>
                    @endif

                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $transaction->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $transaction->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                @if($transaction->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $transaction->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Transfer Details (if transfer) -->
        @if($transaction->transaction_type == 'transfer' && $transaction->metadata)
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Transfer Details</h5>
                </div>
                <div class="card-body">
                    @php
                        $transferType = $transaction->metadata['transfer_type'] ?? '';
                        $otherAccountId = $transferType == 'outgoing' 
                            ? ($transaction->metadata['to_account_id'] ?? null)
                            : ($transaction->metadata['from_account_id'] ?? null);
                    @endphp

                    @if($otherAccountId)
                        @php
                            $otherAccount = \App\Models\BankAccount::find($otherAccountId);
                        @endphp
                        @if($otherAccount)
                            <div class="alert alert-info">
                                <i class="fas fa-exchange-alt me-2"></i>
                                @if($transferType == 'outgoing')
                                    Transferred <strong>{{ number_format($transaction->amount, 2) }}</strong> to:
                                @else
                                    Received <strong>{{ number_format($transaction->amount, 2) }}</strong> from:
                                @endif
                            </div>
                            <div class="info-list">
                                <div class="info-item d-flex justify-content-between mb-3">
                                    <span class="label">Bank:</span>
                                    <span class="value">{{ $otherAccount->bank_name }}</span>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-3">
                                    <span class="label">Account Name:</span>
                                    <span class="value">{{ $otherAccount->account_name }}</span>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-3">
                                    <span class="label">Account Number:</span>
                                    <span class="value">{{ $otherAccount->account_number }}</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        <!-- Linked Payment Details -->
        @if($transaction->reference_type == 'App\Models\Payment' && $transaction->reference)
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Linked Payment</h5>
                </div>
                <div class="card-body">
                    @php
                        $payment = $transaction->reference;
                    @endphp
                    @if($payment)
                        <div class="info-list">
                            <div class="info-item d-flex justify-content-between mb-3">
                                <span class="label">Payment #:</span>
                                <span class="value">{{ $payment->payment_number }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between mb-3">
                                <span class="label">Student:</span>
                                <span class="value">{{ $payment->student->user->first_name ?? '' }} {{ $payment->student->user->last_name ?? '' }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between mb-3">
                                <span class="label">Amount:</span>
                                <span class="value">{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="info-item d-flex justify-content-between mb-3">
                                <span class="label">Method:</span>
                                <span class="value">{{ ucfirst($payment->payment_method) }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('finance.payments.show', $payment->id) }}" class="btn btn-sm btn-primary">
                                View Payment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Audit Trail -->
        <div class="card">
            <div class="card-header">
                <h5>Audit Trail</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-badge bg-primary">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Transaction Created</h6>
                            <p class="text-muted small mb-0">
                                By: {{ $transaction->creator->name ?? 'N/A' }}<br>
                                At: {{ $transaction->created_at->format('d M Y H:i:s') }}
                            </p>
                        </div>
                    </div>
                    
                    @if($transaction->status == 'completed')
                        <div class="timeline-item">
                            <div class="timeline-badge bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Transaction Completed</h6>
                                <p class="text-muted small mb-0">
                                    The transaction was processed successfully
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($transaction->updated_at != $transaction->created_at)
                        <div class="timeline-item">
                            <div class="timeline-badge bg-warning">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Last Updated</h6>
                                <p class="text-muted small mb-0">
                                    At: {{ $transaction->updated_at->format('d M Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-badge {
    position: absolute;
    left: -30px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}
.timeline-content {
    padding-left: 10px;
}
</style>
@endpush