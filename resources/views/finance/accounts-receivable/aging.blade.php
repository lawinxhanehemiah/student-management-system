@extends('layouts.financecontroller')

@section('title', 'Aging Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Aging Report</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="{{ route('finance.accounts-receivable.index') }}" class="breadcrumb-item">Accounts Receivable</a>
                <span class="breadcrumb-item active">Aging Report</span>
            </div>
        </div>
        <div class="btn-list">
            <div class="dropdown">
                <button class="btn btn-success-light btn-wave dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="feather-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('finance.accounts-receivable.export-aging', ['format' => 'pdf', 'as_at' => $asAtDate->format('Y-m-d')]) }}">PDF</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.accounts-receivable.export-aging', ['format' => 'excel', 'as_at' => $asAtDate->format('Y-m-d')]) }}">Excel</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.accounts-receivable.export-aging', ['format' => 'csv', 'as_at' => $asAtDate->format('Y-m-d')]) }}">CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- As At Date -->
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted">Report as at:</span>
                            <h4 class="mb-0">{{ $asAtDate->format('d F Y') }}</h4>
                        </div>
                        <div>
                            <input type="date" class="form-control" id="asAtDate" value="{{ $asAtDate->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aging Summary Cards -->
    <div class="row">
        @foreach($aging as $bucket => $data)
        @php
            $colors = [
                'current' => ['bg' => 'success', 'icon' => 'check-circle'],
                '1_30_days' => ['bg' => 'warning', 'icon' => 'alert-circle'],
                '31_60_days' => ['bg' => 'orange', 'icon' => 'alert-triangle'],
                '61_90_days' => ['bg' => 'danger', 'icon' => 'x-circle'],
                '90_plus_days' => ['bg' => 'dark', 'icon' => 'skull']
            ];
            $color = $colors[$bucket] ?? ['bg' => 'secondary', 'icon' => 'help-circle'];
        @endphp
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-fill">
                            <span class="d-block mb-2 fw-medium">{{ str_replace('_', ' ', ucwords($bucket)) }}</span>
                            <h5 class="fw-semibold mb-1">{{ number_format($data['amount'], 2) }}</h5>
                            <small class="text-muted">{{ $data['count'] }} invoices</small>
                        </div>
                        <div class="avatar avatar-lg bg-{{ $color['bg'] }}-transparent">
                            <i class="feather-{{ $color['icon'] }} fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Aging Chart -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging Distribution</div>
                </div>
                <div class="card-body">
                    <canvas id="agingChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging Summary</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Bucket</th>
                                    <th>Count</th>
                                    <th>Amount</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAmount = array_sum(array_column($aging, 'amount'));
                                @endphp
                                @foreach($aging as $bucket => $data)
                                <tr>
                                    <td>
                                        <span class="badge" style="background: {{ $bucket === 'current' ? '#28a745' : ($bucket === '1_30_days' ? '#ffc107' : ($bucket === '31_60_days' ? '#fd7e14' : ($bucket === '61_90_days' ? '#dc3545' : '#6c757d'))) }}; color: white;">
                                            {{ str_replace('_', ' ', ucwords($bucket)) }}
                                        </span>
                                    </td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['amount'], 2) }}</td>
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
    </div>

    <!-- Aging by Programme -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging by Programme</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap">
                            <thead>
                                <tr>
                                    <th>Programme</th>
                                    <th>Students</th>
                                    <th>Invoices</th>
                                    <th>Current</th>
                                    <th>1-30 Days</th>
                                    <th>31-60 Days</th>
                                    <th>61-90 Days</th>
                                    <th>90+ Days</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programmeBreakdown as $programme)
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-semibold">{{ $programme->name }}</span>
                                            <small class="d-block text-muted">{{ $programme->code }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $programme->student_count }}</td>
                                    <td>{{ $programme->invoice_count }}</td>
                                    <td class="text-success">{{ number_format($programme->bucket_1_30, 0) }}</td>
                                    <td class="text-warning">{{ number_format($programme->bucket_31_60, 0) }}</td>
                                    <td class="text-orange">{{ number_format($programme->bucket_61_90, 0) }}</td>
                                    <td class="text-danger">{{ number_format($programme->bucket_90_plus, 0) }}</td>
                                    <td class="fw-semibold">{{ number_format($programme->total_balance, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Largest Balances -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Largest Outstanding Balances</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Balance</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($largestBalances as $invoice)
                                <tr>
                                    <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                                    <td>{{ $invoice->student->registration_number ?? '' }}</td>
                                    <td class="fw-semibold text-danger">{{ number_format($invoice->balance, 2) }}</td>
                                    <td>{{ $invoice->due_date->diffInDays(now()) }} days</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Aging by Invoice Type</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>1-30 Days</th>
                                    <th>31-60 Days</th>
                                    <th>61-90 Days</th>
                                    <th>90+ Days</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($typeBreakdown as $type)
                                <tr>
                                    <td>{{ str_replace('_', ' ', ucwords($type->invoice_type)) }}</td>
                                    <td>{{ $type->count }}</td>
                                    <td>{{ number_format($type->bucket_1_30, 0) }}</td>
                                    <td>{{ number_format($type->bucket_31_60, 0) }}</td>
                                    <td>{{ number_format($type->bucket_61_90, 0) }}</td>
                                    <td>{{ number_format($type->bucket_90_plus, 0) }}</td>
                                    <td class="fw-semibold">{{ number_format($type->total_balance, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Aging by Bucket -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        @foreach($aging as $bucket => $data)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                               data-bs-toggle="tab" 
                               href="#{{ $bucket }}"
                               role="tab">
                                {{ str_replace('_', ' ', ucwords($bucket)) }}
                                <span class="badge bg-{{ $bucket === 'current' ? 'success' : ($bucket === '1_30_days' ? 'warning' : ($bucket === '31_60_days' ? 'orange' : ($bucket === '61_90_days' ? 'danger' : 'dark'))) }} ms-1">
                                    {{ $data['count'] }}
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        @foreach($aging as $bucket => $data)
                        <div class="tab-pane {{ $loop->first ? 'active' : '' }}" id="{{ $bucket }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Student</th>
                                            <th>Reg No</th>
                                            <th>Type</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                            <th>Balance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['invoices'] as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->student->user->first_name ?? '' }} {{ $invoice->student->user->last_name ?? '' }}</td>
                                            <td>{{ $invoice->student->registration_number ?? '' }}</td>
                                            <td>{{ str_replace('_', ' ', ucwords($invoice->invoice_type)) }}</td>
                                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                            <td>{{ $invoice->due_date->diffInDays(now()) }} days</td>
                                            <td class="fw-semibold">{{ number_format($invoice->balance, 2) }}</td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="#" class="btn btn-sm btn-icon btn-info-light">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-icon btn-warning-light send-reminder" data-id="{{ $invoice->id }}">
                                                        <i class="feather-bell"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Hakikisha Chart.js imepakiwa -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Aging Chart initializing...');
    
    // Tafuta canvas element
    const canvas = document.getElementById('agingChart');
    console.log('Canvas element:', canvas);
    
    if (!canvas) {
        console.error('Canvas element not found!');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Prepare data - ANGALIA HII DATA INAFIKA
    const agingData = @json(array_values(array_column($aging, 'amount')));
    const agingLabels = @json(array_keys($aging)).map(function(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    });
    
    console.log('Aging Labels:', agingLabels);
    console.log('Aging Data:', agingData);
    console.log('Data types:', agingData.map(v => typeof v));
    
    // Create chart
    try {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: agingLabels,
                datasets: [{
                    label: 'Amount (TZS)',
                    data: agingData,
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
                    borderColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'TZS ' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        console.log('Chart created successfully!');
    } catch (error) {
        console.error('Error creating chart:', error);
    }
});
</script>
@endpush
@endsection