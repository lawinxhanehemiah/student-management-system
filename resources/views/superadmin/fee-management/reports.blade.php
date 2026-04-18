@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-file-text text-primary"></i> 
            Fee Reports
        </h1>
        <div>
            <button class="btn btn-success">
                <i class="feather-download"></i> Export PDF
            </button>
            <button class="btn btn-info">
                <i class="feather-printer"></i> Print
            </button>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Generate Report</h6>
        </div>
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-control" id="reportType">
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="programme">Programme-wise</option>
                        <option value="yearly">Yearly Report</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control daterange">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Programme</label>
                    <select class="form-control">
                        <option value="">All Programmes</option>
                        @foreach(\App\Models\Programme::active()->get() as $programme)
                            <option value="{{ $programme->id }}">{{ $programme->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Level</label>
                    <select class="form-control">
                        <option value="">All Levels</option>
                        @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}">Level {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-bar-chart"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Report -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="feather-pie-chart"></i> Summary Report
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div id="feeChart" style="height: 300px;"></div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary mb-3">Programme Distribution</h6>
                    <div class="list-group">
                        @php
                            $programmeStats = \App\Models\Programme::withCount('fees')->get();
                        @endphp
                        @foreach($programmeStats as $programme)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $programme->code }}
                            <span class="badge bg-primary rounded-pill">
                                {{ $programme->fees_count }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="feather-list"></i> Detailed Fee Report
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Programme</th>
                            <th>Level</th>
                            <th>Academic Year</th>
                            <th>Registration Fee</th>
                            <th>Semester 1</th>
                            <th>Semester 2</th>
                            <th>Total Year</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allFees = \App\Models\ProgrammeFee::with(['programme', 'academicYear'])
                                ->orderBy('programme_id')
                                ->orderBy('level')
                                ->limit(20)
                                ->get();
                        @endphp
                        @foreach($allFees as $fee)
                        <tr>
                            <td>{{ $fee->programme->code ?? 'N/A' }}</td>
                            <td class="text-center">Level {{ $fee->level }}</td>
                            <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($fee->registration_fee) }}/=</td>
                            <td class="text-end">{{ number_format($fee->semester_1_fee) }}/=</td>
                            <td class="text-end">{{ number_format($fee->semester_2_fee) }}/=</td>
                            <td class="text-end">{{ number_format($fee->total_year_fee) }}/=</td>
                            <td class="text-end text-success">
                                <strong>{{ number_format($fee->total_fee) }}/=</strong>
                            </td>
                            <td class="text-center">
                                @if($fee->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if($allFees->count() > 0)
                        <tr class="table-active">
                            <td colspan="3" class="text-end"><strong>TOTALS:</strong></td>
                            <td class="text-end"><strong>{{ number_format($allFees->sum('registration_fee')) }}/=</strong></td>
                            <td class="text-end"><strong>{{ number_format($allFees->sum('semester_1_fee')) }}/=</strong></td>
                            <td class="text-end"><strong>{{ number_format($allFees->sum('semester_2_fee')) }}/=</strong></td>
                            <td class="text-end"><strong>{{ number_format($allFees->sum('total_year_fee')) }}/=</strong></td>
                            <td class="text-end text-success">
                                <strong>{{ number_format($allFees->sum('total_fee')) }}/=</strong>
                            </td>
                            <td></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Programmes</h6>
                            <h3>{{ \App\Models\Programme::count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Active Fees</h6>
                            <h3>{{ \App\Models\ProgrammeFee::active()->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Avg. Registration</h6>
                            <h3>{{ number_format(\App\Models\ProgrammeFee::avg('registration_fee')) }}/=</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Avg. Total Fee</h6>
                            <h3>{{ number_format(\App\Models\ProgrammeFee::avg('total_fee')) }}/=</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('.daterange').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        // Initialize chart
        var options = {
            series: [{
                name: 'Fees',
                data: [4500000, 3200000, 2800000, 2100000, 1500000]
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: ['BIT', 'BBA', 'BCOM', 'LLB', 'BED'],
            },
            colors: ['#4e73df'],
        };

        var chart = new ApexCharts(document.querySelector("#feeChart"), options);
        chart.render();
    });
</script>
@endsection