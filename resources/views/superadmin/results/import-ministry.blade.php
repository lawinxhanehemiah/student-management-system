@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Import Ministry Results</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('superadmin.results.import-ministry') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Excel/CSV File *</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.csv">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Allowed file types: .xlsx, .csv</small>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
                <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection