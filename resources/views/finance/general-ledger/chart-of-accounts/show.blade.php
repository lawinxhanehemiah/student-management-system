@extends('layouts.financecontroller')

@section('title', 'Account Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Account Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.chart-of-accounts.index') }}">Chart of Accounts</a></li>
                <li class="breadcrumb-item active">{{ $account->account_code }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if(!$account->journalLines()->exists())
            <a href="{{ route('finance.general-ledger.chart-of-accounts.edit', $account->id) }}" 
               class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        @endif
        <a href="{{ route('finance.general-ledger.chart-of-accounts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Account Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Code:</span>
                        <span class="value fw-bold">{{ $account->account_code }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Name:</span>
                        <span class="value">{{ $account->account_name }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Account Type:</span>
                        <span class="value">
                            <span class="badge bg-{{ 
                                $account->account_type == 'asset' ? 'primary' : 
                                ($account->account_type == 'liability' ? 'warning' : 
                                ($account->account_type == 'equity' ? 'success' : 
                                ($account->account_type == 'revenue' ? 'info' : 'danger'))) 
                            }}">
                                {{ $account->type_name }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Category:</span>
                        <span class="value">{{ $account->category_name ?? '-' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Level:</span>
                        <span class="value">{{ $account->level }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Header Account:</span>
                        <span class="value">
                            @if($account->is_header)
                                <span class="badge bg-info">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">
                            @if($account->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Current Balance:</span>
                        <span class="value fw-bold">{{ number_format($account->current_balance, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Opening Balance:</span>
                        <span class="value">{{ number_format($account->opening_balance, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $account->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $account->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                @if($account->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $account->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($account->parent || $account->children->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Account Hierarchy</h5>
                </div>
                <div class="card-body">
                    @if($account->parent)
                        <div class="mb-3">
                            <h6>Parent Account:</h6>
                            <a href="{{ route('finance.general-ledger.chart-of-accounts.show', $account->parent->id) }}">
                                {{ $account->parent->account_code }} - {{ $account->parent->account_name }}
                            </a>
                        </div>
                    @endif

                    @if($account->children->count() > 0)
                        <div>
                            <h6>Child Accounts ({{ $account->children->count() }}):</h6>
                            <ul class="list-unstyled">
                                @foreach($account->children as $child)
                                    <li class="mb-2">
                                        <a href="{{ route('finance.general-ledger.chart-of-accounts.show', $child->id) }}">
                                            {{ $child->account_code }} - {{ $child->account_name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Transactions -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Recent Journal Entries</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Journal #</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEntries as $line)
                                <tr>
                                    <td>{{ $line->journalEntry->entry_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('finance.general-ledger.journal-entries.show', $line->journalEntry->id) }}">
                                            {{ $line->journalEntry->journal_number }}
                                        </a>
                                    </td>
                                    <td>{{ $line->description ?? $line->journalEntry->description }}</td>
                                    <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                                    <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($line->debit - $line->credit, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted">No transactions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection