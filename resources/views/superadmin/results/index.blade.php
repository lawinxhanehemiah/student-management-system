@extends('layouts.superadmin')

@section('title', 'Results')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Results</h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.results.import-ministry') }}" class="btn btn-info btn-sm">Import Ministry Results</a>
                        <a href="{{ route('superadmin.results.export', request()->query()) }}" class="btn btn-secondary btn-sm">Export CSV</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3 row">
                        <div class="col-md-3">
                            <select name="academic_year" class="form-control">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ request('academic_year') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="module" class="form-control">
                                <option value="">All Modules</option>
                                @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ request('module') == $module->id ? 'selected' : '' }}>{{ $module->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending_hod" {{ request('status') == 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                                <option value="pending_academic" {{ request('status') == 'pending_academic' ? 'selected' : '' }}>Pending Academic</option>
                                <option value="pending_principal" {{ request('status') == 'pending_principal' ? 'selected' : '' }}>Pending Principal</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('superadmin.results.bulk-update') }}" id="bulkForm">
                        @csrf
                        <div class="mb-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary" id="selectAll">Select All</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">Deselect All</button>
                            </div>
                            <select name="status" class="form-control-sm">
                                <option value="">Bulk Update Status</option>
                                <option value="draft">Draft</option>
                                <option value="pending_hod">Pending HOD</option>
                                <option value="pending_academic">Pending Academic</option>
                                <option value="pending_principal">Pending Principal</option>
                                <option value="published">Published</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" formaction="{{ route('superadmin.results.bulk-update') }}">Update Selected</button>
                            <button type="submit" class="btn btn-sm btn-danger" formaction="{{ route('superadmin.results.bulk-delete') }}" onclick="return confirm('Delete selected results?')">Delete Selected</button>
                            <button type="submit" class="btn btn-sm btn-success" formaction="{{ route('superadmin.results.bulk-approve') }}" onclick="return confirm('Approve selected results?')">Approve Selected</button>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead>
                                32<th><input type="checkbox" id="masterCheckbox"></th>
                                    <th>Student</th>
                                    <th>Module</th>
                                    <th>CA Score</th>
                                    <th>Exam Score</th>
                                    <th>Final Score</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $result)
                                <tr>
                                    <td><input type="checkbox" name="result_ids[]" value="{{ $result->id }}" class="result-checkbox"></td>
                                    <td>{{ $result->student->user->full_name ?? 'N/A' }} ({{ $result->student->registration_number ?? '' }})</td>
                                    <td>{{ $result->module->code }}</td>
                                    <td>{{ $result->ca_score ?? '-' }}</td>
                                    <td>{{ $result->exam_score ?? '-' }}</td>
                                    <td>{{ $result->final_score ?? ($result->ca_score && $result->exam_score ? $result->ca_score * 0.4 + $result->exam_score * 0.6 : '-') }}</td>
                                    <td>{{ $result->grade ?? '-' }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($result->status == 'published') bg-success
                                            @elseif(in_array($result->status, ['pending_hod','pending_academic','pending_principal'])) bg-warning
                                            @elseif($result->status == 'rejected') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            {{ str_replace('_', ' ', $result->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $result->source }}</td>
                                    <td>
                                        <a href="{{ route('superadmin.results.show', $result) }}" class="btn btn-info btn-sm">View</a>
                                        <form action="{{ route('superadmin.results.destroy', $result) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this result?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="10" class="text-center">No results found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </form>
                    {{ $results->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('masterCheckbox').addEventListener('change', function(e) {
        let checkboxes = document.querySelectorAll('.result-checkbox');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.result-checkbox').forEach(cb => cb.checked = true);
        document.getElementById('masterCheckbox').checked = true;
    });
    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.result-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('masterCheckbox').checked = false;
    });
</script>
@endpush