@extends('layouts.admission')

@section('title', 'Inquiries Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Inquiries Management</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.inquiries.create') }}" class="btn btn-primary btn-sm">
                            <i class="feather-plus-circle"></i> New Inquiry
                        </a>
                        <a href="{{ route('admission.inquiries.export') }}" class="btn btn-info btn-sm">
                            <i class="feather-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $statistics['total'] }}</h3>
                                    <p>Total Inquiries</p>
                                </div>
                                <div class="icon"><i class="feather-message-square"></i></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $statistics['new'] }}</h3>
                                    <p>New</p>
                                </div>
                                <div class="icon"><i class="feather-alert-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $statistics['in_progress'] }}</h3>
                                    <p>In Progress</p>
                                </div>
                                <div class="icon"><i class="feather-loader"></i></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $statistics['resolved'] }}</h3>
                                    <p>Resolved</p>
                                </div>
                                <div class="icon"><i class="feather-check-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ $statistics['closed'] }}</h3>
                                    <p>Closed</p>
                                </div>
                                <div class="icon"><i class="feather-x-circle"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, Email, Phone, Subject..." value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="new" {{ $status == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="general" {{ $type == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="admission" {{ $type == 'admission' ? 'selected' : '' }}>Admission</option>
                                    <option value="program" {{ $type == 'program' ? 'selected' : '' }}>Program</option>
                                    <option value="payment" {{ $type == 'payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="technical" {{ $type == 'technical' ? 'selected' : '' }}>Technical</option>
                                    <option value="complaint" {{ $type == 'complaint' ? 'selected' : '' }}>Complaint</option>
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
                                    <th>Inquiry No.</th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inquiries as $inquiry)
                                    <tr>
                                        <td>{{ $inquiry->inquiry_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($inquiry->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $inquiry->full_name }}</strong>
                                        </td>
                                        <td>
                                            {{ $inquiry->phone }}<br>
                                            <small>{{ $inquiry->email }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'general' => 'secondary',
                                                    'admission' => 'primary',
                                                    'program' => 'info',
                                                    'payment' => 'warning',
                                                    'technical' => 'dark',
                                                    'complaint' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $typeColors[$inquiry->inquiry_type] ?? 'secondary' }}">
                                                {{ ucfirst($inquiry->inquiry_type) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($inquiry->subject, 40) }}</td>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'success',
                                                    'medium' => 'info',
                                                    'high' => 'warning',
                                                    'urgent' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $priorityColors[$inquiry->priority] }}">
                                                {{ ucfirst($inquiry->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'new' => 'warning',
                                                    'in_progress' => 'primary',
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$inquiry->status] }}">
                                                {{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($inquiry->assigned_first_name)
                                                {{ $inquiry->assigned_first_name }} {{ $inquiry->assigned_last_name }}
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admission.inquiries.show', $inquiry->id) }}" class="btn btn-sm btn-info">
                                                <i class="feather-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No inquiries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $inquiries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection