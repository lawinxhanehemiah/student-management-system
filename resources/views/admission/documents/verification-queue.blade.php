@extends('layouts.admission')

@section('title', 'Verification Queue')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="feather-clock text-warning"></i> Verification Queue
                    </h4>
                    <div class="card-tools">
                        <span class="badge bg-warning">{{ $statistics['total_pending'] }} Pending</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="feather-info"></i> Documents pending verification. Please review each document and mark as verified or rejected.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Document Type</th>
                                    <th>Title</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td>
                                            <strong>{{ $doc->student_first_name }} {{ $doc->student_last_name }}</strong><br>
                                            <small>{{ $doc->registration_number }}</small><br>
                                            <small>{{ $doc->programme_name ?? 'N/A' }}</small>
                                         </td
                                        <td><span class="badge bg-secondary">{{ $doc->document_type_name ?? $doc->document_type }}</span></td
                                        <td>{{ $doc->title }}</td
                                        <td>{{ \Carbon\Carbon::parse($doc->created_at)->diffForHumans() }}</td
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admission.documents.download', $doc->id) }}" class="btn btn-sm btn-success">
                                                    <i class="feather-download"></i> Download
                                                </a>
                                                <button type="button" class="btn btn-sm btn-primary verify-single" 
                                                        data-id="{{ $doc->id }}" 
                                                        data-title="{{ $doc->title }}">
                                                    <i class="feather-check-circle"></i> Verify
                                                </button>
                                            </div>
                                         </td
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No documents pending verification</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection