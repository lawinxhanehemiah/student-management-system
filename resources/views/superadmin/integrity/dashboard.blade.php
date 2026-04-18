@extends('layouts.superadmin')

@section('title', 'Integrity Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Missing Results -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['missing_results'] }}</h3>
                    <p>Missing Results</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('superadmin.integrity.run-checks', ['type' => 'missing']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Stuck Workflows -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['stuck_workflows'] }}</h3>
                    <p>Stuck Workflows</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <a href="{{ route('superadmin.integrity.run-checks', ['type' => 'stuck']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Orphaned Versions -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['orphaned_versions'] }}</h3>
                    <p>Orphaned Versions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-code-branch"></i>
                </div>
                <a href="{{ route('superadmin.integrity.run-checks', ['type' => 'orphaned']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Grade Mismatch -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $summary['grade_mismatch'] }}</h3>
                    <p>Grade Mismatch</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('superadmin.integrity.run-checks', ['type' => 'grade_mismatch']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Repair Actions</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('superadmin.integrity.repair') }}">
                        @csrf
                        <div class="btn-group flex-wrap gap-2">
                            <button type="submit" name="action" value="fix-missing" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Fix Missing Results
                            </button>
                            <button type="submit" name="action" value="fix-stuck" class="btn btn-warning">
                                <i class="fas fa-forward"></i> Fix Stuck Workflows
                            </button>
                            <button type="submit" name="action" value="fix-orphaned" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Fix Orphaned Versions
                            </button>
                            <button type="submit" name="action" value="fix-grade-mismatch" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Fix Grade Mismatches
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Integrity Logs</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('superadmin.integrity.logs') }}" class="btn btn-info">
                        <i class="fas fa-history"></i> View Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection