@extends('layouts.hod')

@section('title', 'Alumni')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="feather-award"></i> Alumni - {{ $programme->name ?? 'Programme' }}
                    </h3>
                </div>
                <div class="card-body">
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
                                <select name="graduation_year" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Years</option>
                                    @foreach($graduationYears as $year)
                                        <option value="{{ $year }}" {{ request('graduation_year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-5 text-right">
                            <a href="{{ route('hod.export.students') }}?status=graduated" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Export Alumni List
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Reg No</th>
                                    <th>Alumni Name</th>
                                    <th>Programme</th>
                                    <th>Graduation Year</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td><strong>{{ $student->registration_number }}</strong></td>
                                    <td>
                                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                                    </td>
                                    <td>{{ $student->programme->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ $student->updated_at->format('Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope"></i> {{ $student->user->email ?? 'N/A' }}
                                        <br>
                                        <i class="fas fa-phone"></i> {{ $student->user->phone ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('hod.students.profile', $student->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('hod.results.transcript', $student->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-file-pdf"></i> Transcript
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No alumni found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection