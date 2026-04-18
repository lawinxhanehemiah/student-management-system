@extends('layouts.financecontroller')

@section('title', 'Supplier Statement - ' . $supplier->name)

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Supplier Statement</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.accounts-payable.suppliers.index') }}" class="text-muted">Suppliers</a> > 
                <a href="{{ route('finance.accounts-payable.suppliers.show', $supplier->id) }}" class="text-muted">{{ $supplier->name }}</a> > 
                <span>Statement</span>
            </div>
        </div>
        <div class="btn-list">
            <button class="btn btn-sm btn-primary-light" onclick="window.print()">
                <i class="feather-printer"></i> Print
            </button>
            <a href="{{ route('finance.accounts-payable.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.accounts-payable.suppliers.statement', $supplier->id) }}">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="small">From:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-sm" name="from_date" value="{{ $fromDate }}">
                    </div>
                    <div class="col-auto">
                        <label class="small">To:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-sm" name="to_date" value="{{ $toDate }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statement Header -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ $supplier->name }}</h5>
                    <p class="small text-muted mb-1">{{ $supplier->address }}</p>
                    <p class="small text-muted mb-1">Tax No: {{ $supplier->tax_number ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <h5>Statement Period</h5>
                    <p class="small text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
                    <p class="small text-muted">Opening Balance: TZS {{ number_format($openingBalance, 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-end">Debit (TZS)</th>
                            <th class="text-end">Credit (TZS)</th>
                            <th class="text-end">Balance (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row -->
                        <tr class="fw-bold">
                            <td>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</td>
                            <td colspan="2">Opening Balance</td>
                            <td class="text-end">{{ number_format($openingBalance, 0) }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end">{{ number_format($openingBalance, 0) }}</td>
                        </tr>

                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</td>
                            <td>{{ $transaction['reference'] }}</td>
                            <td>{{ $transaction['description'] }}</td>
                            <td class="text-end">{{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 0) : '-' }}</td>
                            <td class="text-end text-success">{{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 0) : '-' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($transaction['balance'], 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">
                                <p class="text-muted small mb-0">No transactions for this period</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Closing Balance:</td>
                            <td class="text-end fw-bold">{{ number_format(collect($transactions)->sum('debit'), 0) }}</td>
                            <td class="text-end fw-bold">{{ number_format(collect($transactions)->sum('credit'), 0) }}</td>
                            <td class="text-end fw-bold {{ count($transactions) > 0 ? (end($transactions)['balance'] > 0 ? 'text-danger' : 'text-success') : '' }}">
                                {{ count($transactions) > 0 ? number_format(end($transactions)['balance'], 0) : number_format($openingBalance, 0) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn-list, .card-header, form, .breadcrumb, footer { display: none; }
    .card { border: 1px solid #000 !important; }
    body { background: white; }
</style>
@endsection