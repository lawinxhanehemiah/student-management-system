@extends('layouts.admission')

@section('title', 'Workflow Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <!-- Workflow Stages -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Workflow Stages</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addStageModal">
                            <i class="feather-plus"></i> Add Stage
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Stage Name</th>
                                    <th>Responsible Role</th>
                                    <th>Days to Complete</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflowStages as $stage)
                                    <tr>
                                        <td>{{ $stage->stage_order }}</td>
                                        <td>{{ $stage->stage_name }}<br><small class="text-muted">{{ $stage->stage_code }}</small></td>
                                        <td>{{ ucfirst($stage->responsible_role) }}</td>
                                        <td>{{ $stage->days_to_complete ?? 'N/A' }}</td>
                                        <td>
                                            @if($stage->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-stage" 
                                                    data-id="{{ $stage->id }}"
                                                    data-name="{{ $stage->stage_name }}"
                                                    data-code="{{ $stage->stage_code }}"
                                                    data-order="{{ $stage->stage_order }}"
                                                    data-role="{{ $stage->responsible_role }}"
                                                    data-desc="{{ $stage->description }}"
                                                    data-days="{{ $stage->days_to_complete }}"
                                                    data-required="{{ $stage->is_required }}"
                                                    data-active="{{ $stage->is_active }}">
                                                <i class="feather-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-stage" data-id="{{ $stage->id }}">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                         </td>
                                    </table>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No workflow stages configured</td>
                                    </tr>
                                @endforelse
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
            
            <!-- Notification Templates -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Notification Templates</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTemplateModal">
                            <i class="feather-plus"></i> Add Template
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr><th>Type</th><th>Subject</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($notificationTemplates as $template)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $template->type)) }}</td>
                                        <td>{{ $template->subject }}</td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                         </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-template" 
                                                    data-id="{{ $template->id }}"
                                                    data-type="{{ $template->type }}"
                                                    data-subject="{{ $template->subject }}"
                                                    data-body="{{ $template->body }}"
                                                    data-active="{{ $template->is_active }}">
                                                <i class="feather-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-template" data-id="{{ $template->id }}">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                         </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No notification templates</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Selection Criteria -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Selection Criteria</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admission.settings.workflow.selection-criteria') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>CSEE Weight (%)</label>
                                    <input type="number" name="csee_weight" class="form-control" value="{{ $selectionCriteria->csee_weight }}" min="0" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ACSEE Weight (%)</label>
                                    <input type="number" name="acsee_weight" class="form-control" value="{{ $selectionCriteria->acsee_weight }}" min="0" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Division Bonus (%)</label>
                                    <input type="number" name="division_bonus" class="form-control" value="{{ $selectionCriteria->division_bonus }}" min="0" max="50" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Min Ranking Score (%)</label>
                                    <input type="number" name="min_ranking_score" class="form-control" value="{{ $selectionCriteria->min_ranking_score }}" min="0" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="auto_select_enabled" class="form-check-input" value="1" {{ $selectionCriteria->auto_select_enabled ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Auto-Selection</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Criteria</button>
                    </form>
                </div>
            </div>
            
            <!-- Workflow Rules Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Workflow Information</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="feather-info"></i> The admission workflow follows these stages:
                    </div>
                    <ol>
                        <li><strong>Application Submission</strong> - Applicant submits application</li>
                        <li><strong>Document Verification</strong> - Officer verifies documents</li>
                        <li><strong>Academic Review</strong> - Review academic qualifications</li>
                        <li><strong>Selection</strong> - Selection committee review</li>
                        <li><strong>Approval</strong> - Final approval</li>
                        <li><strong>Offer Letter</strong> - Send admission offer</li>
                        <li><strong>Acceptance</strong> - Applicant accepts offer</li>
                        <li><strong>Registration</strong> - Complete registration</li>
                    </ol>
                    <div class="alert alert-warning">
                        <i class="feather-alert-triangle"></i> Changes to workflow stages affect the entire admission process. Please review carefully before saving.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Stage Modal -->
<div class="modal fade" id="addStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Workflow Stage</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="stageForm" method="POST" action="{{ route('admission.settings.workflow.stage.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="stage_id" id="stage_id">
                    <div class="form-group">
                        <label>Stage Name *</label>
                        <input type="text" name="stage_name" id="stage_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Stage Code *</label>
                        <input type="text" name="stage_code" id="stage_code" class="form-control" required>
                        <small class="text-muted">Unique identifier (e.g., DOC_VERIFY)</small>
                    </div>
                    <div class="form-group">
                        <label>Stage Order *</label>
                        <input type="number" name="stage_order" id="stage_order" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Responsible Role *</label>
                        <select name="responsible_role" id="responsible_role" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($staffRoles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Days to Complete</label>
                        <input type="number" name="days_to_complete" id="days_to_complete" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_required" id="is_required" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Required Stage</label>
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Template</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="templateForm" method="POST" action="{{ route('admission.settings.workflow.notification.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="template_id" id="template_id">
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" id="template_type" class="form-control" required>
                            <option value="application_received">Application Received</option>
                            <option value="application_approved">Application Approved</option>
                            <option value="application_rejected">Application Rejected</option>
                            <option value="admission_letter">Admission Letter</option>
                            <option value="registration_confirmation">Registration Confirmation</option>
                            <option value="payment_reminder">Payment Reminder</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" id="template_subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Body *</label>
                        <textarea name="body" id="template_body" class="form-control" rows="8" required></textarea>
                        <small class="text-muted">Available variables: {{ '{' }}applicant_name{{ '}' }}, {{ '{' }}application_number{{ '}' }}, {{ '{' }}programme{{ '}' }}, {{ '{' }}date{{ '}' }}</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="template_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Stage
    $('.edit-stage').on('click', function() {
        $('#stage_id').val($(this).data('id'));
        $('#stage_name').val($(this).data('name'));
        $('#stage_code').val($(this).data('code'));
        $('#stage_order').val($(this).data('order'));
        $('#responsible_role').val($(this).data('role'));
        $('#description').val($(this).data('desc'));
        $('#days_to_complete').val($(this).data('days'));
        $('#is_required').prop('checked', $(this).data('required') == 1);
        $('#is_active').prop('checked', $(this).data('active') == 1);
        $('#addStageModal').modal('show');
        $('#stageForm').attr('action', '{{ url("admission/settings/workflow/stage") }}/' + $(this).data('id'));
        $('input[name="_method"]').remove();
        $('#stageForm').append('<input type="hidden" name="_method" value="PUT">');
    });
    
    // Delete Stage
    $('.delete-stage').on('click', function() {
        if (confirm('Delete this workflow stage?')) {
            var id = $(this).data('id');
            $.ajax({
                url: '{{ url("admission/settings/workflow/stage") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });
    
    // Edit Template
    $('.edit-template').on('click', function() {
        $('#template_id').val($(this).data('id'));
        $('#template_type').val($(this).data('type'));
        $('#template_subject').val($(this).data('subject'));
        $('#template_body').val($(this).data('body'));
        $('#template_active').prop('checked', $(this).data('active') == 1);
        $('#addTemplateModal').modal('show');
        $('#templateForm').attr('action', '{{ url("admission/settings/workflow/notification-template") }}/' + $(this).data('id'));
        $('input[name="_method"]').remove();
        $('#templateForm').append('<input type="hidden" name="_method" value="PUT">');
    });
    
    // Delete Template
    $('.delete-template').on('click', function() {
        if (confirm('Delete this notification template?')) {
            var id = $(this).data('id');
            $.ajax({
                url: '{{ url("admission/settings/workflow/notification-template") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });
    
    // Reset forms on modal close
    $('#addStageModal').on('hidden.bs.modal', function() {
        $('#stageForm')[0].reset();
        $('#stage_id').val('');
        $('#stageForm').attr('action', '{{ route("admission.settings.workflow.stage.store") }}');
        $('input[name="_method"]').remove();
    });
    
    $('#addTemplateModal').on('hidden.bs.modal', function() {
        $('#templateForm')[0].reset();
        $('#template_id').val('');
        $('#templateForm').attr('action', '{{ route("admission.settings.workflow.notification.store") }}');
        $('input[name="_method"]').remove();
    });
});
</script>
@endsection