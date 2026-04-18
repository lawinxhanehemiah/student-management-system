{{-- resources/views/admission/applications/partials/step5-academics.blade.php --}}
<div id="step-5" class="step-form">
    <form id="form-step-5">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        <input type="hidden" name="entry_level" value="{{ $application->entry_level ?? 'CSEE' }}">
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Fill in academic information for the applicant.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>CSEE Index Number *</label>
                    <input type="text" name="csee_index_number" class="form-control" value="{{ $academic->csee_index_number ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>CSEE School Name *</label>
                    <input type="text" name="csee_school" class="form-control" value="{{ $academic->csee_school ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>CSEE Year *</label>
                    <input type="number" name="csee_year" class="form-control" value="{{ $academic->csee_year ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>CSEE Division *</label>
                    <select name="csee_division" class="form-control" required>
                        <option value="">Select Division</option>
                        <option value="I" {{ ($academic->csee_division ?? '') == 'I' ? 'selected' : '' }}>Division I</option>
                        <option value="II" {{ ($academic->csee_division ?? '') == 'II' ? 'selected' : '' }}>Division II</option>
                        <option value="III" {{ ($academic->csee_division ?? '') == 'III' ? 'selected' : '' }}>Division III</option>
                        <option value="IV" {{ ($academic->csee_division ?? '') == 'IV' ? 'selected' : '' }}>Division IV</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>CSEE Points *</label>
                    <input type="number" name="csee_points" class="form-control" value="{{ $academic->csee_points ?? '' }}" required>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(4)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(5, 6)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>