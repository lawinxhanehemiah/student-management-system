{{-- resources/views/admission/students/_form_fields.blade.php --}}
<div class="row">
    <!-- Registration Number Info (Auto-generated) -->
    <div class="col-12 mb-3">
        <div class="alert alert-info">
            <i class="feather-info me-2"></i>
            <strong>Registration Number:</strong> Itazalishwa moja kwa moja kama <strong>02.[SEQUENCE].[INTAKE_CODE].[YEAR]</strong>
            <br><small>Mfano: 02.001.03.2026 kwa March intake</small>
        </div>
    </div>

    <!-- Academic Information - Students Table -->
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Programme <span class="text-danger">*</span></label>
        <select name="programme_id" id="programme_id" class="form-select" required>
            <option value="">Select Programme</option>
            @foreach($programmes as $programme)
                <option value="{{ $programme->id }}" {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                    {{ $programme->name }}
                </option>
            @endforeach
        </select>
    </div>

    

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
        <select name="academic_year_id" class="form-select" required>
            <option value="">Select Academic Year</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Intake <span class="text-danger">*</span></label>
        <select name="intake" class="form-select" required>
            <option value="">Select Intake</option>
            <option value="March" {{ old('intake') == 'March' ? 'selected' : '' }}>March</option>
            <option value="September" {{ old('intake') == 'September' ? 'selected' : '' }}>September</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Study Mode <span class="text-danger">*</span></label>
        <select name="study_mode" class="form-select" required>
            <option value="">Select Mode</option>
            <option value="full_time" {{ old('study_mode') == 'full_time' ? 'selected' : '' }}>Full Time</option>
            <option value="part_time" {{ old('study_mode') == 'part_time' ? 'selected' : '' }}>Part Time</option>
            <option value="distance" {{ old('study_mode') == 'distance' ? 'selected' : '' }}>Distance Learning</option>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Current Level <span class="text-danger">*</span></label>
        <select name="current_level" class="form-select" required>
            <option value="">Select Level</option>
            @for($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}" {{ old('current_level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
            @endfor
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Current Semester <span class="text-danger">*</span></label>
        <select name="current_semester" class="form-select" required>
            <option value="">Select Semester</option>
            <option value="1" {{ old('current_semester') == 1 ? 'selected' : '' }}>Semester 1</option>
            <option value="2" {{ old('current_semester') == 2 ? 'selected' : '' }}>Semester 2</option>
        </select>
    </div>

    <!-- Guardian Information - Students Table -->
    <div class="col-12 mt-4">
        <h6 class="fw-bold text-warning mb-3">
            <i class="feather-users me-1"></i> Guardian Information
        </h6>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Guardian Name <span class="text-danger">*</span></label>
        <input type="text" name="guardian_name" class="form-control" required 
               value="{{ old('guardian_name') }}" placeholder="Full name of guardian">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Guardian Phone <span class="text-danger">*</span></label>
        <input type="text" name="guardian_phone" class="form-control" required 
               value="{{ old('guardian_phone') }}" placeholder="0712345678">
    </div>

    <!-- Hidden Fields -->
    <input type="hidden" name="student_status" value="active">

    <!-- Invoice Option -->
    <div class="col-12 mb-3 mt-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="generate_invoice" id="generate_invoice" value="1" checked>
            <label class="form-check-label fw-bold" for="generate_invoice">
                Generate Tuition Invoice Immediately
            </label>
            <small class="text-muted d-block">
                This will create an annual tuition invoice with control number
            </small>
        </div>
    </div>
</div>