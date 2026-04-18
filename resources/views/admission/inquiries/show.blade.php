@extends('layouts.admission')

@section('title', 'Inquiry Details - ' . $inquiry->inquiry_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Inquiry Details</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.inquiries.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Inquiry Number</th>
                                    <td>{{ $inquiry->inquiry_number }}</td>
                                </tr>
                                <tr>
                                    <th>Date Created</th>
                                    <td>{{ \Carbon\Carbon::parse($inquiry->created_at)->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Full Name</th>
                                    <td>{{ $inquiry->full_name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $inquiry->email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $inquiry->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Inquiry Type</th>
                                    <td>{{ ucfirst($inquiry->inquiry_type) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Priority</th>
                                    <td>
                                        @php
                                            $priorityColors = ['low' => 'success', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                                        @endphp
                                        <span class="badge badge-{{ $priorityColors[$inquiry->priority] }}">
                                            {{ ucfirst($inquiry->priority) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $statusColors = ['new' => 'warning', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary'];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$inquiry->status] }}">
                                            {{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assigned To</th>
                                    <td>
                                        @if($inquiry->assigned_first_name)
                                            {{ $inquiry->assigned_first_name }} {{ $inquiry->assigned_last_name }}
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $inquiry->created_by_first_name }} {{ $inquiry->created_by_last_name }}</td>
                                </tr>
                                @if($inquiry->resolved_at)
                                <tr>
                                    <th>Resolved At</th>
                                    <td>{{ \Carbon\Carbon::parse($inquiry->resolved_at)->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Resolved By</th>
                                    <td>{{ $inquiry->resolved_first_name }} {{ $inquiry->resolved_last_name }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Subject</label>
                                <div class="alert alert-info">{{ $inquiry->subject }}</div>
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <div class="alert alert-secondary">{{ nl2br(e($inquiry->message)) }}</div>
                            </div>
                            @if($inquiry->notes)
                            <div class="form-group">
                                <label>Internal Notes</label>
                                <div class="alert alert-warning">{{ nl2br(e($inquiry->notes)) }}</div>
                            </div>
                            @endif
                            @if($inquiry->resolution)
                            <div class="form-group">
                                <label>Resolution</label>
                                <div class="alert alert-success">{{ nl2br(e($inquiry->resolution)) }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Update Status Panel -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Update Status</h4>
                </div>
                <div class="card-body">
                    <form id="updateInquiryForm">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="new" {{ $inquiry->status == 'new' ? 'selected' : '' }}>New</option>
                                <option value="in_progress" {{ $inquiry->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $inquiry->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $inquiry->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low" {{ $inquiry->priority == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $inquiry->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $inquiry->priority == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $inquiry->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assign To</label>
                            <select name="assigned_to" class="form-control">
                                <option value="">-- Unassigned --</option>
                                @foreach($staff as $staffMember)
                                    <option value="{{ $staffMember->id }}" {{ $inquiry->assigned_to == $staffMember->id ? 'selected' : '' }}>
                                        {{ $staffMember->first_name }} {{ $staffMember->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $inquiry->notes }}</textarea>
                        </div>
                        <div class="form-group resolution-field" style="display: {{ $inquiry->status == 'resolved' ? 'block' : 'none' }}">
                            <label>Resolution</label>
                            <textarea name="resolution" class="form-control" rows="3">{{ $inquiry->resolution }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update Inquiry</button>
                    </form>
                </div>
            </div>

            <!-- Add Follow-up -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Add Follow-up</h4>
                </div>
                <div class="card-body">
                    <form id="followUpForm">
                        @csrf
                        <div class="form-group">
                            <label>Follow-up Type</label>
                            <select name="follow_up_type" class="form-control" required>
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="note">Note</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Next Follow-up Date</label>
                            <input type="date" name="next_follow_up_date" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Add Follow-up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up Logs -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Follow-up Logs</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Next Follow-up</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($followUps as $followUp)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($followUp->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($followUp->follow_up_type) }}</span>
                                        </td>
                                        <td>{{ nl2br(e($followUp->notes)) }}</td>
                                        <td>{{ $followUp->next_follow_up_date ? \Carbon\Carbon::parse($followUp->next_follow_up_date)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $followUp->first_name }} {{ $followUp->last_name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No follow-up logs yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide resolution field based on status
    $('select[name="status"]').on('change', function() {
        if ($(this).val() === 'resolved') {
            $('.resolution-field').show();
        } else {
            $('.resolution-field').hide();
        }
    });
    
    // Update inquiry form
    $('#updateInquiryForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admission.inquiries.update", $inquiry->id) }}',
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update inquiry');
            }
        });
    });
    
    // Follow-up form
    $('#followUpForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admission.inquiries.follow-up", $inquiry->id) }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to add follow-up');
            }
        });
    });
});
</script>
@endsection