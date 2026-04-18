@extends('layouts.superadmin')

@section('title', 'Module Registration')

@section('content')
<div class="nxl-content">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="page-header">
        <div class="page-header-title">
            <h5>Module Registration</h5>
            <small>Assign modules to programmes, years, semesters, and academic years</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label>Programme</label>
                    <select name="programme_id" class="form-control" required>
                        <option value="">Select Programme</option>
                        @foreach($programmes as $id => $name)
                            <option value="{{ $id }}" {{ request('programme_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Year</label>
                    <select name="year" class="form-control" required>
                        <option value="">Select Year</option>
                        @for($i=1; $i<=6; $i++)
                            <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Semester</label>
                    <select name="semester" class="form-control" required>
                        <option value="">Select Semester</option>
                        @for($i=1; $i<=2; $i++)
                            <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Academic Year</label>
                    <select name="academic_year" class="form-control" required>
                        <option value="">Select Academic Year</option>
                        @php
                            $currentYear = date('Y');
                        @endphp
                        @for($i=$currentYear-2; $i<=$currentYear+2; $i++)
                            <option value="{{ $i.'/'.($i+1) }}" {{ request('academic_year') == $i.'/'.($i+1) ? 'selected' : '' }}>{{ $i.'/'.($i+1) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Load</button>
                </div>
            </form>
        </div>
    </div>

    @if($programmeId && $year && $semester && $academicYear)
    <div class="card">
        <div class="card-header">
            <h6>Assigned Modules for {{ $programmes[$programmeId] ?? '' }} - Year {{ $year }}, Semester {{ $semester }}, Academic Year {{ $academicYear }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.curriculum.store') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="programme_id" value="{{ $programmeId }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <div class="row">
                    <div class="col-md-4">
                        <select name="module_id" class="form-control @error('module_id') is-invalid @enderror" required>
                            <option value="">Select Module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                    {{ $module->code }} - {{ $module->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('module_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.5" name="credits" class="form-control @error('credits') is-invalid @enderror" placeholder="Credits (optional)" value="{{ old('credits') }}">
                        @error('credits')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 form-check mt-2">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input" id="isRequired" {{ old('is_required', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isRequired">Required</label>
                    </div>
                    <div class="col-md-2">
                        <select name="grading_type" class="form-control @error('grading_type') is-invalid @enderror">
                            <option value="marks" {{ old('grading_type') == 'marks' ? 'selected' : '' }}>Marks</option>
                            <option value="cbet" {{ old('grading_type') == 'cbet' ? 'selected' : '' }}>CBET (C/NYC)</option>
                        </select>
                        @error('grading_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <input type="number" step="0.5" name="pass_mark" class="form-control @error('pass_mark') is-invalid @enderror" placeholder="Pass Mark (optional, uses module default)">
                        @error('pass_mark')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                         <tr>
                            <th>Module Code</th>
                            <th>Module Name</th>
                            <th>Type</th>
                            <th>Credits</th>
                            <th>Pass Mark</th>
                            <th>Grading Type</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($curriculum as $item)
                        <tr>
                            <td>{{ $item->module->code }}</td>
                            <td>{{ $item->module->name }}</td>
                            <td>{{ $item->module->type }}</td>
                            <td>{{ $item->credits }}</td>
                            <td>{{ $item->pass_mark ?? $item->module->pass_mark }}%</td>
                            <td>{{ ucfirst($item->grading_type) }}</td>
                            <td>{{ $item->is_required ? 'Yes' : 'No' }}</td>
                            <td>
                                <span class="badge {{ $item->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('superadmin.curriculum.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this module from curriculum?')">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No modules assigned yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection