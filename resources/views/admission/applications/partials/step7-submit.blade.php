{{-- resources/views/admission/applications/partials/step7-submit.blade.php --}}
<div id="step-7" class="step-form">
    <form id="form-step-7">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Application Review</h5>
            <p>Please review all information before submitting.</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h5 class="card-title">Declaration</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="confirm_information" value="1" class="custom-control-input" id="confirm_information" required>
                                <label class="custom-control-label" for="confirm_information">
                                    I confirm that all information provided is true and correct.
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="accept_terms" value="1" class="custom-control-input" id="accept_terms" required>
                                <label class="custom-control-label" for="accept_terms">
                                    I accept the terms and conditions of admission.
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="confirm_documents" value="1" class="custom-control-input" id="confirm_documents" required>
                                <label class="custom-control-label" for="confirm_documents">
                                    I confirm that all required documents have been uploaded.
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="allow_data_sharing" value="1" class="custom-control-input" id="allow_data_sharing">
                                <label class="custom-control-label" for="allow_data_sharing">
                                    I allow my data to be shared for verification purposes.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(6)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-success" onclick="submitApplication()">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </div>
        </div>
    </form>
</div>