@extends('layouts.admission')

@section('title', 'Applicant Ranking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Applicant Ranking & Selection</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#calculateRankingModal">
                            <i class="feather-refresh-cw"></i> Calculate Ranking
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#autoSelectModal">
                            <i class="feather-users"></i> Auto Select
                        </button>
                        <a href="{{ route('admission.selection.export-ranking', request()->all()) }}" class="btn btn-info btn-sm">
                            <i class="feather-download"></i> Export CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Programme</label>
                                <select name="programme_id" class="form-control">
                                    <option value="">All Programmes</option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}" {{ $selectedProgrammeId == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Intake</label>
                                <select name="intake" class="form-control">
                                    <option value="March" {{ $selectedIntake == 'March' ? 'selected' : '' }}>March</option>
                                    <option value="September" {{ $selectedIntake == 'September' ? 'selected' : '' }}>September</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, App No, Phone..." value="{{ $search }}">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">
                                    <i class="feather-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="feather-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Applicants</span>
                                    <span class="info-box-number">{{ $statistics['total_applicants'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="feather-bar-chart-2"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg CSEE Points</span>
                                    <span class="info-box-number">{{ $statistics['avg_csee_points'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Score Range</span>
                                    <span class="info-box-number">{{ $statistics['score_min'] }} - {{ $statistics['score_max'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="feather-trending-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Average Score</span>
                                    <span class="info-box-number">{{ $statistics['score_avg'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Division Distribution -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h5 class="card-title">Division Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="progress-group">
                                        <span class="progress-text">Division I</span>
                                        <span class="progress-number"><b>{{ $statistics['division_counts']['I'] }}</b>/{{ $statistics['total_applicants'] }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($statistics['division_counts']['I'] / max($statistics['total_applicants'], 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        <span class="progress-text">Division II</span>
                                        <span class="progress-number"><b>{{ $statistics['division_counts']['II'] }}</b>/{{ $statistics['total_applicants'] }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ ($statistics['division_counts']['II'] / max($statistics['total_applicants'], 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        <span class="progress-text">Division III</span>
                                        <span class="progress-number"><b>{{ $statistics['division_counts']['III'] }}</b>/{{ $statistics['total_applicants'] }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($statistics['division_counts']['III'] / max($statistics['total_applicants'], 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        <span class="progress-text">Division IV</span>
                                        <span class="progress-number"><b>{{ $statistics['division_counts']['IV'] }}</b>/{{ $statistics['total_applicants'] }}</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: {{ ($statistics['division_counts']['IV'] / max($statistics['total_applicants'], 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Programme Summary Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Programme</th>
                                    <th>Capacity</th>
                                    <th>Applied</th>
                                    <th>Selected</th>
                                    <th>Approved</th>
                                    <th>Available</th>
                                    <th>Selection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectionSummary as $summary)
                                    <tr>
                                        <td>{{ $summary['programme_name'] }}</td>
                                        <td>{{ $summary['capacity'] }}</td>
                                        <td>{{ $summary['submitted'] }}</td>
                                        <td>{{ $summary['approved'] }}</td>
                                        <td>{{ $summary['approved'] }}</td>
                                        <td>
                                            @if($summary['available_slots'] > 0)
                                                <span class="badge badge-success">{{ $summary['available_slots'] }}</span>
                                            @else
                                                <span class="badge badge-danger">Full</span>
                                            @endif
                                        </td>
                                        <td>{{ $summary['selection_rate'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Applicants Table -->
                    <form method="POST" action="{{ route('admission.selection.bulk-select') }}" id="bulkSelectForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Rank</th>
                                        <th>App No.</th>
                                        <th>Full Name</th>
                                        <th>Contact</th>
                                        <th>Programme</th>
                                        <th>CSEE</th>
                                        <th>Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" class="applicant-checkbox">
                                            </td>
                                            <td>
                                                @if($app->rank_position <= 10)
                                                    <span class="badge badge-success badge-pill">{{ $app->rank_position }}</span>
                                                @elseif($app->rank_position <= 30)
                                                    <span class="badge badge-info badge-pill">{{ $app->rank_position }}</span>
                                                @else
                                                    <span class="badge badge-secondary badge-pill">{{ $app->rank_position }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $app->application_number }}</td>
                                            <td>
                                                <strong>{{ $app->first_name }} {{ $app->middle_name }} {{ $app->last_name }}</strong><br>
                                                <small>{{ $app->date_of_birth ? \Carbon\Carbon::parse($app->date_of_birth)->age : 'N/A' }} years</small>
                                            </td>
                                            <td>
    {{ $app->phone_number }}<br>
    <small>{{ $app->email }}</small>
</td>
                                            <td>{{ $app->programme_name ?? 'N/A' }}</td>
                                            <td>
                                                {{ $app->csee_points ?? 'N/A' }} points<br>
                                                <small>Div {{ $app->csee_division ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $app->ranking_score ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success select-one" data-id="{{ $app->id }}">
                                                    <i class="feather-check"></i> Select
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning waitlist-one" data-id="{{ $app->id }}">
                                                    <i class="feather-clock"></i> Waitlist
                                                </button>
                                                <a href="{{ route('admission.applicants.show', $app->id) }}" class="btn btn-sm btn-info">
                                                    <i class="feather-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No applicants found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="bulkSelectBtn">
                                    <i class="feather-check-square"></i> Bulk Select Selected
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calculate Ranking Modal -->
<div class="modal fade" id="calculateRankingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculate Ranking Scores</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>This will calculate ranking scores for all submitted applicants based on:</p>
                <ul>
                    <li>CSEE Points (50% weight)</li>
                    <li>ACSEE Points (30% weight if available)</li>
                    <li>Division Bonus (20% weight)</li>
                </ul>
                <div class="alert alert-warning">
                    <i class="feather-alert-triangle"></i> This operation may take a few moments.
                </div>
                <form id="calculateRankingForm">
                    @csrf
                    <input type="hidden" name="programme_id" value="{{ $selectedProgrammeId }}">
                    <input type="hidden" name="intake" value="{{ $selectedIntake }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="calculateRankingBtn">Calculate Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Auto Select Modal -->
<div class="modal fade" id="autoSelectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Select Top Candidates</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="autoSelectForm">
                    @csrf
                    <div class="form-group">
                        <label>Number of candidates to select</label>
                        <input type="number" name="number_to_select" class="form-control" value="50" min="1" max="500">
                    </div>
                    <div class="form-group">
                        <label>Programme (Optional)</label>
                        <select name="programme_id" class="form-control">
                            <option value="">All Programmes</option>
                            @foreach($programmes as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="intake" value="{{ $selectedIntake }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="autoSelectBtn">Auto Select</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.applicant-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Calculate Ranking
    $('#calculateRankingBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="feather-loader fa-spin"></i> Calculating...');
        
        $.ajax({
            url: '{{ route("admission.selection.ranking.calculate") }}',
            method: 'POST',
            data: $('#calculateRankingForm').serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message);
                    btn.prop('disabled', false).html('Calculate Now');
                }
            },
            error: function(xhr) {
                toastr.error('Calculation failed: ' + xhr.responseJSON.message);
                btn.prop('disabled', false).html('Calculate Now');
            }
        });
    });
    
    // Auto Select
    $('#autoSelectBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="feather-loader fa-spin"></i> Selecting...');
        
        $.ajax({
            url: '{{ route("admission.selection.auto-select") }}',
            method: 'POST',
            data: $('#autoSelectForm').serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message);
                    btn.prop('disabled', false).html('Auto Select');
                }
            },
            error: function(xhr) {
                toastr.error('Auto selection failed');
                btn.prop('disabled', false).html('Auto Select');
            }
        });
    });
    
    // Single Select
    $('.select-one').on('click', function() {
        var id = $(this).data('id');
        var btn = $(this);
        
        if (confirm('Are you sure you want to select this applicant?')) {
            btn.prop('disabled', true).html('<i class="feather-loader fa-spin"></i>');
            
            $.ajax({
                url: '{{ url("admission/selection/select") }}/' + id,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).html('<i class="feather-check"></i> Select');
                    }
                },
                error: function() {
                    toastr.error('Failed to select applicant');
                    btn.prop('disabled', false).html('<i class="feather-check"></i> Select');
                }
            });
        }
    });
});
</script>
@endsection