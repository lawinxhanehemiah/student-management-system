@extends('layouts.financecontroller')

@section('title', 'Audit Log Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Audit Log Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.audit.audit-trail') }}">Audit Trail</a></li>
                <li class="breadcrumb-item active">Log Details</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.audit.audit-trail') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Basic Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Date/Time</th>
                        <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>
                            <strong>{{ $log->user_name }}</strong>
                            @if($log->user_email)
                                <br><small>{{ $log->user_email }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>User Role</th>
                        <td>{{ $log->user_role ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Action</th>
                        <td>
                            <span class="badge bg-{{ 
                                $log->action == 'created' ? 'success' : 
                                ($log->action == 'updated' ? 'info' : 
                                ($log->action == 'deleted' ? 'danger' : 'primary')) 
                            }} fs-6">
                                {{ strtoupper($log->action) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Model Type</th>
                        <td>{{ $log->model_type }}</td>
                    </tr>
                    <tr>
                        <th>Model ID</th>
                        <td>{{ $log->model_id }}</td>
                    </tr>
                    <tr>
                        <th>Identifier</th>
                        <td>{{ $log->model_identifier ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $log->description }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5>Technical Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">IP Address</th>
                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td><small>{{ $log->user_agent ?? 'N/A' }}</small></td>
                    </tr>
                    <tr>
                        <th>URL</th>
                        <td><small>{{ $log->url ?? 'N/A' }}</small></td>
                    </tr>
                    <tr>
                        <th>Method</th>
                        <td>{{ $log->method ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Session ID</th>
                        <td><small>{{ $log->metadata['session_id'] ?? 'N/A' }}</small></td>
                    </tr>
                    <tr>
                        <th>Request ID</th>
                        <td><small>{{ $log->metadata['request_id'] ?? 'N/A' }}</small></td>
                    </tr>
                </table>
            </div>
        </div>

        @if($model)
        <div class="card mt-4">
            <div class="card-header">
                <h5>Related Model</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>{{ class_basename($model) }}</strong>
                    @if(method_exists($model, 'getAuditIdentifier'))
                        <br>{{ $model->getAuditIdentifier() }}
                    @endif
                </p>
                <a href="#" class="btn btn-sm btn-primary">View Model</a>
            </div>
        </div>
        @endif
    </div>
</div>

@if($log->old_values || $log->new_values)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Changed Values</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($log->old_values)
                    <div class="col-md-6">
                        <h6>Old Values</h6>
                        <pre class="bg-light p-3 rounded">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    @if($log->new_values)
                    <div class="col-md-6">
                        <h6>New Values</h6>
                        <pre class="bg-light p-3 rounded">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
                @if($log->changed_fields)
                <div class="mt-3">
                    <h6>Changed Fields</h6>
                    @foreach($log->changed_fields as $field)
                        <span class="badge bg-warning">{{ $field }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection