@extends('layouts.financecontroller')

@section('title', 'Accounts Receivable Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Accounts Receivable</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <span class="breadcrumb-item active">Accounts Receivable</span>
            </div>
        </div>
        <div class="btn-list">
            <a href="{{ route('finance.accounts-receivable.outstanding') }}" class="btn btn-primary-light btn-wave">
                <i class="feather-list"></i> Outstanding Invoices
            </a>
            <a href="{{ route('finance.accounts-receivable.aging') }}" class="btn btn-info-light btn-wave">
                <i class="feather-clock"></i> Aging Report
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Total Receivable</span>
                            <h3 class="fw-semibold mb-2">{{ number_format($summary['total_receivable'] ?? 0, 2) }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-transparent">Current: {{ number_format($summary['current'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-primary-transparent">
                            <i class="feather-credit-card fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Overdue Amount</span>
                            <h3 class="fw-semibold mb-2 text-danger">{{ number_format($summary['total_overdue'] ?? 0, 2) }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-transparent">Avg Days: {{ $summary['avg_days_overdue'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-danger-transparent">
                            <i class="feather-alert-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Collection Rate</span>
                            <h3 class="fw-semibold mb-2">{{ $summary['collection_rate'] ?? 0 }}%</h3>
                            <div class="progress progress-sm" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $summary['collection_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-success-transparent">
                            <i class="feather-trending-up fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">Bad Debt Ratio</span>
                            <h3 class="fw-semibold mb-2">{{ $summary['bad_debt_ratio'] ?? 0 }}%</h3>
                            <div class="progress progress-sm" style="height: 5px;">
                                <div class="progress-bar bg-danger" style="width: {{ $summary['bad_debt_ratio'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="avatar avatar-lg bg-danger-transparent">
                            <i class="feather-alert-octagon fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Summary -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging Summary</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Bucket</th>
                                    <th>Count</th>
                                    <th>Amount (TZS)</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAmount = array_sum(array_column($agingSummary ?? [], 'amount'));
                                @endphp
                                @foreach($agingSummary ?? [] as $bucket => $data)
                                <tr>
                                    <td>
                                        <span class="badge" style="background: {{ $bucket === 'current' ? '#28a745' : ($bucket === '1_30_days' ? '#ffc107' : ($bucket === '31_60_days' ? '#fd7e14' : ($bucket === '61_90_days' ? '#dc3545' : '#6c757d'))) }}; color: white;">
                                            {{ str_replace('_', ' ', ucwords($bucket)) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($data['count']) }}</td>
                                    <td class="fw-semibold">{{ number_format($data['amount'], 2) }}</td>
                                    <td>
                                        @php $percent = $totalAmount > 0 ? ($data['amount'] / $totalAmount) * 100 : 0; @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <span>{{ number_format($percent, 1) }}%</span>
                                            <div class="progress progress-sm flex-fill" style="height: 5px;">
                                                <div class="progress-bar" style="width: {{ $percent }}%; background: {{ $bucket === 'current' ? '#28a745' : ($bucket === '1_30_days' ? '#ffc107' : ($bucket === '31_60_days' ? '#fd7e14' : ($bucket === '61_90_days' ? '#dc3545' : '#6c757d'))) }}"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging Distribution</div>
                </div>
                <div class="card-body">
                    <canvas id="agingChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Overdue Invoices -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Recent Overdue Invoices</div>
                    <div class="card-actions">
                        <a href="{{ route('finance.accounts-receivable.outstanding') }}" class="btn btn-sm btn-primary-light">
                            View All <i class="feather-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueInvoices ?? [] as $invoice)
                                <tr>
                                    <td>
                                        <a href="#" class="fw-semibold">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                                    <td>{{ $invoice->student->registration_number ?? '' }}</td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $invoice->due_date->diffInDays(now()) }} days
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($invoice->balance, 2) }}</td>
                                    <td>
                                        <div class="hstack gap-2 flex-wrap">
                                            <a href="#" class="btn btn-sm btn-icon btn-info-light" data-bs-toggle="tooltip" title="View Invoice">
                                                <i class="feather-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-icon btn-warning-light send-reminder" 
                                                    data-id="{{ $invoice->id }}" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Send Reminder">
                                                <i class="feather-bell"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 100px;">
                                        <p class="mt-2 text-muted">No overdue invoices found</p>
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

    <!-- Top Defaulters & Collection Trends -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Top Defaulters</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Invoices</th>
                                    <th>Total Balance</th>
                                    <th>Max Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDefaulters ?? [] as $defaulter)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-primary-transparent">
                                                <span>{{ substr($defaulter->student_name, 0, 1) }}</span>
                                            </div>
                                            <span>{{ $defaulter->student_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $defaulter->registration_number }}</td>
                                    <td>{{ $defaulter->invoice_count }}</td>
                                    <td class="fw-semibold text-danger">{{ number_format($defaulter->total_balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $defaulter->max_days_overdue }} days</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">No defaulters found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Collection Trends</div>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aging Chart
    const agingCtx = document.getElementById('agingChart')?.getContext('2d');
    if (agingCtx) {
        const agingData = @json(array_column($agingSummary ?? [], 'amount'));
        const agingLabels = @json(array_map(function($key) {
            return str_replace('_', ' ', ucwords($key));
        }, array_keys($agingSummary ?? [])));
        
        new Chart(agingCtx, {
            type: 'doughnut',
            data: {
                labels: agingLabels,
                datasets: [{
                    data: agingData,
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart')?.getContext('2d');
    if (trendsCtx) {
        const trendsData = @json($trends ?? []);
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(t => t.month),
                datasets: [
                    {
                        label: 'Invoiced',
                        data: trendsData.map(t => t.invoiced),
                        borderColor: '#ffc107',
                        backgroundColor: 'transparent',
                        tension: 0.4
                    },
                    {
                        label: 'Collected',
                        data: trendsData.map(t => t.collected),
                        borderColor: '#28a745',
                        backgroundColor: 'transparent',
                        tension: 0.4
                    },
                    {
                        label: 'Outstanding',
                        data: trendsData.map(t => t.outstanding),
                        borderColor: '#dc3545',
                        backgroundColor: 'transparent',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Send Reminder
    $('.send-reminder').click(function() {
        const invoiceId = $(this).data('id');
        const btn = $(this);
        
        Swal.fire({
            title: 'Send Reminder',
            text: 'Select reminder type',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Email',
            cancelButtonText: 'SMS',
            showDenyButton: true,
            denyButtonText: 'Both'
        }).then((result) => {
            let reminderType = 'email';
            if (result.dismiss === Swal.DismissReason.cancel) {
                reminderType = 'sms';
            } else if (result.isDenied) {
                reminderType = 'both';
            } else if (!result.isConfirmed) {
                return;
            }
            
            btn.prop('disabled', true).html('<i class="feather-loader spin"></i>');
            
            $.ajax({
                url: '{{ route("finance.accounts-receivable.send-reminders") }}',
                method: 'POST',
                data: {
                    invoice_ids: [invoiceId],
                    reminder_type: reminderType,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to send reminder', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="feather-bell"></i>');
                }
            });
        });
    });
});
</script>
@endpush
@endsection