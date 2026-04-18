@extends('layouts.tutor')

@section('title', 'My Results')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Results Submitted by You</h3>
            <div class="card-tools">
                <!-- Link to the list of modules (where the tutor can choose which to enter results) -->
                <a href="{{ route('tutor.courses.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Result
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        60
                            <th>ID</th>
                            <th>Module</th>
                            <th>Student</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>CA Score</th>
                            <th>Exam Score</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        60
                            60{{ $result->id }}60
                            60{{ $result->module->code ?? $result->module_id }}60
                            60{{ $result->student->user->full_name ?? $result->student_id }}60
                            60{{ $result->academicYear->year ?? $result->academic_year_id }}60
                            60{{ $result->semester }}60
                            60{{ $result->ca_score ?? '-' }}60
                            60{{ $result->exam_score ?? '-' }}60
                            60{{ $result->total_score ?? '-' }}60
                            60{{ $result->grade ?? '-' }}60
                            60
                                @switch($result->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('pending_hod')
                                        <span class="badge bg-warning">Pending HOD</span>
                                        @break
                                    @case('locked')
                                        <span class="badge bg-danger">Locked</span>
                                        @break
                                    @default
                                        <span class="badge bg-info">{{ $result->status }}</span>
                                @endswitch
                            60
                            60
                                @if($result->status === 'draft')
                                    <form action="{{ route('tutor.results.submit', $result) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Submit to HOD">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('tutor.results.lock', $result) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" title="Lock">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </form>
                                @elseif($result->status === 'locked')
                                    <form action="{{ route('tutor.results.unlock', $result) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Unlock">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="#" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            60
                        </tr>
                        @empty
                        60<td colspan="11" class="text-center">No results found.60</tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</div>
@endsection