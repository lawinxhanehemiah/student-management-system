@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Direct Adjustment (Super Admin - No Approval Required)</h5>
            @if(isset($student))
                <small>Student: {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }} ({{ $student->registration_number ?? '' }})</small>
            @endif
        </div>
        <div class="card-body">
            @if(!isset($student))
                <div class="alert alert-warning">
                    <i class="feather-alert-triangle"></i> Please search for a student first.
                </div>
                <form action="{{ route('superadmin.payment-adjustments.search-student') }}" method="GET" class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="registration_number" class="form-control" 
                               placeholder="Enter Student Registration Number" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather-user"></i> Search Student
                        </button>
                    </div>
                </form>
            @else
            <form method="POST" action="{{ route('superadmin.payment-adjustments.process-direct', $student->id) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Request Type <span class="text-danger">*</span></label>
                    <select name="request_type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="manual_payment">Manual Payment</option>
                        <option value="correction">Correction</option>
                        <option value="void">Void</option>
                        <option value="refund">Refund</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this adjustment is needed..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Related Invoice (optional)</label>
                    <select name="invoice_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($student->invoices ?? [] as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - Balance: {{ number_format($inv->balance,0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Additional Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('superadmin.payment-adjustments.index') }}" class="btn btn-secondary">
                        <i class="feather-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-zap"></i> Process Adjustment (No Approval)
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection