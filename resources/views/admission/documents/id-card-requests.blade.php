@extends('layouts.admission')

@section('title', 'ID Card Requests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">ID Card Requests</h4>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="small-box bg-info">
                                <div class="inner"><h3>{{ $statistics['total'] }}</h3><p>Total</p></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-warning">
                                <div class="inner"><h3>{{ $statistics['pending'] }}</h3><p>Pending</p></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-primary">
                                <div class="inner"><h3>{{ $statistics['processing'] }}</h3><p>Processing</p></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-success">
                                <div class="inner"><h3>{{ $statistics['completed'] }}</h3><p>Completed</p></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-danger">
                                <div class="inner"><h3>{{ $statistics['rejected'] }}</h3><p>Rejected</p></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-5">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Reg No, Name..." value="{{ $search }}">
                            </div>
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Request #</th>
                                    <th>Reg No.</th>
                                    <th>Student Name</th>
                                    <th>Programme</th>
                                    <th>Study Mode</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>{{ $request->request_number }}</td>
                                        <td>{{ $request->registration_number }}</td>
                                        <td>{{ $request->first_name }} {{ $request->middle_name }} {{ $request->last_name }}</td>
                                        <td>{{ $request->programme_name }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->study_mode)) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $statusColors = ['pending' => 'warning', 'processing' => 'primary', 'completed' => 'success', 'rejected' => 'danger'];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$request->status] }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary update-status" 
                                                    data-id="{{ $request->id }}" 
                                                    data-status="{{ $request->status }}"
                                                    data-notes="{{ $request->notes }}">
                                                <i class="feather-edit"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No ID card requests found.</td>
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
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update ID Card Request Status</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="updateStatusForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="request_id">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.update-status').on('click', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var notes = $(this).data('notes');
        
        $('#request_id').val(id);
        $('select[name="status"]').val(status);
        $('textarea[name="notes"]').val(notes);
        $('#updateStatusModal').modal('show');
    });
    
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#request_id').val();
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ url("admission/documents/id-card-requests") }}/' + id,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Failed to update status');
            }
        });
    });
});
</script>
@endsection