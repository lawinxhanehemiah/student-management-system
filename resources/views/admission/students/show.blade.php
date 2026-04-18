@extends('layouts.admission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-eye me-2"></i> Student Details
        </h1>
        <div>
            <a href="{{ route('admission.students.index') }}" class="btn btn-sm btn-secondary">
                <i class="feather-list me-1"></i> All Students
            </a>
            <a href="{{ route('admission.students.register') }}" class="btn btn-sm btn-primary">
                <i class="feather-user-plus me-1"></i> New Registration
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
            <i class="feather-check-circle me-2"></i> {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Student Info Card -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="feather-user me-1"></i> Student Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                            <i class="feather-user text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold">{{ $student->user->first_name }} {{ $student->user->last_name }}</h5>
                        <span class="badge bg-success px-3 py-2">{{ $student->registration_number }}</span>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Full Name</th>
                            <td>{{ $student->user->first_name }} {{ $student->user->middle_name }} {{ $student->user->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $student->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $student->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Programme</th>
                            <td>{{ $student->programme->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Intake</th>
                            <td>{{ $student->intake }} {{ date('Y') }}</td>
                        </tr>
                        <tr>
                            <th>Study Mode</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $student->study_mode)) }}</td>
                        </tr>
                        <tr>
                            <th>Current Level</th>
                            <td>Year {{ $student->current_level }}, Semester {{ $student->current_semester }}</td>
                        </tr>
                        <tr>
                            <th>Academic Year</th>
                            <td>{{ $student->academicYear->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Guardian</th>
                            <td>{{ $student->guardian_name }}<br><small>{{ $student->guardian_phone }}</small></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge bg-{{ $student->status == 'active' ? 'success' : 'warning' }}">{{ ucfirst($student->status) }}</span></td>
                        </tr>
                        <tr>
                            <th>Registered On</th>
                            <td>{{ $student->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>
                    
                    @if($student->application)
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="fw-bold mb-2">Application Details</h6>
                        <p class="mb-1">Application #: {{ $student->application->application_number }}</p>
                        <p class="mb-0">Status: <span class="badge bg-info">{{ ucfirst($student->application->status) }}</span></p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Credentials Card -->
            <div class="card shadow mb-4 border-success">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 fw-bold">
                        <i class="feather-key me-1"></i> Login Credentials
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted d-block">Username (Registration Number)</small>
                                <strong class="fs-5">{{ $student->registration_number }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted d-block">Default Password</small>
                                <strong class="fs-5">{{ $student->registration_number }}</strong>
                                <span class="badge bg-warning ms-2">Must change on first login</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="feather-info me-2"></i>
                        Student can login using Registration Number as both username and password.
                    </div>
                </div>
            </div>

            <!-- Invoices Card -->
            @if($invoices->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="feather-file-text me-1"></i> Fee Invoices
                    </h6>
                    <span class="badge bg-primary">{{ $invoices->count() }} Invoice(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Control #</th>
                                    <th>Academic Year</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->control_number ?? 'N/A' }}</td>
                                    <td>{{ $invoice->academicYear->name ?? 'N/A' }}</td>
                                    <td>TZS {{ number_format($invoice->total_amount, 0) }}</td>
                                    <td>TZS {{ number_format($invoice->paid_amount, 0) }}</td>
                                    <td class="fw-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        TZS {{ number_format($invoice->balance, 0) }}
                                    </td>
                                    <td>
                                        @if($invoice->payment_status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->payment_status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="feather-zap me-1"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary w-100 py-3">
                                <i class="feather-file-text d-block mb-1" style="font-size: 1.5rem;"></i>
                                Generate Invoice
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-success w-100 py-3">
                                <i class="feather-credit-card d-block mb-1" style="font-size: 1.5rem;"></i>
                                Record Payment
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-info w-100 py-3">
                                <i class="feather-printer d-block mb-1" style="font-size: 1.5rem;"></i>
                                Print ID Card
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection