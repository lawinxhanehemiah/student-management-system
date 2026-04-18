@extends('layouts.admission')

@section('title', 'Edit Intake - ' . $intake->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Intake: {{ $intake->name }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.intakes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admission.intakes.update', $intake->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Intake Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $intake->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-control" required>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $intake->academic_year_id == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $intake->start_date }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $intake->end_date }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Application Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="application_deadline" class="form-control" value="{{ $intake->application_deadline }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Announcement Date</label>
                                    <input type="date" name="announcement_date" class="form-control" value="{{ $intake->announcement_date }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registration Deadline</label>
                                    <input type="date" name="registration_deadline" class="form-control" value="{{ $intake->registration_deadline }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Max Applications</label>
                                    <input type="number" name="max_applications" class="form-control" value="{{ $intake->max_applications }}" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="upcoming" {{ $intake->status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="open" {{ $intake->status == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="closed" {{ $intake->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="completed" {{ $intake->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save"></i> Update Intake
                        </button>
                        <a href="{{ route('admission.intakes.show', $intake->id) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection