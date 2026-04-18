@extends('layouts.superadmin')

@section('title', 'Stuck Workflows')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Stuck Workflows (Pending >5 days)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            32<th>Student</th>
                                <th>Module</th>
                                <th>Status</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stuck as $result)
                            <tr>
                                <td>{{ $result->student->user->full_name ?? 'N/A' }} ({{ $result->student->registration_number ?? '' }})</td>
                                <td>{{ $result->module->code }}</td>
                                <td>{{ $result->status }}</td>
                                <td>{{ $result->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('superadmin.approvals.results.approve', $result) }}" class="btn btn-sm btn-primary">Force Approve</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5">No stuck workflows.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection