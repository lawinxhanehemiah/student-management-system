@extends('layouts.app')

@section('title', 'Application Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Welcome, {{ Auth::user()->name }}!</h2>
                            <p class="mb-0">Track and manage your applications here</p>
                        </div>
                        <div>
                            @if(!$hasActiveDraft && $activeYear)
                                <a href="{{ route('application.start') }}" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>Start New Application
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Applications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $applications->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Submitted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $applications->where('status', 'submitted')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Drafts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $applications->where('status', 'draft')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Year</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeYear->name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Applications</h6>
                </div>
                <div class="card-body">
                    @if($applications->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                            <h5>No Applications Yet</h5>
                            <p class="text-muted">Start your first application to get started</p>
                            @if($activeYear)
                                <a href="{{ route('application.start') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Start Application
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered" id="applicationsTable">
                                <thead>
                                    <tr>
                                        <th>Application #</th>
                                        <th>Academic Year</th>
                                        <th>Intake</th>
                                        <th>Entry Level</th>
                                        <th>Status</th>
                                        <th>Submitted On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $app)
                                        <tr>
                                            <td>{{ $app->application_number }}</td>
                                            <td>
                                                @php
                                                    $year = DB::table('academic_years')->find($app->academic_year_id);
                                                @endphp
                                                {{ $year->name ?? 'N/A' }}
                                            </td>
                                            <td>{{ $app->intake }}</td>
                                            <td>{{ $app->entry_level }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = [
                                                        'draft' => 'warning',
                                                        'submitted' => 'primary',
                                                        'under_review' => 'info',
                                                        'accepted' => 'success',
                                                        'rejected' => 'danger'
                                                    ][$app->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($app->submitted_at)
                                                    {{ \Carbon\Carbon::parse($app->submitted_at)->format('d/m/Y') }}
                                                @else
                                                    <span class="text-muted">Not submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('application.view', $app->id) }}" 
                                                       class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($app->status == 'draft')
                                                        <a href="{{ route('application.start') }}" 
                                                           class="btn btn-warning" title="Continue">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('application.cancel', $app->id) }}" 
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" 
                                                                    title="Cancel" 
                                                                    onclick="return confirm('Cancel this application?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($app->status == 'submitted')
                                                        <a href="{{ route('application.download', $app->id) }}" 
                                                           class="btn btn-success" title="Download PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($hasActiveDraft)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><i class="fas fa-exclamation-triangle text-warning me-2"></i>You have a draft application</h5>
                                <p class="mb-0">Continue where you left off to complete your application</p>
                            </div>
                            <div>
                                <a href="{{ route('application.start') }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Continue Draft
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#applicationsTable').DataTable({
        "order": [[5, "desc"]],
        "responsive": true
    });
});
</script>
@endpush