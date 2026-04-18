<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Selected Applications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve the selected applications?</p>
                <div class="mb-3">
                    <label for="bulkApproveNotes" class="form-label">Approval Notes (Optional)</label>
                    <textarea class="form-control" id="bulkApproveNotes" rows="3" 
                              placeholder="Add any notes about this approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="bulkApproveBtn">
                    <i class="fas fa-check me-2"></i> Approve Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Selected Applications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject the selected applications?</p>
                <div class="mb-3">
                    <label for="bulkRejectReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="bulkRejectReason" rows="3" 
                              placeholder="Please provide reason for rejection..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="bulkRejectBtn">
                    <i class="fas fa-times me-2"></i> Reject Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Waitlist Modal -->
<div class="modal fade" id="bulkWaitlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Waitlist Selected Applications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to waitlist the selected applications?</p>
                <div class="mb-3">
                    <label for="bulkWaitlistNotes" class="form-label">Waitlist Notes (Optional)</label>
                    <textarea class="form-control" id="bulkWaitlistNotes" rows="3" 
                              placeholder="Add any notes about waitlisting..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="bulkWaitlistBtn">
                    <i class="fas fa-hourglass-half me-2"></i> Waitlist Selected
                </button>
            </div>
        </div>
    </div>
</div>