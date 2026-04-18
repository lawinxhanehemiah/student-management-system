{{-- ============================================ --}}
{{-- SUPPLEMENTARY FEES VIEWS (EXAMS ONLY) --}}
{{-- ============================================ --}}

{{-- resources/views/superadmin/supplementary-fees/index.blade.php --}}
@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt text-warning"></i>
                        Supplementary Fees (Exams Only) - {{ $programme->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.programmes.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Programmes
                        </a>
                        <a href="{{ route('superadmin.programmes.supplementary-fees.create', $programme->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-plus"></i> Add Supplementary Fee
                        </a>
                        <a href="{{ route('superadmin.programmes.supplementary-fees.bulk-create', $programme->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-layer-group"></i> Bulk Add
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Filters --}}
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <select name="academic_year_id" class="form-control">
                                        <option value="">All Academic Years</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Level</label>
                                    <select name="level" class="form-control">
                                        <option value="">All Levels</option>
                                        @foreach($levels as $level)
                                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                                Year {{ $level }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester" class="form-control">
                                        <option value="">All Semesters</option>
                                        @foreach($semesters as $key => $semester)
                                            <option value="{{ $key }}" {{ request('semester') == $key ? 'selected' : '' }}>
                                                {{ $semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="is_active" class="form-control">
                                        <option value="">All</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('superadmin.programmes.supplementary-fees.index', $programme->id) }}" class="btn btn-default">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Copy Fees Form --}}
                    <div class="bg-light p-3 mb-4 rounded">
                        <form method="POST" action="{{ route('superadmin.programmes.supplementary-fees.copy-fees', $programme->id) }}" class="form-inline">
                            @csrf
                            <div class="row w-100">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="mr-2">Copy From:</label>
                                        <select name="from_academic_year_id" class="form-control" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="mr-2">To:</label>
                                        <select name="to_academic_year_id" class="form-control" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-info" onclick="return confirm('Copy fees to new academic year?')">
                                        <i class="fas fa-copy"></i> Copy Supplementary Fees
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Fees Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-warning">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Academic Year</th>
                                    <th>Level</th>
                                    <th>Semester</th>
                                    
                                    <th>Total Fee (TZS)</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fees as $fee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                                    <td>Year {{ $fee->level }}</td>
                                    <td>Semester {{ $fee->semester }}</td>
                                    
                                    <td class="text-right font-weight-bold">{{ number_format($fee->total_fee, 2) }}</td>
                                    <td>
                                        @if($fee->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $fee->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('superadmin.programmes.supplementary-fees.edit', [$programme->id, $fee->id]) }}" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('superadmin.programmes.supplementary-fees.destroy', [$programme->id, $fee->id]) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Delete this supplementary fee?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No supplementary fees found.</p>
                                        <a href="{{ route('superadmin.programmes.supplementary-fees.create', $programme->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-plus"></i> Add Supplementary Fee
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $fees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection