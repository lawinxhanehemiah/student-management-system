@extends('layouts.financecontroller')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">My Adjustment Requests</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr><th>ID</th><th>Student</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>{{ $req->student->user->first_name ?? '' }} {{ $req->student->user->last_name ?? '' }}<br><small>{{ $req->student->registration_number ?? '' }}</small></td>
                        <td>{{ ucfirst($req->request_type) }}</td>
                        <td>{{ number_format($req->amount,0) }}</td>
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
                        <td>{{ $req->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('finance.payment-adjustments.show', $req->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No requests found</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection