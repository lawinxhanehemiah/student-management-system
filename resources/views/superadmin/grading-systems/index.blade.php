@extends('layouts.superadmin')

@section('title', 'Grading Systems')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grading Systems</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.grading-systems.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Min Score</th>
                                <th>Max Score</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gradingSystems as $gs)
                            <tr>
                                <td>{{ $gs->name }}</td>
                                <td>{{ $gs->min_score }}</td>
                                <td>{{ $gs->max_score }}</td>
                                <td>{{ $gs->grade }}</td>
                                <td>{{ number_format($gs->grade_point, 1) }}</td>
                                <td>{{ $gs->academicYear ? $gs->academicYear->name : 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $gs->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $gs->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('superadmin.grading-systems.edit', $gs) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('superadmin.grading-systems.destroy', $gs) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                    <a href="{{ route('superadmin.grading-systems.toggle-active', $gs) }}" class="btn btn-sm {{ $gs->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        {{ $gs->is_active ? 'Deactivate' : 'Activate' }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No grading systems found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection