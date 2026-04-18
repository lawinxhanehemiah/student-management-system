@extends('layouts.tutor')

@section('title', 'Enter Results')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Enter Results for {{ $module->code }} - {{ $module->name }}
                <br><small>Academic Year: {{ $academicYearId }} | Semester: {{ $semester }}</small>
            </h3>
            <div class="card-tools">
                <a href="{{ route('lecturer.results.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lecturer.results.store', [$module, $academicYearId, $semester]) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>CA Score (0-100)</th>
                                <th>Exam Score (0-100)</th>
                                <th>Total (auto)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->user->full_name ?? $student->id }}</td>
                                    <td>
                                        <input type="number" name="scores[{{ $student->id }}]" class="form-control score-ca" step="any" min="0" max="100" value="{{ old('scores.'.$student->id) }}">
                                    </td>
                                    <td>
                                        <input type="number" name="scores[{{ $student->id }}]" class="form-control score-exam" step="any" min="0" max="100" value="{{ old('scores.'.$student->id) }}">
                                    </td>
                                    <td class="total-score">-</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save as Draft</button>
                    <button type="submit" name="submit_to_hod" value="1" class="btn btn-success">Save & Submit to HOD</button>
                    <a href="{{ route('lecturer.results.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple auto-calculate total (CA + Exam) per row
    document.querySelectorAll('.score-ca, .score-exam').forEach(input => {
        input.addEventListener('input', function() {
            let row = this.closest('tr');
            let ca = parseFloat(row.querySelector('.score-ca').value) || 0;
            let exam = parseFloat(row.querySelector('.score-exam').value) || 0;
            let total = ca + exam;
            row.querySelector('.total-score').innerText = total.toFixed(2);
        });
    });
</script>
@endpush