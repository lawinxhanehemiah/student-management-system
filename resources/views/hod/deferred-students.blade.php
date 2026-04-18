@extends('layouts.hod')

@section('title', 'Deferred Students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="feather-pause-circle"></i> Deferred/Inactive Students - {{ $programme->name ?? 'Programme' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">{{ $students->total() }} Deferred Students</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        These students are currently inactive/deferred. They cannot register for courses or sit for exams.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search by name or reg no" 
                                           value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('hod.export.students') }}?status=inactive" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Export List
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                抱
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Year</th>
                                    <th>Deferred Since</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr class="table-warning">
                                    <td><strong>{{ $student->registration_number }}</strong></td>
                                    <td>
                                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                                        <br>
                                        <small class="text-muted">{{ $student->user->email ?? 'No email' }}</small>
                                    </td>
                                    <td>Year {{ $student->current_level }}</td>
                                    <td>
                                        {{ $student->updated_at->format('d M Y') }}
                                        <br>
                                        <small>{{ $student->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">Not specified</span>
                                    </td>
                                    <td>
                                        {{-- FIXED: Pass the ID correctly in the onclick function --}}
                                        <button class="btn btn-sm btn-success" onclick="activateStudent({{ $student->id }})">
                                            <i class="fas fa-play"></i> Activate
                                        </button>
                                        <a href="{{ route('hod.students.profile', $student->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> No deferred students found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function activateStudent(id) {
    Swal.fire({
        title: 'Activate Student?',
        text: "Are you sure you want to activate this student? They will be able to register for courses and sit for exams.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, activate student!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we activate the student',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // FIXED: Use the correct URL with the ID parameter
            $.ajax({
                url: '{{ url("hod/students/activate") }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Student activated successfully',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to activate student',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to activate student';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        }
    });
}
</script>
@endpush
@endsection