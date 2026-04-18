{{-- resources/views/admission/applications/partials/step3-contact.blade.php --}}
<div id="step-3" class="step-form">
    <form id="form-step-3">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" class="form-control" value="{{ $contact->phone ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" value="{{ $contact->email ?? $application->applicant_email ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Region *</label>
                    <input type="text" name="region" class="form-control" value="{{ $contact->region ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>District *</label>
                    <input type="text" name="district" class="form-control" value="{{ $contact->district ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Postal Address</label>
                    <input type="text" name="postal_address" class="form-control" value="{{ $contact->postal_address ?? '' }}">
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(2)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(3, 4)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>