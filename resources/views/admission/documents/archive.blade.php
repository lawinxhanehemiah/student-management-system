@extends('layouts.admission')

@section('title', 'Document Archive')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Document Archive</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadDocumentModal">
                            <i class="feather-upload"></i> Upload Document
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner"><h3>{{ $statistics['total'] }}</h3><p>Total Documents</p></div>
                            </div>
                        </div>
                        @foreach($statistics['by_type'] as $type)
                        <div class="col-md-2">
                            <div class="small-box bg-secondary">
                                <div class="inner"><h3>{{ $type->count }}</h3><p>{{ ucfirst(str_replace('_', ' ', $type->document_type)) }}</p></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Title, Description, Reg No..." value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <label>Document Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="offer_letter" {{ $type == 'offer_letter' ? 'selected' : '' }}>Offer Letter</option>
                                    <option value="id_card" {{ $type == 'id_card' ? 'selected' : '' }}>ID Card</option>
                                    <option value="transcript" {{ $type == 'transcript' ? 'selected' : '' }}>Transcript</option>
                                    <option value="certificate" {{ $type == 'certificate' ? 'selected' : '' }}>Certificate</option>
                                    <option value="other" {{ $type == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Category</label>
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <option value="admission" {{ $category == 'admission' ? 'selected' : '' }}>Admission</option>
                                    <option value="academic" {{ $category == 'academic' ? 'selected' : '' }}>Academic</option>
                                    <option value="financial" {{ $category == 'financial' ? 'selected' : '' }}>Financial</option>
                                    <option value="personal" {{ $category == 'personal' ? 'selected' : '' }}>Personal</option>
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
                                    <th>Document #</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Student</th>
                                    <th>File Size</th>
                                    <th>Uploaded By</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                    <tr>
                                        <td>{{ $document->document_number }}</td>
                                        <td>
                                            <strong>{{ $document->title }}</strong><br>
                                            <small>{{ Str::limit($document->description, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ ucfirst($document->category) }}</span>
                                        </td>
                                        <td>
                                            @if($document->student_id)
                                                {{ $document->registration_number }}<br>
                                                <small>{{ $document->student_first_name }} {{ $document->student_last_name }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($document->file_size / 1024, 2) }} KB</td>
                                        <td>{{ $document->uploaded_by_first_name }} {{ $document->uploaded_by_last_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admission.documents.download', $document->id) }}" class="btn btn-sm btn-success">
                                                <i class="feather-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-doc" data-id="{{ $document->id }}">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                         </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No documents found.</td>
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

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('admission.documents.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Document Type <span class="text-danger">*</span></label>
                                <select name="document_type" class="form-control" required>
                                    <option value="offer_letter">Offer Letter</option>
                                    <option value="id_card">ID Card</option>
                                    <option value="transcript">Transcript</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="admission">Admission</option>
                                    <option value="academic">Academic</option>
                                    <option value="financial">Financial</option>
                                    <option value="personal">Personal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Associated Student (Optional)</label>
                                <select name="student_id" class="form-control">
                                    <option value="">-- None --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->registration_number }} - {{ $student->first_name }} {{ $student->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>File <span class="text-danger">*</span> (PDF, JPG, PNG, DOC, DOCX - Max 10MB)</label>
                        <input type="file" name="document" class="form-control-file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.delete-doc').on('click', function() {
        var id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            $.ajax({
                url: '{{ url("admission/documents") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to delete document');
                }
            });
        }
    });
});
</script>
@endsection