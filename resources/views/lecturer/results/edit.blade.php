@extends('layouts.tutor')

@section('title', 'Edit Result')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Result for {{ $result->student->user->full_name ?? '' }}</h3>
            <div class="card-tools">
                <a href="{{ route('lecturer.results.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('lecturer.results.update', $result) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>CA Score</label>
                            <input type="number" name="ca_score" class="form-control" step="any" min="0" max="100" value="{{ $result->ca_score }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Exam Score</label>
                            <input type="number" name="exam_score" class="form-control" step="any" min="0" max="100" value="{{ $result->exam_score }}">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('lecturer.results.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection