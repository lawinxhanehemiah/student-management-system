@extends('layouts.admission')

@section('title', 'Document Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="feather-folder text-primary"></i> Document Management
                    </h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openUploadModal()">
                        <i class="feather-upload"></i> Upload Document
                    </button>
                </div>
                <div class="card-body">
                    
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $statistics['total'] }}</h3>
                                    <p>Total Documents</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $statistics['pending'] }}</h3>
                                    <p>Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $statistics['verified'] }}</h3>
                                    <p>Verified</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $statistics['rejected'] }}</h3>
                                    <p>Rejected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Status</label>
                                <select name="verification_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="verified" {{ request('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Document Type</label>
                                <select name="document_type" class="form-control">
                                    <option value="">All</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->code }}" {{ request('document_type') == $type->code ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Doc No, Title, Name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">Filter</button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Documents Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Doc No.</th>
                                    <th>Title</th>
                                    <th>Applicant</th>
                                    <th>Type</th>
                                    <th>Uploaded</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td>{{ $doc->document_number }}</td
                                        <td>
                                            <strong>{{ $doc->title }}</strong>
                                            @if($doc->description)
                                                <br><small class="text-muted">{{ Str::limit($doc->description, 50) }}</small>
                                            @endif
                                         </td
                                        <td>
                                            @if($doc->application && $doc->application->personalInfo)
                                                {{ $doc->application->personalInfo->first_name }} {{ $doc->application->personalInfo->last_name }}<br>
                                                <small>{{ $doc->application->application_number }}</small>
                                            @else
                                                N/A
                                            @endif
                                         </td
                                        <td>{{ $doc->document_type }}</td
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td
                                        <td>
                                            @if($doc->verification_status == 'verified')
                                                <span class="badge bg-success">Verified</span>
                                            @elseif($doc->verification_status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                         </td
                                        <td>
                                            <a href="{{ route('admission.documents.download', $doc->id) }}" class="btn btn-sm btn-success">
                                                <i class="feather-download"></i>
                                            </a>
                                            @if($doc->verification_status == 'pending')
                                                <button class="btn btn-sm btn-primary" onclick="verifyDocument({{ $doc->id }}, '{{ $doc->title }}')">
                                                    <i class="feather-check-circle"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-danger" onclick="deleteDocument({{ $doc->id }})">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                         </td
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="feather-folder fa-3x text-muted"></i>
                                            <p class="mt-2">No documents found.</p>
                                        </td
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" id="doc_title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Document Type <span class="text-danger">*</span></label>
                                <select name="document_type" class="form-control" id="doc_type" required>
                                    <option value="">Select Type</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->code }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Application <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="app_search" class="form-control" placeholder="Search by App No or Name...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="searchAppBtn">
                                    <i class="feather-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div id="searchResults" class="list-group mt-2" style="max-height: 250px; overflow-y: auto; display: none;"></div>
                        <input type="hidden" name="application_id" id="selected_app_id">
                        <div id="selectedAppInfo" class="alert alert-info mt-2" style="display: none;">
                            <i class="feather-check-circle"></i> Selected: <span id="selectedAppText"></span>
                            <button type="button" class="close" onclick="clearSelected()">&times;</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" id="doc_desc"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>File <span class="text-danger">*</span> (Max 10MB)</label>
                        <input type="file" name="document" class="form-control-file" id="doc_file" required>
                    </div>
                    
                    <div id="progressBar" class="progress mt-2" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Document</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="verifyForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="verify_doc_id">
                    <p><strong>Document:</strong> <span id="verify_doc_title"></span></p>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="verification_status" id="verify_status" class="form-control" required>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="rejectReasonGroup" style="display: none;">
                        <label>Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Why is this document rejected?"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Verification Notes</label>
                        <textarea name="verification_notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open upload modal
function openUploadModal() {
    $('#uploadModal').modal('show');
}

// Clear selected application
function clearSelected() {
    $('#selected_app_id').val('');
    $('#selectedAppInfo').hide();
    $('#app_search').val('');
}

// Verify document
function verifyDocument(id, title) {
    $('#verify_doc_id').val(id);
    $('#verify_doc_title').text(title);
    $('#verify_status').val('verified');
    $('#rejectReasonGroup').hide();
    $('#verifyModal').modal('show');
}

// Delete document
function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document?')) {
        $.ajax({
            url: '/admission/documents/' + id,
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
}

$(document).ready(function() {
    // Search applications
    $('#searchAppBtn').on('click', function() {
        var search = $('#app_search').val();
        if (search.length < 2) {
            toastr.warning('Enter at least 2 characters');
            return;
        }
        
        $('#searchResults').html('<div class="text-center p-3">Searching...</div>').show();
        
        $.ajax({
            url: '{{ route("admission.documents.search-applications") }}',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, app) {
                        html += `<a href="javascript:void(0)" class="list-group-item list-group-item-action" 
                                    onclick="selectApplication(${app.id}, '${app.application_number} - ${app.first_name} ${app.last_name}')">
                                    <strong>${app.application_number}</strong><br>
                                    <small>${app.first_name} ${app.last_name}</small>
                                </a>`;
                    });
                    $('#searchResults').html(html);
                } else {
                    $('#searchResults').html('<div class="text-center p-3 text-muted">No applications found</div>');
                }
            },
            error: function() {
                $('#searchResults').html('<div class="text-center p-3 text-danger">Search failed</div>');
            }
        });
    });
    
    // Select application
    window.selectApplication = function(id, text) {
        $('#selected_app_id').val(id);
        $('#selectedAppText').text(text);
        $('#selectedAppInfo').show();
        $('#app_search').val('');
        $('#searchResults').hide();
    };
    
    // Search on Enter
    $('#app_search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#searchAppBtn').click();
        }
    });
    
    // Show/hide rejection reason
    $('#verify_status').on('change', function() {
        if ($(this).val() === 'rejected') {
            $('#rejectReasonGroup').show();
        } else {
            $('#rejectReasonGroup').hide();
        }
    });
    
    // Submit verification
    $('#verifyForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#verify_doc_id').val();
        var formData = $(this).serialize();
        
        $.ajax({
            url: '/admission/documents/' + id + '/verify',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#verifyModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });
    
    // Upload document
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#selected_app_id').val()) {
            toastr.error('Please select an application');
            return;
        }
        
        var formData = new FormData(this);
        
        $('#progressBar').show();
        $('.progress-bar').css('width', '0%');
        
        $.ajax({
            url: '{{ route("admission.documents.upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percent = (e.loaded / e.total) * 100;
                        $('.progress-bar').css('width', percent + '%');
                    }
                });
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#uploadModal').modal('hide');
                    $('#uploadForm')[0].reset();
                    clearSelected();
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Upload failed';
                toastr.error(msg);
            },
            complete: function() {
                $('#progressBar').hide();
            }
        });
    });
});
</script>
@endsection