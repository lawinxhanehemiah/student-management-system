@extends('layouts.financecontroller')

@section('title', 'Ledger Report')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Ledger Report</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">General Ledger</a></li>
                <li class="breadcrumb-item active">Ledger Report</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($selectedAccount)
            <a href="{{ route('finance.general-ledger.ledger-reports.export', request()->query()) }}" 
               class="btn btn-secondary">
                <i class="fas fa-download me-2"></i>Export
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.general-ledger.ledger-reports.index') }}" method="GET" class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label for="account_id" class="form-label required">Select Account</label>
                    <select name="account_id" id="account_id" class="form-select" required>
                        <option value="">Choose Account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" 
                                {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" 
                           value="{{ request('from_date', date('Y-m-01')) }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" 
                           value="{{ request('to_date', date('Y-m-d')) }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="account_type" class="form-label">Account Type</label>
                    <select name="account_type" id="account_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($accountTypes as $key => $type)
                            <option value="{{ $key }}" {{ request('account_type') == $key ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-filter me-2">
                        <i class="fas fa-search me-2"></i>Generate Report
                    </button>
                    <a href="{{ route('finance.general-ledger.ledger-reports.summary') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar me-2"></i>Summary
                    </a>
                </div>
            </form>
        </div>

        @if($selectedAccount)
            <!-- Report Header -->
            <div class="text-center mb-4">
                <h4>GENERAL LEDGER REPORT</h4>
                <h5>{{ $selectedAccount->account_code }} - {{ $selectedAccount->account_name }}</h5>
                <p class="text-muted">
                    Period: {{ date('F d, Y', strtotime(request('from_date', date('Y-m-01')))) }} 
                    to {{ date('F d, Y', strtotime(request('to_date', date('Y-m-d')))) }}
                </p>
                <p>
                    <strong>Opening Balance:</strong> {{ number_format($openingBalance, 2) }} |
                    <strong>Closing Balance:</strong> {{ number_format($closingBalance, 2) }}
                </p>
            </div>

            <!-- Ledger Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Journal #</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Opening Balance:</td>
                            <td class="text-end fw-bold">{{ number_format($openingBalance, 2) }}</td>
                        </tr>

                        @forelse($ledgerEntries as $entry)
                            <tr>
                                <td>{{ $entry->journalEntry->entry_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('finance.general-ledger.journal-entries.show', $entry->journalEntry->id) }}">
                                        {{ $entry->journalEntry->journal_number }}
                                    </a>
                                </td>
                                <td>{{ $entry->description ?? $entry->journalEntry->description }}</td>
                                <td class="text-end">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                                <td class="text-end">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                                <td class="text-end fw-bold">{{ number_format($entry->running_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted">No transactions found for this period</p>
                                </td>
                            </tr>
                        @endforelse

                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Closing Balance:</td>
                            <td class="text-end fw-bold">{{ number_format($closingBalance, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($ledgerEntries->isNotEmpty())
                <!-- Summary -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Total Debits</h6>
                                <h4>{{ number_format($ledgerEntries->sum('debit'), 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Total Credits</h6>
                                <h4>{{ number_format($ledgerEntries->sum('credit'), 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Net Movement</h6>
                                <h4>{{ number_format($closingBalance - $openingBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <img src="{{ asset('assets/img/select-account.svg') }}" alt="Select Account" height="150">
                <h5 class="mt-3">Select an account to view ledger report</h5>
                <p class="text-muted">Choose an account from the dropdown above to generate the report</p>
            </div>
        @endif
    </div>
</div>
@endsection