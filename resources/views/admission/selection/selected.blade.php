@extends('layouts.admission')

@section('title', 'Selected Students')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Selected Students</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.selection.export-ranking', ['intake' => $selectedIntake, 'status' => 'selected']) }}" class="btn btn-info btn-sm">
                            <i class="feather-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="feather-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Selected</span>
                                    <span class="info-box-number">{{ $statistics['total_selected'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="feather-mail"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Letters Sent</span>
                                    <span class="info-box-number">{{ $statistics['letters_sent'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="feather-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Letters Pending</span>
                                    <span class="info-box-number">{{ $statistics['letters_pending'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Programme</label>
                                <select name="programme_id" class="form-control">
                                    <option value="">All Programmes</option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}" {{ $selectedProgrammeId == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Intake</label>
                                <select name="intake" class="form-control">
                                    <option value="March" {{ $selectedIntake == 'March' ? 'selected' : '' }}>March</option>
                                    <option value="September" {{ $selectedIntake == 'September' ? 'selected' : '' }}>September</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, App No, Phone..." value="{{ $search }}">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>App No.</th>
                                    <th>Full Name</th>
                                    <th>Contact</th>
                                    <th>Programme</th>
                                    <th>CSEE</th>
                                    <th>Status</th>
                                    <th>Letter Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedStudents as $student)
                                    <tr>
                                        <td>{{ $student->application_number }}</td>
                                        <td>
                                            <strong>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</strong>
                                        </td>
                                        <td>
                                            {{ $student->phone_number }}<br>
                                            <small>{{ $student->email }}</small>  <!-- FIXED: changed from user_email to email -->
                                        </td>
                                        <td>{{ $student->programme_name }}</td>
                                        <td>
                                            {{ $student->csee_points ?? 'N/A' }} pts<br>
                                            Div {{ $student->csee_division ?? 'N/A' }}
                                         </td>
                                        <td>
                                            @if($student->status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($student->status == 'registered')
                                                <span class="badge badge-primary">Registered</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $student->status }}</span>
                                            @endif
                                         </td>
                                        <td>
                                            @if($student->admission_letter_sent_at)
                                                <span class="badge badge-success">Sent</span><br>
                                                <small>{{ \Carbon\Carbon::parse($student->admission_letter_sent_at)->format('d/m/Y') }}</small>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                         </td>
                                        <td>
                                            <a href="{{ route('admission.applicants.show', $student->id) }}" class="btn btn-sm btn-info">
                                                <i class="feather-eye"></i>
                                            </a>
                                            @if(!$student->admission_letter_sent_at)
                                                <button class="btn btn-sm btn-primary send-letter" data-id="{{ $student->id }}">
                                                    <i class="feather-mail"></i> Send Letter
                                                </button>
                                            @endif
                                         </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No selected students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $selectedStudents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection