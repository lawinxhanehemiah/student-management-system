@extends('layouts.admission')

@section('title', 'Intake Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="feather-calendar text-primary"></i> Intake Management
                    </h4>
                    <a href="{{ route('admission.intakes.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather-plus"></i> Create New Intake
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="feather-info"></i> 
                        <strong>Intake Management</strong> - Manage admission cycles (March, September, etc.) and assign programmes to each intake.
                    </div>
                    
                    <!-- Intakes Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Intake Name</th>
                                    <th>Code</th>
                                    <th>Academic Year</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Application Deadline</th>
                                    <th>Status</th>
                                    <th>Programmes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($intakes as $intake)
                                    <tr>
                                        <td>
                                            <strong>{{ $intake->name }}</strong>
                                        </td
                                        <td>{{ $intake->code }}</td
                                        <td>{{ $intake->academic_year_name ?? 'N/A' }}</td
                                        <td>{{ \Carbon\Carbon::parse($intake->start_date)->format('d/m/Y') }}</td
                                        <td>{{ \Carbon\Carbon::parse($intake->end_date)->format('d/m/Y') }}</td
                                        <td>
                                            {{ \Carbon\Carbon::parse($intake->application_deadline)->format('d/m/Y') }}
                                            @if($intake->application_deadline < now())
                                                <br><span class="badge bg-danger">Passed</span>
                                            @endif
                                         </td
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'upcoming' => 'secondary',
                                                    'open' => 'success',
                                                    'closed' => 'danger',
                                                    'completed' => 'info'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$intake->status] ?? 'secondary' }}">
                                                {{ ucfirst($intake->status) }}
                                            </span>
                                         </td
                                        <td>
                                            <span class="badge bg-primary">{{ DB::table('intake_programmes')->where('intake_id', $intake->id)->count() }} programmes</span>
                                         </td
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admission.intakes.show', $intake->id) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="feather-eye"></i>
                                                </a>
                                                <a href="{{ route('admission.intakes.edit', $intake->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="feather-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-intake" data-id="{{ $intake->id }}" title="Delete">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </div>
                                         </td
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="feather-calendar fa-3x text-muted"></i>
                                            <p class="mt-2">No intakes found.</p>
                                            <a href="{{ route('admission.intakes.create') }}" class="btn btn-primary btn-sm">
                                                Create First Intake
                                            </a>
                                        </td
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $intakes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete Intake
    $('.delete-intake').on('click', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this intake? This will also remove all programme assignments.')) {
            $.ajax({
                url: '{{ url("admission/intakes") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Failed to delete intake';
                    toastr.error(msg);
                }
            });
        }
    });
});
</script>
@endsection