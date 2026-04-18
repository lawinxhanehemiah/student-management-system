@extends('layouts.admission')

@section('title', 'Intake Details - ' . $intake->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Intake Details</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.intakes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('admission.intakes.edit', $intake->id) }}" class="btn btn-primary btn-sm">
                            <i class="feather-edit"></i> Edit Intake
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Basic Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th width="40%">Intake Name</th><td><strong>{{ $intake->name }}</strong></td></tr>
                                <tr><th>Intake Code</th><td>{{ $intake->code }}</td></tr>
                                <tr><th>Academic Year</th><td>{{ $intake->academic_year_name }}</td></tr>
                                <tr><th>Status</th>
                                    <td>
                                        @php
                                            $statusColors = ['upcoming' => 'secondary', 'open' => 'success', 'closed' => 'danger', 'completed' => 'info'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$intake->status] }}">{{ ucfirst($intake->status) }}</span>
                                     </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr><th>Start Date</th><td>{{ \Carbon\Carbon::parse($intake->start_date)->format('d/m/Y') }}</td></tr>
                                <tr><th>End Date</th><td>{{ \Carbon\Carbon::parse($intake->end_date)->format('d/m/Y') }}</td></tr>
                                <tr><th>Application Deadline</th><td>{{ \Carbon\Carbon::parse($intake->application_deadline)->format('d/m/Y') }}</td></tr>
                                <tr><th>Registration Deadline</th><td>{{ $intake->registration_deadline ? \Carbon\Carbon::parse($intake->registration_deadline)->format('d/m/Y') : 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="feather-book"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Assigned Programmes</span>
                                    <span class="info-box-number">{{ $assignedProgrammes->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="feather-file-text"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Applications</span>
                                    <span class="info-box-number">{{ $applications }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Capacity</span>
                                    <span class="info-box-number">{{ $assignedProgrammes->sum('capacity') ?: 'Unlimited' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assigned Programmes -->
                    <h5 class="mt-4">Assigned Programmes</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Programme Code</th>
                                    <th>Programme Name</th>
                                    <th>Capacity</th>
                                    <th>Applications</th>
                                    <th>Selection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignedProgrammes as $programme)
                                    <tr>
                                        <td>{{ $programme->code }}</td
                                        <td>{{ $programme->name }}</td
                                        <td>{{ $programme->capacity ?: 'Unlimited' }}</td
                                        <td>
                                            @php
                                                $appCount = DB::table('applications')->where('selected_program_id', $programme->programme_id)->where('intake', $intake->name)->count();
                                            @endphp
                                            {{ $appCount }}
                                         </td
                                        <td>
                                            @php
                                                $rate = $programme->capacity > 0 ? round(($appCount / $programme->capacity) * 100, 1) : 0;
                                            @endphp
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $rate > 80 ? 'danger' : ($rate > 60 ? 'warning' : 'success') }}" 
                                                     style="width: {{ min($rate, 100) }}%">
                                                    {{ $rate }}%
                                                </div>
                                            </div>
                                         </td
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No programmes assigned</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection