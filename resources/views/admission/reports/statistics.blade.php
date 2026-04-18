@extends('layouts.admission')

@section('title', 'Admission Statistics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Admission Statistics Dashboard</h4>
                    <div class="card-tools">
                        <form method="GET" class="form-inline">
                            <select name="year" class="form-control form-control-sm mr-2">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="feather-filter"></i> Filter
                            </button>
                            <a href="{{ route('admission.reports.export.statistics-pdf', ['year' => $year]) }}" class="btn btn-danger btn-sm">
                                <i class="feather-file-text"></i> PDF
                            </a>
                            <a href="{{ route('admission.reports.export.csv', ['type' => 'applications', 'year' => $year]) }}" class="btn btn-success btn-sm ml-2">
                                <i class="feather-download"></i> Export CSV
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $yearlyComparison['current_year_applications'] }}</h3>
                    <p>Total Applications</p>
                </div>
                <div class="icon"><i class="feather-file-text"></i></div>
                @if($yearlyComparison['applications_growth'] != 0)
                    <div class="small-box-footer">
                        @if($yearlyComparison['applications_growth'] > 0)
                            <i class="feather-trending-up"></i> {{ abs($yearlyComparison['applications_growth']) }}% from {{ $year - 1 }}
                        @else
                            <i class="feather-trending-down"></i> {{ abs($yearlyComparison['applications_growth']) }}% from {{ $year - 1 }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $approvedCount }}</h3>
                    <p>Approved/Selected</p>
                </div>
                <div class="icon"><i class="feather-user-check"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $selectionRate }}%</h3>
                    <p>Selection Rate</p>
                </div>
                <div class="icon"><i class="feather-percent"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ round($avgProcessingTime->avg_days ?? 0) }} <small>days</small></h3>
                    <p>Avg Processing Time</p>
                </div>
                <div class="icon"><i class="feather-clock"></i></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Applications by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
                        </thead>
                        <tbody>
                            @php $total = $yearlyComparison['current_year_applications']; @endphp
                            @foreach(['submitted', 'under_review', 'approved', 'registered', 'rejected', 'waitlisted'] as $status)
                                @php $count = $applicationsByStatus[$status]->total ?? 0; @endphp
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $total > 0 ? round(($count / $total) * 100, 1) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Monthly Applications Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top 10 Programmes by Applications</h5>
                </div>
                <div class="card-body">
                    <canvas id="programmeChart" height="250"></canvas>
                    <div class="mt-3">
                        @foreach($applicationsByProgramme as $prog)
                            <span class="badge badge-secondary m-1">{{ $prog->programme }}: {{ $prog->total }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Selection Rate by Programme</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr><th>Programme</th><th>Submitted</th><th>Selected</th><th>Rate</th></tr>
                            </thead>
                            <tbody>
                                @foreach($selectionByProgramme as $prog)
                                    <tr>
                                        <td>{{ $prog->programme }}</td>
                                        <td>{{ $prog->submitted }}</td>
                                        <td>{{ $prog->selected }}</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: {{ $prog->selection_rate }}%">
                                                    {{ $prog->selection_rate }}%
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

    <!-- Demographics Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Gender Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="genderChart" height="200"></canvas>
                    <div class="text-center mt-3">
                        @foreach($genderDistribution as $gender)
                            <strong>{{ $gender->gender }}:</strong> {{ $gender->total }} ({{ round(($gender->total / $yearlyComparison['current_year_applications']) * 100, 1) }}%)
                            <br>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Age Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="ageChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">CSEE Division Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="divisionChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Performance Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">CSEE Points Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="cseePointsChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top Regions by Applications</h5>
                </div>
                <div class="card-body">
                    <canvas id="regionChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Submitted', 'Under Review', 'Approved', 'Registered', 'Rejected', 'Waitlisted'],
            datasets: [{
                data: [
                    {{ $applicationsByStatus['submitted']->total ?? 0 }},
                    {{ $applicationsByStatus['under_review']->total ?? 0 }},
                    {{ $applicationsByStatus['approved']->total ?? 0 }},
                    {{ $applicationsByStatus['registered']->total ?? 0 }},
                    {{ $applicationsByStatus['rejected']->total ?? 0 }},
                    {{ $applicationsByStatus['waitlisted']->total ?? 0 }}
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#007bff', '#dc3545', '#6c757d']
            }]
        }
    });
    
    // Monthly Chart
    var monthlyData = @json($monthlyApplications);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var monthlyCounts = Array(12).fill(0);
    monthlyData.forEach(function(item) {
        monthlyCounts[item.month - 1] = item.total;
    });
    
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Applications',
                data: monthlyCounts,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true
            }]
        }
    });
    
    // Programme Chart
    var programmeLabels = @json($applicationsByProgramme->pluck('programme'));
    var programmeData = @json($applicationsByProgramme->pluck('total'));
    new Chart(document.getElementById('programmeChart'), {
        type: 'bar',
        data: {
            labels: programmeLabels,
            datasets: [{
                label: 'Applications',
                data: programmeData,
                backgroundColor: '#17a2b8'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } }
        }
    });
    
    // Gender Chart
    var genderLabels = @json($genderDistribution->pluck('gender'));
    var genderData = @json($genderDistribution->pluck('total'));
    new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderData,
                backgroundColor: ['#007bff', '#dc3545']
            }]
        }
    });
    
    // Age Chart
    var ageLabels = @json($ageGroups->pluck('age_group'));
    var ageData = @json($ageGroups->pluck('total'));
    new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: ageLabels,
            datasets: [{
                label: 'Applicants',
                data: ageData,
                backgroundColor: '#28a745'
            }]
        }
    });
    
    // Division Chart
    var divisionLabels = @json($divisionDistribution->pluck('csee_division'));
    var divisionData = @json($divisionDistribution->pluck('total'));
    new Chart(document.getElementById('divisionChart'), {
        type: 'pie',
        data: {
            labels: divisionLabels,
            datasets: [{
                data: divisionData,
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
            }]
        }
    });
    
    // CSEE Points Chart
    var pointsLabels = @json($cseePointsDistribution->pluck('points_range'));
    var pointsData = @json($cseePointsDistribution->pluck('total'));
    new Chart(document.getElementById('cseePointsChart'), {
        type: 'bar',
        data: {
            labels: pointsLabels,
            datasets: [{
                label: 'Applicants',
                data: pointsData,
                backgroundColor: '#6c757d'
            }]
        }
    });
    
    // Region Chart
    var regionLabels = @json($regionDistribution->pluck('region'));
    var regionData = @json($regionDistribution->pluck('total'));
    new Chart(document.getElementById('regionChart'), {
        type: 'horizontalBar',
        data: {
            labels: regionLabels,
            datasets: [{
                label: 'Applicants',
                data: regionData,
                backgroundColor: '#fd7e14'
            }]
        }
    });
});
</script>
@endsection