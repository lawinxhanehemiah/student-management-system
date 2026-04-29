{{-- resources/views/superadmin/results/modals/upload.blade.php --}}

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Bulk Upload Results</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Please ensure your Excel file follows the template format. 
                        <a href="#" onclick="downloadTemplate()">Download template here</a>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Academic Year <span class="text-danger">*</span></label>
                                <select name="academic_year_id" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-control" required>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Excel File (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">Max file size: 10MB</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="auto_approve" name="auto_approve" class="custom-control-input" value="1">
                            <label class="custom-control-label" for="auto_approve">
                                Auto-approve after upload (Super Admin only)
                            </label>
                        </div>
                    </div>
                    
                    <div id="uploadProgress" style="display:none;">
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%">
                                Processing...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    $('#uploadProgress').show();
    
    $.ajax({
        url: '/superadmin/results/api/bulk-upload',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            $('#uploadProgress').hide();
            if (response.success) {
                toastr.success(response.message);
                $('#uploadModal').modal('hide');
                $('#uploadForm')[0].reset();
                loadResults();
                loadStats();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            $('#uploadProgress').hide();
            let error = xhr.responseJSON;
            if (error && error.errors) {
                $.each(error.errors, function(key, val) {
                    toastr.error(val[0]);
                });
            } else if (error && error.message) {
                toastr.error(error.message);
            } else {
                toastr.error('Upload failed. Please check your file format.');
            }
        }
    });
});
</script>