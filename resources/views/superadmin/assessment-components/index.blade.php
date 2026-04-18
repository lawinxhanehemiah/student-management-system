@extends('layouts.superadmin')

@section('title', 'Assessment Components')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assessment Components</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.assessment-components.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            32<th>Module</th>
                                <th>Component Name</th>
                                <th>Weight (%)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($components as $comp)
                            <tr>
                                <td>{{ $comp->module->code }} - {{ $comp->module->name }}</td>
                                <td>{{ $comp->name }}</td>
                                <td>{{ $comp->weight }}</td>
                                <td>
                                    <span class="badge {{ $comp->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $comp->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('superadmin.assessment-components.edit', $comp) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('superadmin.assessment-components.destroy', $comp) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                    <a href="{{ route('superadmin.assessment-components.toggle-active', $comp) }}" class="btn btn-sm {{ $comp->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        {{ $comp->is_active ? 'Deactivate' : 'Activate' }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No components found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $components->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection