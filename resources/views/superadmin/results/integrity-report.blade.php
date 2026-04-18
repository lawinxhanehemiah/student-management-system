@extends('layouts.superadmin')

@section('title', 'Integrity Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $missingResults }}</h3>
                    <p>Missing Results</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('superadmin.results.missing-results') }}" class="small-box-footer">View details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stuckWorkflows }}</h3>
                    <p>Stuck Workflows (>5 days)</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <a href="{{ route('superadmin.results.stuck-workflows') }}" class="small-box-footer">View details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $orphanedVersions }}</h3>
                    <p>Orphaned Versions</p>
                </div>
                <div class="icon"><i class="fas fa-code-branch"></i></div>
                <a href="{{ route('superadmin.results.integrity-report') }}" class="small-box-footer">Fix needed <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Integrity Fixes</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('superadmin.results.integrity-fix') }}">
                        @csrf
                        <div class="btn-group">
                            <button type="submit" name="action" value="fix-missing" class="btn btn-primary">Fix Missing Results</button>
                            <button type="submit" name="action" value="fix-stuck" class="btn btn-warning">Fix Stuck Workflows</button>
                            <button type="submit" name="action" value="fix-orphaned" class="btn btn-danger">Fix Orphaned Versions</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection