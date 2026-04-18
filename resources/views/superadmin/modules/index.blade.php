@extends('layouts.superadmin')

@section('title', 'Manage Modules')

@section('content')
<div class="nxl-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="page-header">
        <div class="page-header-title">
            <h5>Modules</h5>
        </div>
        <div class="page-header-right">
            <a href="{{ route('superadmin.modules.create') }}" class="btn btn-primary">Add New Module</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Search & Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by code or name" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('superadmin.modules.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Modules Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Type</th>
                            <th>Default Credits</th>
                            <th>Pass Mark</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                            <tr>
                                <td>{{ $module->code }}</td>
                                <td>{{ $module->name }}</td>
                                <td>{{ $module->nta_level }}</td>
                                <td>
                                    @if($module->type == 'Core')
                                        <span class="badge bg-primary">Core</span>
                                    @elseif($module->type == 'Fundamental')
                                        <span class="badge bg-info">Fundamental</span>
                                    @else
                                        <span class="badge bg-secondary">Elective</span>
                                    @endif
                                </td>
                                <td>{{ number_format($module->default_credits, 1) }}</td>
                                <td>{{ $module->pass_mark }}%</td>
                                <td>{{ $module->department->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('superadmin.modules.edit', $module) }}" 
                                       class="btn btn-sm btn-primary">Edit</a>
                                    
                                    <form action="{{ route('superadmin.modules.destroy', $module) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete this module? This action cannot be undone.')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    No modules found. <a href="{{ route('superadmin.modules.create') }}">Create one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $modules->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection