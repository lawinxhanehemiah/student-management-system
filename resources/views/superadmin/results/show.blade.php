@extends('layouts.superadmin')

@section('title', 'Result Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Result Details</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>Student</th><td>{{ $result->student->user->full_name ?? 'N/A' }} ({{ $result->student->registration_number ?? '' }})</td></tr>
                        <tr><th>Module</th><td>{{ $result->module->code }} - {{ $result->module->name }}</td></tr>
                        <tr><th>Academic Year</th><td>{{ $result->academicYear->name ?? 'N/A' }}</td></tr>
                        <tr><th>Semester</th><td>{{ $result->semester }}</td></tr>
                        <tr><th>CA Score</th><td>{{ $result->ca_score ?? '-' }}</td></tr>
                        <tr><th>Exam Score</th><td>{{ $result->exam_score ?? '-' }}</td></tr>
                        <tr><th>Final Score</th><td>{{ $result->final_score ?? ($result->ca_score && $result->exam_score ? $result->ca_score * 0.4 + $result->exam_score * 0.6 : '-') }}</td></tr>
                        <tr><th>Grade</th><td>{{ $result->grade ?? '-' }}</td></tr>
                        <tr><th>Grade Point</th><td>{{ $result->grade_point ?? '-' }}</td></tr>
                        <tr><th>Status</th><td>{{ $result->status }}</td></tr>
                        <tr><th>Source</th><td>{{ $result->source }}</td></tr>
                        <tr><th>Version</th><td>{{ $result->version }}</td></tr>
                        <tr><th>Current Version</th><td>{{ $result->is_current ? 'Yes' : 'No' }}</td></tr>
                        <tr><th>Approved By</th><td>{{ $result->approvedBy->name ?? 'N/A' }}</td></tr>
                        <tr><th>Approved At</th><td>{{ $result->approved_at ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection