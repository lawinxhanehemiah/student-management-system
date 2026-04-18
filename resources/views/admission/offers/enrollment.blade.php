@extends('layouts.admission')

@section('title', 'Enrollment Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Student Enrollment</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="bulkEnrollBtn">
                            <i class="feather-user-plus"></i> Bulk Enroll
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Admitted</span>
                                    <span class="info-box-number">{{ $statistics['total_admitted'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="feather-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Enrolled Students</span>
                                    <span class="info-box-number">{{ $statistics['enrolled'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="feather-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending Enrollment</span>
                                    <span class="info-box-number">{{ $statistics['pending_enrollment'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <form id="bulkEnrollForm" method="POST" action="{{ route('admission.offers.enrollment.bulk') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30"><input type="checkbox" id="selectAll"></th>
                                        <th>App No.</th>
                                        <th>Full Name</th>
                                        <th>Programme</th>
                                        <th>Intake</th>
                                        <th>Admission Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                        <tr>
                                            <td><input type="checkbox" name="application_ids[]" value="{{ $app->id }}" class="enroll-checkbox"></td>
                                            <td>{{ $app->application_number }}</td>
                                            <td>
                                                <strong>{{ $app->first_name }} {{ $app->middle_name }} {{ $app->last_name }}</strong>
                                            </td>
                                            <td>{{ $app->programme_name }}</td>
                                            <td>{{ $app->intake }}</td>
                                            <td>{{ \Carbon\Carbon::parse($app->admission_date)->format('d/m/Y') }}</td>
                                            <td>
                                                @if($app->student_id)
                                                    <span class="badge badge-success">Enrolled</span><br>
                                                    <small>Reg: {{ $app->registration_number }}</small>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                             </td>
                                            <td>
                                                @if(!$app->student_id)
                                                    <button type="button" class="btn btn-sm btn-success enroll-single" data-id="{{ $app->id }}">
                                                        <i class="feather-user-plus"></i> Enroll
                                                    </button>
                                                @else
                                                    <a href="{{ route('admission.students.show', $app->student_id) }}" class="btn btn-sm btn-info">
                                                        <i class="feather-eye"></i> View
                                                    </a>
                                                @endif
                                             </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No admitted students pending enrollment.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="bulkEnrollSubmitBtn">
                                    <i class="feather-user-plus"></i> Enroll Selected
                                </button>
                            </div>
                        </div>
                    </form>

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
    // Select All
    $('#selectAll').on('change', function() {
        $('.enroll-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Single Enroll
    $('.enroll-single').on('click', function() {
        var id = $(this).data('id');
        var btn = $(this);
        
        if (confirm('Enroll this student? A registration number will be generated automatically.')) {
            btn.prop('disabled', true).html('<i class="feather-loader fa-spin"></i>');
            
            $.ajax({
                url: '{{ url("admission/offers/enrollment/mark") }}/' + id,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).html('<i class="feather-user-plus"></i> Enroll');
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Failed to enroll student';
                    toastr.error(errorMsg);
                    btn.prop('disabled', false).html('<i class="feather-user-plus"></i> Enroll');
                }
            });
        }
    });
    
    // Bulk Enroll
    $('#bulkEnrollBtn').on('click', function() {
        var checked = $('.enroll-checkbox:checked').length;
        if (checked === 0) {
            toastr.warning('Please select at least one student to enroll.');
            return;
        }
        
        if (confirm('Enroll ' + checked + ' selected student(s)? Registration numbers will be generated automatically.')) {
            $('#bulkEnrollForm').submit();
        }
    });
});
</script>
@endsection