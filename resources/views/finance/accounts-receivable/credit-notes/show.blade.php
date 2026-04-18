@extends('layouts.financecontroller')

@section('title', 'Credit Note Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Credit Note Details</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <a href="{{ route('finance.credit-notes.index') }}" class="breadcrumb-item">Credit Notes</a>
                <span class="breadcrumb-item active">{{ $creditNote->credit_note_number }}</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.credit-notes.print', $creditNote->id) }}" target="_blank" class="btn btn-primary-light btn-wave">
                <i class="feather-printer"></i> Print
            </a>
            <a href="{{ route('finance.credit-notes.download', $creditNote->id) }}" class="btn btn-success-light btn-wave">
                <i class="feather-download"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Credit Note Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Credit Note Number</label>
                            <h5>{{ $creditNote->credit_note_number }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Status</label><br>
                            @if($creditNote->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($creditNote->status == 'used')
                                <span class="badge bg-info">Used</span>
                            @elseif($creditNote->status == 'expired')
                                <span class="badge bg-warning">Expired</span>
                            @elseif($creditNote->status == 'void')
                                <span class="badge bg-danger">Void</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Issue Date</label>
                            <h5>{{ $creditNote->issue_date->format('d F Y') }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Expiry Date</label>
                            <h5>{{ $creditNote->expiry_date ? $creditNote->expiry_date->format('d F Y') : 'No expiry' }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Original Amount</label>
                            <h5 class="text-primary">TZS {{ number_format($creditNote->amount, 2) }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Remaining Amount</label>
                            <h5 class="text-success">TZS {{ number_format($creditNote->remaining_amount, 2) }}</h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Reason</label>
                            <h5>{{ ucfirst(str_replace('_', ' ', $creditNote->reason)) }}</h5>
                        </div>
                        @if($creditNote->description)
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Description</label>
                            <p>{{ $creditNote->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Student Information</div>
                </div>
                <div class="card-body">
                    <h5>{{ $creditNote->student->user->first_name ?? '' }} {{ $creditNote->student->user->last_name ?? '' }}</h5>
                    <p class="text-muted">{{ $creditNote->student->registration_number ?? 'N/A' }}</p>
                    <hr>
                    <p><strong>Programme:</strong> {{ $creditNote->student->programme->name ?? 'N/A' }}</p>
                    <p><strong>Academic Year:</strong> {{ $creditNote->academicYear->name ?? 'N/A' }}</p>
                    <p><strong>Source Invoice:</strong> {{ $creditNote->invoice->invoice_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection