@extends('layouts.admission')

@section('title', 'Offer Acceptance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Offer Acceptance Tracking</h4>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="feather-mail"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Offers Sent</span>
                                    <span class="info-box-number">{{ $statistics['total_offers_sent'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Accepted</span>
                                    <span class="info-box-number">{{ $statistics['accepted'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="feather-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending Response</span>
                                    <span class="info-box-number">{{ $statistics['pending_response'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="feather-x-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Not Admitted</span>
                                    <span class="info-box-number">{{ $statistics['not_admitted'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>App No.</th>
                                    <th>Full Name</th>
                                    <th>Programme</th>
                                    <th>Intake</th>
                                    <th>Offer Sent Date</th>
                                    <th>Acceptance Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                    <tr>
                                        <td>{{ $app->application_number }}</td>
                                        <td>{{ $app->first_name }} {{ $app->middle_name }} {{ $app->last_name }}</td>
                                        <td>{{ $app->programme_name }}</td>
                                        <td>{{ $app->intake }}</td>
                                        <td>{{ \Carbon\Carbon::parse($app->admission_letter_sent_at)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($app->admission_status == 'admitted')
                                                <span class="badge badge-success">Admitted</span>
                                            @elseif($app->admission_status == 'not_admitted')
                                                <span class="badge badge-danger">Not Admitted</span>
                                            @elseif($app->admission_status == 'pending_payment')
                                                <span class="badge badge-warning">Pending Payment</span>
                                            @else
                                                <span class="badge badge-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select class="form-control form-control-sm status-update" data-id="{{ $app->id }}">
                                                <option value="">Update Status</option>
                                                <option value="admitted" {{ $app->admission_status == 'admitted' ? 'selected' : '' }}>Admitted</option>
                                                <option value="pending_payment" {{ $app->admission_status == 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                                                <option value="not_admitted" {{ $app->admission_status == 'not_admitted' ? 'selected' : '' }}>Not Admitted</option>
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No offer letters sent yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.status-update').on('change', function() {
        var status = $(this).val();
        var id = $(this).data('id');
        
        if (!status) return;
        
        $.ajax({
            url: '{{ url("admission/offers/acceptance/update") }}/' + id,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                admission_status: status
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
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