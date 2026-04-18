{{-- resources/views/admission/applications/drafts.blade.php --}}
@extends('layouts.admission')

@section('title', 'Draft Applications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Draft Applications</h3>
                    <div class="card-tools">
                        <a href="{{ route('admission.officer.applications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Application
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="academic_year" class="form-control">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ request('academic_year') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email or application number..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Applications Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>App No</th>
                                    <th>Applicant</th>
                                    <th>Academic Year</th>
                                    <th>Intake</th>
                                    <th>Progress</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                <tr>
                                    <td>{{ $app->application_number }}</td>
                                    <td>
                                        <strong>{{ $app->applicant_name }}</strong><br>
                                        <small>{{ $app->applicant_email }}</small>
                                    </td>
                                    <td>{{ $app->academic_year_name }}</td>
                                    <td>{{ $app->intake }}</td>
                                    <td>
                                        @php
                                            $completed = 0;
                                            if($app->step_basic_completed) $completed++;
                                            if($app->step_personal_completed) $completed++;
                                            if($app->step_contact_completed) $completed++;
                                            if($app->step_next_of_kin_completed) $completed++;
                                            if($app->step_academic_completed) $completed++;
                                            if($app->step_programs_completed) $completed++;
                                            $percent = round(($completed / 6) * 100);
                                        @endphp
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percent }}%">{{ $percent }}%</div>
                                        </div>
                                        <small>{{ $completed }}/6 steps</small>
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($app->created_at)) }}</td>
                                    <td>
                                        <a href="{{ route('admission.officer.applications.edit', $app->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $app->id }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No draft applications found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $applications->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admission-officer/applications/' + id;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection