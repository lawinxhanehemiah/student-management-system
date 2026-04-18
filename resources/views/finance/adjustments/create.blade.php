@extends('layouts.financecontroller')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">New Adjustment Request</h5>
            @if(isset($student))
                <small>Student: {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }} ({{ $student->registration_number ?? '' }})</small>
            @endif
        </div>
        <div class="card-body">
            @if(!isset($student))
                <div class="alert alert-warning">
                    <i class="feather-alert-triangle"></i> No student selected. Please go to student payment info page and click "Request Adjustment".
                </div>
                <a href="{{ route('finance.student-payment-info.search') }}" class="btn btn-primary">Search Student</a>
            @else
            <form method="POST" action="{{ route('finance.payment-adjustments.store', $student->id) }}" enctype="multipart/form-data">
                @csrf

                {{-- Request Type --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Request Type <span class="text-danger">*</span></label>
                    <select name="request_type" class="form-select" required>
                        <option value="">-- Select Request Type --</option>
                        <option value="manual_payment">Manual Payment</option>
                        <option value="correction">Correction</option>
                        <option value="void">Void</option>
                        <option value="refund">Refund</option>
                    </select>
                </div>

                {{-- Amount --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Amount (TZS) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" step="0.01" required>
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this adjustment is needed..."></textarea>
                </div>

                {{-- Related Invoice --}}
                <div class="mb-3">
                    <label class="form-label">Related Invoice (optional)</label>
                    <select name="invoice_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($student->invoices ?? [] as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - Balance: {{ number_format($inv->balance,0) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ============ ATTACHMENT SECTION ============ --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Supporting Documents</label>
                    <div class="border rounded p-3 bg-light">
                        <div class="mb-3">
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">
                                <i class="feather-info"></i> 
                                Allowed formats: PDF, JPG, PNG, DOC, DOCX. Max size: 5MB
                            </small>
                        </div>
                        <div id="attachmentPreview" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2">
                                <i class="feather-file"></i> <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-link text-danger float-end" onclick="clearAttachment()">Remove</button>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="feather-alert-circle"></i> 
                            Upload payment receipt, bank slip, or any supporting document for this adjustment request.
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label class="form-label">Additional Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                </div>

                <hr>

                {{-- Submit Buttons --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('finance.student-payment-info.show', $student->id) }}" class="btn btn-secondary">
                        <i class="feather-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-send"></i> Submit Request
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<style>
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.3rem;
    }
    .border-light {
        border-color: #e9ecef !important;
    }
</style>

<script>
    // Attachment preview
    document.querySelector('input[name="attachment"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('attachmentPreview');
        const fileNameSpan = document.getElementById('fileName');
        
        if (file) {
            fileNameSpan.innerHTML = '<strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(2) + ' KB)';
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    function clearAttachment() {
        document.querySelector('input[name="attachment"]').value = '';
        document.getElementById('attachmentPreview').style.display = 'none';
    }
</script>
@endsection