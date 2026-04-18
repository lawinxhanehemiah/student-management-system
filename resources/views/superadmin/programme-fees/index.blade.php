@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-dollar-sign text-primary"></i> 
            Fees for: {{ $programme->name }} ({{ $programme->code }})
        </h1>
        <div>
            <a href="{{ route('superadmin.programmes.index') }}" class="btn btn-secondary">
                <i class="feather-arrow-left"></i> Back to Programmes
            </a>
            <a href="{{ route('superadmin.programmes.fees.create', $programme->id) }}" 
               class="btn btn-primary">
                <i class="feather-plus-circle"></i> Add Fee
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="feather-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="feather-alert-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="level" class="form-control">
                        <option value="">All Levels</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                Level {{ $level }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="is_active" class="form-control">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fees Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Fees List ({{ $fees->total() }})
            </h6>
            @if($fees->count() > 0)
                <span class="text-primary">
                    Total Amount: <strong>{{ number_format($fees->sum('total_fee')) }}/=</strong>
                </span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Level</th>
                            <th>Registration Fee</th>
                            <th>Semester 1</th>
                            <th>Semester 2</th>
                            <th>Total Year</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fees as $fee)
                        <tr>
                            <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">Level {{ $fee->level }}</span>
                            </td>
                            <td class="text-end">{{ number_format($fee->registration_fee) }}/=</td>
                            <td class="text-end">{{ number_format($fee->semester_1_fee) }}/=</td>
                            <td class="text-end">{{ number_format($fee->semester_2_fee) }}/=</td>
                            <td class="text-end text-primary">
                                <strong>{{ number_format($fee->total_year_fee) }}/=</strong>
                            </td>
                            <td class="text-end text-success">
                                <strong>{{ number_format($fee->total_fee) }}/=</strong>
                            </td>
                            <td>
                                @if($fee->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('superadmin.programmes.fees.edit', ['programme' => $programme->id, 'fee' => $fee->id]) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="feather-edit"></i>
                                    </a>
                                    <form action="{{ route('superadmin.programmes.fees.destroy', ['programme' => $programme->id, 'fee' => $fee->id]) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this fee?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="feather-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="feather-dollar-sign text-muted" style="font-size: 48px;"></i>
                                <h5 class="mt-3 text-muted">No fees found for this programme</h5>
                                <a href="{{ route('superadmin.programmes.fees.create', $programme->id) }}" 
                                   class="btn btn-primary mt-2">
                                    <i class="feather-plus-circle"></i> Add First Fee
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($fees->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $fees->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection