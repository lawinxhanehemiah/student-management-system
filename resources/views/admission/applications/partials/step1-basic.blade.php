{{-- resources/views/admission/applications/partials/step1-basic.blade.php --}}
<div id="step-1" class="step-form {{ ($currentStep ?? 1) == 1 ? 'active' : '' }}">
    <form id="form-step-1">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Academic Year *</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($application->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Intake *</label>
                    <select name="intake" class="form-control" required>
                        <option value="March" {{ ($application->intake ?? '') == 'March' ? 'selected' : '' }}>March Intake</option>
                        <option value="September" {{ ($application->intake ?? '') == 'September' ? 'selected' : '' }}>September Intake</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Entry Level *</label>
                    <select name="entry_level" class="form-control" required>
                        <option value="CSEE" {{ ($application->entry_level ?? '') == 'CSEE' ? 'selected' : '' }}>CSEE (Form Four)</option>
                        <option value="ACSEE" {{ ($application->entry_level ?? '') == 'ACSEE' ? 'selected' : '' }}>ACSEE (Form Six)</option>
                        <option value="Diploma" {{ ($application->entry_level ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                        <option value="Degree" {{ ($application->entry_level ?? '') == 'Degree' ? 'selected' : '' }}>Degree</option>
                        <option value="Mature" {{ ($application->entry_level ?? '') == 'Mature' ? 'selected' : '' }}>Mature Entry</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Study Mode</label>
                    <select name="study_mode" class="form-control">
                        <option value="Full Time" {{ ($application->study_mode ?? '') == 'Full Time' ? 'selected' : '' }}>Full Time</option>
                        <option value="Part Time" {{ ($application->study_mode ?? '') == 'Part Time' ? 'selected' : '' }}>Part Time</option>
                        <option value="Distance" {{ ($application->study_mode ?? '') == 'Distance' ? 'selected' : '' }}>Distance Learning</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="is_free_application" value="1" class="custom-control-input" id="is_free_application" {{ ($application->is_free_application ?? 1) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_free_application">Free Application (No fee required)</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(1, 2)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>