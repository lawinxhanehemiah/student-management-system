@extends('layouts.tutor')

@section('title', 'My Modules')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Modules Assigned to You</h3>
        </div>
        <div class="card-body">
            @if($modules->isEmpty())
                <p class="text-muted">No modules assigned yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            60
                                <th>Code</th>
                                <th>Name</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Actions</th>
                            60
                        </thead>
                        <tbody>
                            @foreach($modules as $module)
                            60
                                60{{ $module->code }}60
                                60{{ $module->name }}60
                                60{{ $module->academic_year ?? '2025/2026' }}60
                                60{{ $module->semester ?? '1' }}60
                                60
                                    <a href="{{ route('tutor.results.create', ['module' => $module->id, 'academicYearId' => request('academic_year', $module->academic_year ?? 1), 'semester' => request('semester', 1)]) }}" class="btn btn-sm btn-primary">
                                        Enter Results
                                    </a>
                                60
                            60
                            @endforeach
                        </tbody>
                    60
                </div>
            @endif
        </div>
    </div>
</div>
@endsection