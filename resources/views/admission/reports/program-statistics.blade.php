@extends('layouts.admission')

@section('title', 'Program Statistics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Program Statistics</h4>
                    <div class="card-tools">
                        <form method="GET" class="form-inline">
                            <select name="year" class="form-control form-control-sm mr-2">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <select name="intake" class="form-control form-control-sm mr-2">
                                <option value="March" {{ $intake == 'March' ? 'selected' : '' }}>March</option>
                                <option value="September" {{ $intake == 'September' ? 'selected' : '' }}>September</option>
                            </select>
                            <select name="programme_id" class="form-control form-control-sm mr-2">
                                <option value="">All Programmes</option>
                                @foreach($programmes as $prog)
                                    <option value="{{ $prog->id }}" {{ $programmeId == $prog->id ? 'selected' : '' }}>
                                        {{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="feather-filter"></i> Filter
                            </button>
                            <a href="{{ route('admission.reports.export.program-pdf', ['year' => $year, 'programme_id' => $programmeId]) }}" class="btn btn-danger btn-sm">
                                <i class="feather-file-text"></i> PDF
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Programme Summary -->
    @if($selectedProgramme)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">{{ $selectedProgramme->name }} ({{ $selectedProgramme->code }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="feather-file-text"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Applications</span>
                                    <span class="info-box-number">{{ array_sum(array_column($programmeStatusCounts->toArray(), 'total')) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Selected</span>
                                    <span class="info-box-number">{{ $programmeStatusCounts['approved']->total ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="feather-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Under Review</span>
                                    <span class="info-box-number">{{ $programmeStatusCounts['under_review']->total ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="feather-x-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rejected</span>
                                    <span class="info-box-number">{{ $programmeStatusCounts['rejected']->total ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Programmes -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top 10 Programmes by Applications</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProgrammesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Competition Ratio (Applications per Slot)</h5>
                </div>
                <div class="card-body">
                    <canvas id="competitionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Programme Selection Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Programme</th>
                                    <th>Capacity</th>
                                    <th>Submitted</th>
                                    <th>Under Review</th>
                                    <th>Selected</th>
                                    <th>Rejected</th>
                                    <th>Waitlisted</th>
                                    <th>Selection Rate</th>
                                    <th>Available Slots</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectionDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->name }}</td>
                                        <td>{{ $detail->capacity ?? 'N/A' }}</td>
                                        <td>{{ $detail->submitted }}</td>
                                        <td>{{ $detail->under_review }}</td>
                                        <td>{{ $detail->selected }}</td>
                                        <td>{{ $detail->rejected }}</td>
                                        <td>{{ $detail->waitlisted }}</td>
                                        <td>
                                            @php $rate = $detail->submitted > 0 ? round(($detail->selected / $detail->submitted) * 100, 1) : 0; @endphp
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: {{ $rate }}%">{{ $rate }}%</div>
                                            </div>
                                        </td>
                                        <td>
                                            @php $available = ($detail->capacity ?? 0) - $detail->selected; @endphp
                                            @if($available > 0)
                                                <span class="badge badge-success">{{ $available }}</span>
                                            @else
                                                <span class="badge badge-danger">Full</span>
                                            @endif
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

    <!-- Academic Performance -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Academic Performance by Programme</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Programme</th>
                                    <th>Avg CSEE Points</th>
                                    <th>Min CSEE Points</th>
                                    <th>Max CSEE Points</th>
                                    <th>Avg ACSEE Passes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($academicPerformance as $perf)
                                    <tr>
                                        <td>{{ $perf->name }}</td>
                                        <td>{{ $perf->avg_csee_points ?? 'N/A' }}</td>
                                        <td>{{ $perf->min_csee_points ?? 'N/A' }}</td>
                                        <td>{{ $perf->max_csee_points ?? 'N/A' }}</td>
                                        <td>{{ $perf->avg_acsee_passes ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicants List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Applicants List</h5>
                    <div class="card-tools">
                        <a href="{{ route('admission.reports.export.csv', ['type' => 'applications', 'year' => $year]) }}" class="btn btn-success btn-sm">
                            <i class="feather-download"></i> Export CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>App No.</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Email/Phone</th>
                                    <th>CSEE</th>
                                    <th>ACSEE</th>
                                    <th>Status</th>
                                    <th>Submitted Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applicants as $applicant)
                                    <tr>
                                        <td>{{ $applicant->application_number }}</td>
                                        <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->last_name }}</td>
                                        <td>{{ $applicant->gender ?? 'N/A' }}</td>
                                        <td>{{ $applicant->email }}<br><small>{{ $applicant->phone }}</small></td>
                                        <td>{{ $applicant->csee_points ?? 'N/A' }} ({{ $applicant->csee_division ?? 'N/A' }})</td>
                                        <td>{{ $applicant->acsee_principal_passes ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = ['submitted' => 'warning', 'under_review' => 'primary', 'approved' => 'success', 'registered' => 'info', 'rejected' => 'danger', 'waitlisted' => 'secondary'];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$applicant->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $applicant->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($applicant->submitted_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No applicants found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $applicants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Top Programmes Chart
    var programmeLabels = @json($topProgrammes->pluck('name'));
    var programmeData = @json($topProgrammes->pluck('total_applications'));
    new Chart(document.getElementById('topProgrammesChart'), {
        type: 'bar',
        data: {
            labels: programmeLabels,
            datasets: [{
                label: 'Applications',
                data: programmeData,
                backgroundColor: '#007bff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
    
    // Competition Chart
    var compLabels = @json($competitionRatio->pluck('name'));
    var compData = @json($competitionRatio->pluck('competition_ratio'));
    new Chart(document.getElementById('competitionChart'), {
        type: 'horizontalBar',
        data: {
            labels: compLabels,
            datasets: [{
                label: 'Applicants per Slot',
                data: compData,
                backgroundColor: '#dc3545'
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
});
</script>
@endsection