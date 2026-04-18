@extends('layouts.hod')

@section('title', 'Active Students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="feather-user-check"></i> Active Students - {{ $programme->name ?? 'Programme' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">{{ $students->total() }} Active Students</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" class="form-inline">
                                <select name="level" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Years</option>
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
                        <div class="col-md-5 text-right">
                            <a href="{{ route('hod.export.students') }}?status=active" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        @foreach($levels as $level)
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $students->where('current_level', $level)->count() }}</h3>
                                        <p>Year {{ $level }} Students</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Year</th>
                                    <th>Study Mode</th>
                                    <th>Guardian</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td><strong>{{ $student->registration_number }}</strong></td>
                                    <td>
                                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                                        <br>
                                        <small class="text-muted">{{ $student->user->email ?? 'No email' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">Year {{ $student->current_level }}</span>
                                        <br>
                                        <small>Semester {{ $student->current_semester }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $student->study_mode)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-user"></i> {{ $student->guardian_name ?? 'N/A' }}
                                        <br>
                                        <i class="fas fa-phone"></i> {{ $student->guardian_phone ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hod.students.profile', $student->id) }}" 
                                               class="btn btn-info" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('hod.students.register-courses', $student->id) }}" 
                                               class="btn btn-success" title="Register Courses">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No active students found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $students->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection