@extends('layouts.superadmin')

@section('title', 'Result Approvals')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Approvals</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            32<th>Student</th>
                                <th>Module</th>
                                <th>Current Status</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $result)
                            <tr>
                                <td>{{ $result->student->user->full_name ?? 'N/A' }} ({{ $result->student->registration_number ?? '' }})</td>
                                <td>{{ $result->module->code }}</td>
                                <td>{{ $result->status }}</td>
                                <td>{{ $result->updated_at->diffForHumans() }}</td>
                                <td>
                                    @if($result->status == 'pending_hod')
                                        <form action="{{ route('superadmin.approvals.results.approve', $result) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $result->id }}">Reject</button>
                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $result->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('superadmin.approvals.results.reject', $result) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Result</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Remarks</label>
                                                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(in_array($result->status, ['pending_academic','pending_principal']))
                                        <form action="{{ route('superadmin.approvals.results.approve', $result) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('superadmin.results.show', $result) }}" class="btn btn-info btn-sm">View</a>
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#forcePublishModal{{ $result->id }}">Force Publish</button>
                                    <!-- Force Publish Modal -->
                                    <div class="modal fade" id="forcePublishModal{{ $result->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('superadmin.approvals.results.force-publish', $result) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Force Publish Result</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to force publish this result? This will bypass the approval workflow.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-warning">Force Publish</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5">No pending approvals.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $results->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection