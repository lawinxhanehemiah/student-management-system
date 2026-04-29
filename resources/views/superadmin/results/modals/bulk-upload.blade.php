{{-- resources/views/superadmin/results/bulk-upload.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Bulk Upload Results')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-upload"></i> Bulk Upload Results
            </h3>
            <div class="card-tools">
                <a href="{{ route('superadmin.results.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Results
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-info">
                        <div class="info-box-content">
                            <span class="info-box-text">Template Instructions</span>
                            <span class="info-box-number">Required Columns</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                registration_number, module_code, cw, exam
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">Example Data</span>
                            <span class="info-box-number">PST-2024-001, PST04101, 75, 68</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Registration number must exist in system
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="bulkUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select name="academic_year_id" class="form-control" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester</label>
                            <select name="semester" class="form-control" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Module (Optional)</label>
                            <select name="module_id" class="form-control">
                                <option value="">All Modules (use module_code in file)</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->code }} - {{ $module->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Excel File</label>
                    <div class="custom-file">
                        <input type="file" name="file" class="custom-file-input" id="excelFile" accept=".xlsx,.xls,.csv" required>
                        <label class="custom-file-label" for="excelFile">Choose file...</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="autoApprove" name="auto_approve" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="autoApprove">
                            Auto-approve after upload
                        </label>
                    </div>
                </div>
                
                <div id="uploadProgressArea" style="display:none;">
                    <div class="progress mb-2">
                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">
                            0%
                        </div>
                    </div>
                    <p id="uploadStatus" class="text-muted">Processing...</p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                    <a href="#" class="btn btn-success" onclick="downloadTemplate()">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </form>
            
            <div id="uploadResult" style="display:none;" class="mt-4">
                <h5>Upload Results</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner"><h3 id="totalRows">0</h3><p>Total Rows</p></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-success">
                            <div class="inner"><h3 id="successRows">0</h3><p>Successful</p></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-danger">
                            <div class="inner"><h3 id="failedRows">0</h3><p>Failed</p></div>
                        </div>
                    </div>
                </div>
                <div id="errorList" class="alert alert-danger" style="display:none;">
                    <strong>Errors:</strong>
                    <ul id="errorListItems"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});

$('#bulkUploadForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    $('#uploadProgressArea').show();
    $('#uploadResult').hide();
    $('#uploadProgressBar').css('width', '0%').text('0%');
    
    $.ajax({
        url: '/superadmin/results/api/bulk-upload',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            let xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);
                    $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
                }
            });
            return xhr;
        },
        success: function(response) {
            $('#uploadProgressBar').css('width', '100%').text('100%');
            $('#uploadStatus').text('Upload completed!');
            
            if (response.success) {
                $('#totalRows').text(response.data.total_rows || 0);
                $('#successRows').text(response.data.success_rows || 0);
                $('#failedRows').text(response.data.failed_rows || 0);
                
                if (response.data.errors && response.data.errors.length > 0) {
                    $('#errorListItems').empty();
                    $.each(response.data.errors, function(i, err) {
                        $('#errorListItems').append(`<li>${err}</li>`);
                    });
                    $('#errorList').show();
                }
                
                $('#uploadResult').show();
                toastr.success(response.message);
                
                setTimeout(function() {
                    window.location.href = '/superadmin/results';
                }, 3000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            $('#uploadProgressArea').hide();
            let error = xhr.responseJSON;
            toastr.error(error?.message || 'Upload failed');
        }
    });
});

function downloadTemplate() {
    let moduleId = $('select[name="module_id"]').val();
    let url = '/superadmin/results/download-template';
    if (moduleId) url += `?module_id=${moduleId}`;
    window.location.href = url;
}
</script>
@endpush