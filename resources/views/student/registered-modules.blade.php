@extends('layouts.students')

@section('title', 'Registered Modules')

@section('content')
<div class="container-fluid">
    <!-- Header info -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="alert alert-info py-2 mb-0">
                <strong>Registered Modules</strong><br>
                Current Year: {{ $currentAcademicYear }} |
                Last Login: {{ $lastLogin->format('d M, Y H:i:s') }} |
                Today: {{ $today }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- DataTable style toolbar -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0">Show</label>
                        <select name="length" class="form-control form-control-sm w-auto" onchange="this.form.submit()" form="filterForm">
                            @foreach([10,25,50,100] as $len)
                                <option value="{{ $len }}" {{ request('length',10)==$len ? 'selected' : '' }}>{{ $len }}</option>
                            @endforeach
                        </select>
                        <span class="mb-0">entries</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <form method="GET" id="filterForm" class="d-flex justify-content-end align-items-center gap-2">
                        <label class="mb-0">Search:</label>
                        <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-primary">Go</button>
                        <a href="{{ request()->url() }}" class="btn btn-sm btn-secondary">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Table centered with auto width -->
            <div class="d-flex justify-content-center">
                <div class="table-responsive" style="max-width: 100%;">
                    <table class="table table-bordered table-striped w-auto mx-auto">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Module Name</th>
                                <th>Module Code</th>
                                <th>Module Credit</th>
                                <th>Program</th>
                                <th>Department</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($curriculum as $index => $item)
                            <tr>
                                <td class="text-center">{{ $curriculum->firstItem() + $index }}</td>
                                <td>{{ $item->module->name ?? 'N/A' }}</td>
                                <td>{{ $item->module->code ?? 'N/A' }}</td>
                                <td class="text-center">{{ $item->credits }}</td>
                                <td class="text-center">
                                    @php
                                        $progCode = $item->programme->code ?? $student->programme->code ?? null;
                                    @endphp
                                    {{ $progCode ? 'D' . $progCode : 'N/A' }}
                                </td>
                                <td>{{ $item->module->department->name ?? '' }}</td>
                                <td class="text-center">{{ $item->semester }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No modules registered for your current year.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination info & links -->
            <div class="row mt-3">
                <div class="col-md-6">
                    Showing {{ $curriculum->firstItem() ?? 0 }} to {{ $curriculum->lastItem() ?? 0 }} of {{ $curriculum->total() }} entries
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        {{ $curriculum->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection