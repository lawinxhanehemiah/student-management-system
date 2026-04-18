@extends('layouts.superadmin')

@section('title', 'Missing Results')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Missing Results (Students registered but no result)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            32<th>Student</th>
                                <th>Module</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($missing as $m)
                            <tr>
                                <td>{{ $m->student->user->full_name ?? 'N/A' }} ({{ $m->student->registration_number ?? '' }})</td>
                                <td>{{ $m->module->code ?? 'N/A' }}</td>
                                <td>{{ $m->academicYear->name ?? 'N/A' }}</td>
                                <td>{{ $m->semester }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-success">Create Result</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5">No missing results found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection