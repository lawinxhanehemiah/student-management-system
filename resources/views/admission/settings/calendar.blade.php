@extends('layouts.admission')

@section('title', 'Admission Calendar')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    .fc-event {
        cursor: pointer;
    }
    .calendar-sidebar .card {
        margin-bottom: 20px;
    }
    .event-list-item {
        padding: 10px;
        border-left: 3px solid;
        margin-bottom: 10px;
        background: #f8f9fa;
    }
    .event-list-item:hover {
        background: #e9ecef;
    }
    .event-badge {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 8px;
    }
    .btn-add-event {
        cursor: pointer !important;
        pointer-events: auto !important;
        z-index: 999 !important;
    }
    .modal {
        z-index: 1050 !important;
    }
    .modal-backdrop {
        z-index: 1040 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Admission Calendar</h4>
                    <button type="button" class="btn btn-primary btn-sm btn-add-event" id="addEventBtn" style="cursor: pointer;">
                        <i class="feather-plus"></i> Add Event
                    </button>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- Upcoming Events -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Upcoming Events</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($upcomingEvents as $event)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="event-badge" style="background: {{ $event->color }}"></span>
                                        <strong>{{ $event->title }}</strong>
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($event->event_date)->format('d M') }}</small>
                                </div>
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</small>
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-info edit-event" data-id="{{ $event->id }}" 
                                            data-title="{{ $event->title }}" 
                                            data-description="{{ $event->description }}"
                                            data-date="{{ $event->event_date }}"
                                            data-type="{{ $event->event_type }}"
                                            data-color="{{ $event->color }}"
                                            data-reminder="{{ $event->reminder_days }}">
                                        <i class="feather-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-event" data-id="{{ $event->id }}">
                                        <i class="feather-trash-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center">No upcoming events</div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Intake Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Intake Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admission.settings.calendar.intake-settings') }}">
                        @csrf
                        <div class="form-group">
                            <label>Current Intake</label>
                            <select name="current_intake" class="form-control">
                                <option value="March" {{ ($intakeSettings['current_intake']->value ?? 'March') == 'March' ? 'selected' : '' }}>March</option>
                                <option value="September" {{ ($intakeSettings['current_intake']->value ?? 'March') == 'September' ? 'selected' : '' }}>September</option>
                            </select>
                        </div>
                        
                        <h6 class="mt-3">March Intake</h6>
                        <div class="form-group">
                            <label>Application Deadline</label>
                            <input type="date" name="application_deadline_march" class="form-control" value="{{ $intakeSettings['application_deadline_march']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Announcement Date</label>
                            <input type="date" name="announcement_date_march" class="form-control" value="{{ $intakeSettings['announcement_date_march']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Registration Deadline</label>
                            <input type="date" name="registration_deadline_march" class="form-control" value="{{ $intakeSettings['registration_deadline_march']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Orientation Date</label>
                            <input type="date" name="orientation_date_march" class="form-control" value="{{ $intakeSettings['orientation_date_march']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Classes Start</label>
                            <input type="date" name="classes_start_march" class="form-control" value="{{ $intakeSettings['classes_start_march']->value ?? '' }}">
                        </div>
                        
                        <h6 class="mt-3">September Intake</h6>
                        <div class="form-group">
                            <label>Application Deadline</label>
                            <input type="date" name="application_deadline_september" class="form-control" value="{{ $intakeSettings['application_deadline_september']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Announcement Date</label>
                            <input type="date" name="announcement_date_september" class="form-control" value="{{ $intakeSettings['announcement_date_september']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Registration Deadline</label>
                            <input type="date" name="registration_deadline_september" class="form-control" value="{{ $intakeSettings['registration_deadline_september']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Orientation Date</label>
                            <input type="date" name="orientation_date_september" class="form-control" value="{{ $intakeSettings['orientation_date_september']->value ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Classes Start</label>
                            <input type="date" name="classes_start_september" class="form-control" value="{{ $intakeSettings['classes_start_september']->value ?? '' }}">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Add Calendar Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="eventForm" method="POST" action="{{ route('admission.settings.calendar.event.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="event_id" id="event_id">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Event Date *</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Event Type *</label>
                        <select name="event_type" id="event_type" class="form-control" required>
                            <option value="application_deadline">Application Deadline</option>
                            <option value="admission_letter">Admission Letter</option>
                            <option value="registration">Registration</option>
                            <option value="orientation">Orientation</option>
                            <option value="exam">Exam</option>
                            <option value="holiday">Holiday</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" id="color" class="form-control" value="#007bff">
                    </div>
                    <div class="form-group">
                        <label>Reminder Days Before</label>
                        <input type="number" name="reminder_days" id="reminder_days" class="form-control" min="0" max="30">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_public" id="is_public" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Public Event</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
$(document).ready(function() {
    // ============================================
    // ADD EVENT BUTTON - FIXED
    // ============================================
    $('#addEventBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Add Event button clicked'); // Check if this appears in console
        
        // Reset form
        $('#eventForm')[0].reset();
        $('#event_id').val('');
        $('#eventForm').attr('action', '{{ route("admission.settings.calendar.event.store") }}');
        $('input[name="_method"]').remove();
        $('#addEventModalLabel').text('Add Calendar Event');
        
        // Show modal
        $('#addEventModal').modal('show');
    });
    
    // ============================================
    // INITIALIZE CALENDAR
    // ============================================
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: '{{ route("admission.settings.calendar.events.json") }}',
            eventClick: function(info) {
                // Edit event on click
                $('#event_id').val(info.event.id);
                $('#title').val(info.event.title);
                $('#event_date').val(info.event.startStr);
                $('#event_type').val(info.event.extendedProps.event_type);
                $('#color').val(info.event.backgroundColor);
                $('#description').val(info.event.extendedProps.description || '');
                $('#reminder_days').val(info.event.extendedProps.reminder_days || '');
                
                $('#addEventModalLabel').text('Edit Calendar Event');
                $('#addEventModal').modal('show');
                $('#eventForm').attr('action', '{{ url("admission/settings/calendar/event") }}/' + info.event.id);
                $('input[name="_method"]').remove();
                $('#eventForm').append('<input type="hidden" name="_method" value="PUT">');
            },
            eventDidMount: function(info) {
                // Add tooltip
                $(info.el).attr('title', info.event.title);
            }
        });
        calendar.render();
    } else {
        console.error('Calendar element not found');
    }
    
    // ============================================
    // EDIT EVENT BUTTON (from sidebar)
    // ============================================
    $('.edit-event').on('click', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var description = $(this).data('description');
        var date = $(this).data('date');
        var type = $(this).data('type');
        var color = $(this).data('color');
        var reminder = $(this).data('reminder');
        
        $('#event_id').val(id);
        $('#title').val(title);
        $('#description').val(description || '');
        $('#event_date').val(date);
        $('#event_type').val(type);
        $('#color').val(color);
        $('#reminder_days').val(reminder);
        
        $('#addEventModalLabel').text('Edit Calendar Event');
        $('#addEventModal').modal('show');
        $('#eventForm').attr('action', '{{ url("admission/settings/calendar/event") }}/' + id);
        $('input[name="_method"]').remove();
        $('#eventForm').append('<input type="hidden" name="_method" value="PUT">');
    });
    
    // ============================================
    // DELETE EVENT
    // ============================================
    $('.delete-event').on('click', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this event?')) {
            $.ajax({
                url: '{{ url("admission/settings/calendar/event") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        } else {
                            alert(response.message);
                        }
                        location.reload();
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to delete event';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else {
                        alert(msg);
                    }
                }
            });
        }
    });
    
    // ============================================
    // FORM SUBMIT - AJAX
    // ============================================
    $('#eventForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var method = form.find('input[name="_method"]').val() || 'POST';
        var formData = form.serialize();
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    $('#addEventModal').modal('hide');
                    location.reload();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function(xhr) {
                var msg = 'Failed to save event';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    msg = Object.values(errors).flat().join('\n');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            }
        });
    });
});
</script>
@endsection