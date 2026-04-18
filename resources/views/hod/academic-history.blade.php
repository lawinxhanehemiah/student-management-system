@extends('layouts.hod')

@section('title', 'Academic History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Academic History - 
                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Reg No: {{ $student->registration_number }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- CGPA Summary Card -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">CGPA</span>
                                    <span class="info-box-number">{{ number_format($cgpa, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-book"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Credits</span>
                                    <span class="info-box-number">{{ $totalCredits ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Points</span>
                                    <span class="info-box-number">{{ number_format($totalPoints ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Level</span>
                                    <span class="info-box-number">Year {{ $student->current_level }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Semester Results -->
                    @forelse($semesterResults as $key => $semester)
                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h4 class="card-title">
                                {{ $semester['academic_year'] }} - Semester {{ $semester['semester'] }}
                                <span class="badge badge-primary ml-2">GPA: {{ $semester['gpa'] }}</span>
                            </h4>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Credits</th>
                                            <th>Marks</th>
                                            <th>Grade</th>
                                            <th>Grade Point</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($semester['registrations'] as $reg)
                                        <tr>
                                            <td>{{ $reg->course->code ?? 'N/A' }}</td>
                                            <td>{{ $reg->course->name ?? 'N/A' }}</td>
                                            <td>{{ $reg->course->credit_hours ?? 3 }}</td>
                                            <td>
                                                @if($reg->results)
                                                    {{ $reg->results->marks }}%
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reg->results)
                                                    <span class="badge badge-{{ $reg->results->grade == 'F' ? 'danger' : 'success' }}">
                                                        {{ $reg->results->grade }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reg->results)
                                                    {{ number_format($reg->results->grade_point, 2) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($reg->results)
                                                    @if($reg->results->grade == 'F')
                                                        <span class="badge badge-danger">Failed</span>
                                                    @elseif($reg->results->grade == 'I')
                                                        <span class="badge badge-warning">Incomplete</span>
                                                    @else
                                                        <span class="badge badge-success">Passed</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <td colspan="2"><strong>Semester Summary</strong></td>
                                            <td><strong>{{ $semester['total_credits'] }}</strong></td>
                                            <td colspan="2"><strong>GPA: {{ $semester['gpa'] }}</strong></td>
                                            <td><strong>{{ number_format($semester['total_points'], 2) }}</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No academic records found for this student.
                    </div>
                    @endforelse

                    <!-- Transcript Download -->
                    <div class="text-center mt-4">
                        <a href="{{ route('hod.results.transcript', $student->id) }}" 
                           class="btn btn-lg btn-primary">
                            <i class="fas fa-download"></i> Download Full Transcript
                        </a>
                        <a href="{{ route('hod.results.transcript-download', $student->id) }}" 
                           class="btn btn-lg btn-success">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection