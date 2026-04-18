@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Adjustment Requests (Super Admin)</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#searchStudentModal">
                <i class="feather-plus-circle"></i> New Direct Adjustment
            </button>
        </div>
        <div class="card-body">
            <!-- Student Search Modal -->
            <div class="modal fade" id="searchStudentModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Student for Direct Adjustment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('superadmin.payment-adjustments.search-student') }}" method="GET">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Student Registration Number</label>
                                    <input type="text" name="registration_number" class="form-control" required 
                                           placeholder="Enter registration number">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Search & Adjust</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Approved By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>
                                <strong>{{ $req->student->user->first_name ?? '' }} {{ $req->student->user->last_name ?? '' }}</strong>
                                <br><small class="text-muted">{{ $req->student->registration_number ?? '' }}</small>
                            </td>
                            <td>{{ ucfirst($req->request_type) }}</td>
                            <td>{{ number_format($req->amount, 0) }}</td>
                            <td>
                                @if($req->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($req->status == 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($req->status == 'executed')
                                    <span class="badge bg-success">Executed</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $req->creator->name ?? $req->creator->first_name ?? '' }}</td>
                            <td>{{ $req->approver->name ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('superadmin.payment-adjustments.show', $req->id) }}" class="btn btn-info">
                                        <i class="feather-eye"></i> View
                                    </a>
                                    @if(!in_array($req->status, ['executed', 'approved']))
                                    <form action="{{ route('superadmin.payment-adjustments.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this request?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="feather-trash-2"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="feather-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">No adjustment requests found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection