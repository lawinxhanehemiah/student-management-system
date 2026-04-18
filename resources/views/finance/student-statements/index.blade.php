@extends('layouts.financecontroller')

@section('title', 'Student Statements')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">Student Statements</h1>
            <div class="breadcrumb">
                <a href="{{ route('finance.dashboard') }}" class="breadcrumb-item">Finance</a>
                <a href="#" class="breadcrumb-item">Revenue Management</a>
                <span class="breadcrumb-item active">Student Statements</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Find Student Statement</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.student-statements.search') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label required">Registration Number</label>
                            <input type="text" 
                                   class="form-control @error('registration_number') is-invalid @enderror" 
                                   name="registration_number" 
                                   value="{{ old('registration_number') }}"
                                   placeholder="e.g., 2023/CS/001">
                            @error('registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Academic Year (Optional)</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-search"></i> Search Statement
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Search by Reg No (AJAX) -->
            <div class="card custom-card mt-3">
                <div class="card-header">
                    <div class="card-title">Quick Search</div>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="quickRegNo" placeholder="Enter registration number">
                        <button class="btn btn-primary" type="button" id="quickSearchBtn">
                            <i class="feather-search"></i> Go
                        </button>
                    </div>
                    <div id="quickResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#quickSearchBtn').click(function() {
        const regNo = $('#quickRegNo').val();
        
        if (!regNo) {
            alert('Please enter registration number');
            return;
        }
        
        $.ajax({
            url: '{{ route("finance.student-statements.by-reg", "") }}/' + regNo,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const student = response.student;
                    const html = `
                        <div class="alert alert-success">
                            <h6>Student Found!</h6>
                            <p><strong>Name:</strong> ${student.name}</p>
                            <p><strong>Reg No:</strong> ${student.registration_number}</p>
                            <p><strong>Programme:</strong> ${student.programme}</p>
                            <p><strong>Balance:</strong> TZS ${student.balance.toLocaleString()}</p>
                            <a href="/finance/student-statements/${student.id}" class="btn btn-sm btn-primary">
                                View Statement
                            </a>
                        </div>
                    `;
                    $('#quickResult').html(html).show();
                } else {
                    $('#quickResult').html(`
                        <div class="alert alert-danger">Student not found!</div>
                    `).show();
                }
            },
            error: function() {
                $('#quickResult').html(`
                    <div class="alert alert-danger">Error searching for student</div>
                `).show();
            }
        });
    });
});
</script>
@endpush
@endsection