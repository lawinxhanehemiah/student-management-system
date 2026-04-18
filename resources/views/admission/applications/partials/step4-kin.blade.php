{{-- resources/views/admission/applications/partials/step4-kin.blade.php --}}
<div id="step-4" class="step-form">
    <form id="form-step-4">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Guardian/Next of Kin Name *</label>
                    <input type="text" name="guardian_name" class="form-control" value="{{ $kin->guardian_name ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Guardian Phone *</label>
                    <input type="text" name="guardian_phone" class="form-control" value="{{ $kin->guardian_phone ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Relationship *</label>
                    <select name="relationship" class="form-control" required>
                        <option value="">Select Relationship</option>
                        <option value="Father" {{ ($kin->relationship ?? '') == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Mother" {{ ($kin->relationship ?? '') == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Guardian" {{ ($kin->relationship ?? '') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="Sibling" {{ ($kin->relationship ?? '') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                        <option value="Spouse" {{ ($kin->relationship ?? '') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                        <option value="Other" {{ ($kin->relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Guardian Address</label>
                    <textarea name="guardian_address" class="form-control" rows="2">{{ $kin->guardian_address ?? '' }}</textarea>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(3)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(4, 5)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>