@extends('layouts.superadmin')

@section('title', 'Integrity Checks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Integrity Check Results</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.integrity.dashboard') }}" class="btn btn-secondary btn-sm">Back to Dashboard</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($results['missing']) && $type == 'missing')
                        <h4>Missing Results ({{ count($results['missing']) }})</h4>
                        <table class="table table-sm">
                            <thead><tr><th>Student</th><th>Module</th><th>Year</th><th>Semester</th></tr></thead>
                            <tbody>
                                @foreach($results['missing'] as $item)
                                <tr>
                                    <td>{{ $item->student_id }}</td>
                                    <td>{{ $item->module_id }}</td>
                                    <td>{{ $item->academic_year_id }}</td>
                                    <td>{{ $item->semester }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(isset($results['stuck']) && $type == 'stuck')
                        <h4>Stuck Workflows ({{ count($results['stuck']) }})</h4>
                        <table class="table table-sm">
                            <thead><tr><th>Student</th><th>Module</th><th>Status</th><th>Updated</th></tr></thead>
                            <tbody>
                                @foreach($results['stuck'] as $result)
                                <tr>
                                    <td>{{ $result->student->user->full_name ?? 'N/A' }}</td>
                                    <td>{{ $result->module->code }}</td>
                                    <td>{{ $result->status }}</td>
                                    <td>{{ $result->updated_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(isset($results['orphaned']) && $type == 'orphaned')
                        <h4>Orphaned Versions ({{ count($results['orphaned']) }})</h4>
                        <table class="table table-sm">
                            <thead><tr><th>Student</th><th>Module</th><th>Year/Sem</th><th>Version</th><th>Current</th></tr></thead>
                            <tbody>
                                @foreach($results['orphaned'] as $result)
                                <tr>
                                    <td>{{ $result->student_id }}</td>
                                    <td>{{ $result->module_id }}</td>
                                    <td>{{ $result->academic_year_id }}/{{ $result->semester }}</td>
                                    <td>{{ $result->version }}</td>
                                    <td>{{ $result->is_current ? 'Yes' : 'No' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(isset($results['grade_mismatch']) && $type == 'grade_mismatch')
                        <h4>Grade Mismatches ({{ count($results['grade_mismatch']) }})</h4>
                        <table class="table table-sm">
                            <thead><tr><th>Student</th><th>Module</th><th>Final Score</th><th>Current Grade</th><th>Correct Grade</th></tr></thead>
                            <tbody>
                                @foreach($results['grade_mismatch'] as $result)
                                <tr>
                                    <td>{{ $result->student->user->full_name ?? 'N/A' }}</td>
                                    <td>{{ $result->module->code }}</td>
                                    <td>{{ $result->final_score }}</td>
                                    <td>{{ $result->grade }}</td>
                                    <td>{{ $result->gradingSystem->grade ?? 'Unknown' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection