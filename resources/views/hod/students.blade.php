@extends('layouts.hod')

@section('title', 'All Students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="feather-users"></i> All Students - {{ $programme->name ?? 'Programme' }}
                    </h3>
                    <div class="card-tools">
                        <form method="GET" class="form-inline">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name or reg no" 
                                       value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <form method="GET" class="form-inline">
                                <select name="level" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Levels</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                            Year {{ $level }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" class="form-inline">
                                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                @if(request('level'))
                                    <input type="hidden" name="level" value="{{ request('level') }}">
                                @endif
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('hod.export.students') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Reg No</th>
                                    <th>Full Name</th>
                                    <th>Programme</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $index => $student)
                                <tr>
                                    <td>{{ $students->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $student->registration_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $student->user->first_name ?? '' }} 
                                        {{ $student->user->middle_name ?? '' }} 
                                        {{ $student->user->last_name ?? '' }}
                                        <br>
                                        <small class="text-muted">{{ $student->user->email ?? 'No email' }}</small>
                                    </td>
                                    <td>{{ $student->programme->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">Year {{ $student->current_level }}</span>
                                        <br>
                                        <small>Semester {{ $student->current_semester }}</small>
                                    </td>
                                    <td>
                                        @if($student->status == 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($student->status == 'graduated')
                                            <span class="badge badge-primary">Graduated</span>
                                        @elseif($student->status == 'suspended')
                                            <span class="badge badge-danger">Suspended</span>
                                        @else
                                            <span class="badge badge-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hod.students.profile', $student->id) }}" 
                                               class="btn btn-info" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('hod.students.academic-history', $student->id) }}" 
                                               class="btn btn-primary" title="Academic History">
                                                <i class="fas fa-history"></i>
                                            </a>
                                            <a href="{{ route('hod.students.register-courses', $student->id) }}" 
                                               class="btn btn-success" title="Register Courses">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('hod.students.clearance', $student->id) }}" 
                                               class="btn btn-warning" title="Clearance Status">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> No students found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $students->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection