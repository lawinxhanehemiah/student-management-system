{{-- resources/views/admission/applications/partials/step6-programs.blade.php --}}
<div id="step-6" class="step-form">
    <form id="form-step-6">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Please ensure academic information is saved first to see eligible programmes.
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>First Choice Programme *</label>
                    <select name="first_choice_program_id" class="form-control" required>
                        <option value="">Select Programme</option>
                        @foreach($programs ?? [] as $program)
                            <option value="{{ $program->id }}" {{ ($programChoice->first_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} ({{ $program->code ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Second Choice Programme</label>
                    <select name="second_choice_program_id" class="form-control">
                        <option value="">Select Programme</option>
                        @foreach($programs ?? [] as $program)
                            <option value="{{ $program->id }}" {{ ($programChoice->second_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} ({{ $program->code ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Third Choice Programme</label>
                    <select name="third_choice_program_id" class="form-control">
                        <option value="">Select Programme</option>
                        @foreach($programs ?? [] as $program)
                            <option value="{{ $program->id }}" {{ ($programChoice->third_choice_program_id ?? '') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} ({{ $program->code ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(5)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(6, 7)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>