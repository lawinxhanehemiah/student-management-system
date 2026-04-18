@extends('layouts.applicant')

@section('title', 'Admission Letter')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-envelope-open-text fa-4x text-muted mb-4"></i>
                        <h3 class="text-dark mb-3">No Admission Letter Available</h3>
                        <p class="text-muted mb-4">
                            You don't have an admission letter yet. Admission letters are 
                            sent after your application has been approved by the admission office.
                        </p>
                        
                        <!-- Check application status -->
                        @php
                            $application = DB::table('applications')
                                ->where('user_id', auth()->id())
                                ->orderBy('created_at', 'desc')
                                ->first();
                        @endphp
                        
                        @if($application)
                        <div class="alert alert-info text-start">
                            <h6 class="alert-heading">Your Application Status</h6>
                            <p class="mb-1">
                                <strong>Application No:</strong> 
                                {{ $application->application_number }}
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $application->status == 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </p>
                            <p class="mb-0">
                                <strong>Last Update:</strong> 
                                {{ \Carbon\Carbon::parse($application->updated_at)->format('M d, Y') }}
                            </p>
                        </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('applicant.dashboard') }}" 
                               class="btn btn-primary">
                                <i class="fas fa-home me-2"></i> Back to Dashboard
                            </a>
                            <a href="{{ route('applicant.application.status') }}" 
                               class="btn btn-outline-primary ms-2">
                                <i class="fas fa-clipboard-check me-2"></i> Check Application Status
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection