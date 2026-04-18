{{-- resources/views/admission/applications/partials/step2-personal.blade.php --}}
<div id="step-2" class="step-form">
    <form id="form-step-2">
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ $personal->first_name ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ $personal->middle_name ?? '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ $personal->last_name ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ ($personal->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ ($personal->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ $personal->date_of_birth ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="{{ $personal->nationality ?? 'Tanzanian' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Marital Status</label>
                    <select name="marital_status" class="form-control">
                        <option value="">Select Status</option>
                        <option value="single" {{ ($personal->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ ($personal->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                        <option value="divorced" {{ ($personal->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ ($personal->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <button type="button" class="btn btn-secondary" onclick="showStep(1)">Previous</button>
            </div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" onclick="nextStep(2, 3)">Save & Continue</button>
            </div>
        </div>
    </form>
</div>