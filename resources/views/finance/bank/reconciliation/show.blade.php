@extends('layouts.financecontroller')

@section('title', 'Reconciliation Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Reconciliation Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.bank.reconciliation.index') }}">Reconciliation</a></li>
                <li class="breadcrumb-item active">{{ $reconciliation->reconciliation_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($reconciliation->status == 'in_progress')
            <form action="{{ route('finance.bank.reconciliation.complete', $reconciliation->id) }}" 
                  method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" 
                        onclick="return confirm('Complete this reconciliation? Transactions will be marked as reconciled.')">
                    <i class="fas fa-check me-2"></i>Complete Reconciliation
                </button>
            </form>
        @endif
        <a href="{{ route('finance.bank.reconciliation.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Reconciliation Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Reconciliation #:</span>
                        <span class="value fw-bold">{{ $reconciliation->reconciliation_number }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Bank Account:</span>
                        <span class="value">{{ $reconciliation->bankAccount->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Number:</span>
                        <span class="value">{{ $reconciliation->bankAccount->account_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Statement Date:</span>
                        <span class="value">{{ $reconciliation->statement_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Period:</span>
                        <span class="value">
                            {{ $reconciliation->start_date->format('d M Y') }} - 
                            {{ $reconciliation->end_date->format('d M Y') }}
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">
                            @if($reconciliation->status == 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($reconciliation->status == 'in_progress')
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $reconciliation->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $reconciliation->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($reconciliation->completed_at)
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Completed By:</span>
                            <span class="value">{{ $reconciliation->completer->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item d-flex justify-content-between mb-3">
                            <span class="label">Completed At:</span>
                            <span class="value">{{ $reconciliation->completed_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                @if($reconciliation->notes)
                    <div class="mt-3">
                        <h6>Notes:</h6>
                        <p class="text-muted">{{ $reconciliation->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Balance Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Statement Balance</h6>
                        <h4>{{ number_format($reconciliation->statement_balance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>System Balance</h6>
                        <h4>{{ number_format($reconciliation->system_balance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card {{ $reconciliation->difference == 0 ? 'bg-success' : 'bg-danger' }} text-white">
                    <div class="card-body text-center">
                        <h6>Difference</h6>
                        <h4>{{ number_format($reconciliation->difference, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="card">
            <div class="card-header">
                <h5>Transactions - {{ $reconciliation->start_date->format('M Y') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction #</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-light">
                                <td colspan="6" class="text-end fw-bold">Opening Balance:</td>
                                <td class="text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            
                            @foreach($transactions as $txn)
                                <tr class="{{ $txn->is_matched ? '' : 'table-warning' }}">
                                    <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                    <td>{{ $txn->transaction_number }}</td>
                                    <td>{{ Str::limit($txn->description, 30) }}</td>
                                    <td class="text-end text-success">
                                        {{ in_array($txn->transaction_type, ['deposit', 'opening_balance']) ? number_format($txn->amount, 2) : '-' }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ in_array($txn->transaction_type, ['withdrawal', 'transfer']) ? number_format($txn->amount, 2) : '-' }}
                                    </td>
                                    <td class="text-end">{{ number_format($txn->running_balance, 2) }}</td>
                                    <td>
                                        @if($txn->is_matched)
                                            <span class="badge bg-success">Matched</span>
                                        @else
                                            <span class="badge bg-warning">Unmatched</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            
                            <tr class="table-secondary">
                                <td colspan="6" class="text-end fw-bold">Closing Balance:</td>
                                <td class="text-end fw-bold">{{ number_format($reconciliation->system_balance, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection