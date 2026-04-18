@extends('layouts.admission')

@section('title', 'Follow-up Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Follow-up Logs</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.inquiries.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back to Inquiries
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $statistics['total'] }}</h3>
                                    <p>Total Follow-ups</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $statistics['calls'] }}</h3>
                                    <p>Calls</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $statistics['emails'] }}</h3>
                                    <p>Emails</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $statistics['meetings'] }}</h3>
                                    <p>Meetings</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Inquiry number, name, subject..." value="{{ $search }}">
                            </div>
                            <div class="col-md-4">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="call" {{ $type == 'call' ? 'selected' : '' }}>Call</option>
                                    <option value="email" {{ $type == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="meeting" {{ $type == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                    <option value="note" {{ $type == 'note' ? 'selected' : '' }}>Note</option>
                                    <option value="other" {{ $type == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
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
                                    <th>Date</th>
                                    <th>Inquiry #</th>
                                    <th>Inquirer</th>
                                    <th>Subject</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Next Follow-up</th>
                                    <th>Created By</th>
                                    <th>Inquiry Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($followUps as $followUp)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($followUp->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $followUp->inquiry_number }}</td>
                                        <td>{{ $followUp->full_name }}</td>
                                        <td>{{ Str::limit($followUp->subject, 40) }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($followUp->follow_up_type) }}</span>
                                        </td>
                                        <td>{{ Str::limit($followUp->notes, 60) }}</td>
                                        <td>{{ $followUp->next_follow_up_date ? \Carbon\Carbon::parse($followUp->next_follow_up_date)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $followUp->first_name }} {{ $followUp->last_name }}</td>
                                        <td>
                                            @php
                                                $statusColors = ['new' => 'warning', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary'];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$followUp->inquiry_status] }}">
                                                {{ ucfirst(str_replace('_', ' ', $followUp->inquiry_status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No follow-up logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $followUps->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection