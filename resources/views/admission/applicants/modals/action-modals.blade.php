<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admission.applicants.approve', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this application?</p>
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label">Approval Notes (Optional)</label>
                        <textarea class="form-control" id="approveNotes" name="notes" rows="3" 
                                  placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i> Approve Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admission.applicants.reject', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this application?</p>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" 
                                  placeholder="Please provide reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i> Reject Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Waitlist Modal -->
<div class="modal fade" id="waitlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admission.applicants.waitlist', $application->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Waitlist Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to waitlist this application?</p>
                    <div class="mb-3">
                        <label for="waitlistNotes" class="form-label">Waitlist Notes (Optional)</label>
                        <textarea class="form-control" id="waitlistNotes" name="notes" rows="3" 
                                  placeholder="Add any notes about waitlisting..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-hourglass-half me-2"></i> Waitlist Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>