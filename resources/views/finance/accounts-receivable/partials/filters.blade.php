@props(['route', 'filters' => [], 'academicYears' => [], 'students' => []])

<div class="card custom-card">
    <div class="card-header">
        <div class="card-title">Filters</div>
        <div class="card-actions">
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="feather-filter"></i> Toggle Filters
            </button>
        </div>
    </div>
    <div class="collapse {{ count($filters) > 0 ? 'show' : '' }}" id="filterCollapse">
        <div class="card-body">
            <form method="GET" action="{{ $route }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Registration No</label>
                        <input type="text" class="form-control" name="reg_no" value="{{ $filters['reg_no'] ?? '' }}" placeholder="Enter reg no">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ ($filters['academic_year_id'] ?? '') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Invoice Type</label>
                        <select class="form-select" name="invoice_type">
                            <option value="">All Types</option>
                            <option value="tuition" {{ ($filters['invoice_type'] ?? '') == 'tuition' ? 'selected' : '' }}>Tuition</option>
                            <option value="registration" {{ ($filters['invoice_type'] ?? '') == 'registration' ? 'selected' : '' }}>Registration</option>
                            <option value="repeat_module" {{ ($filters['invoice_type'] ?? '') == 'repeat_module' ? 'selected' : '' }}>Repeat Module</option>
                            <option value="supplementary" {{ ($filters['invoice_type'] ?? '') == 'supplementary' ? 'selected' : '' }}>Supplementary</option>
                            <option value="hostel" {{ ($filters['invoice_type'] ?? '') == 'hostel' ? 'selected' : '' }}>Hostel</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Aging Category</label>
                        <select class="form-select" name="aging_category">
                            <option value="">All</option>
                            <option value="current" {{ ($filters['aging_category'] ?? '') == 'current' ? 'selected' : '' }}>Current</option>
                            <option value="1_30_days" {{ ($filters['aging_category'] ?? '') == '1_30_days' ? 'selected' : '' }}>1-30 Days</option>
                            <option value="31_60_days" {{ ($filters['aging_category'] ?? '') == '31_60_days' ? 'selected' : '' }}>31-60 Days</option>
                            <option value="61_90_days" {{ ($filters['aging_category'] ?? '') == '61_90_days' ? 'selected' : '' }}>61-90 Days</option>
                            <option value="90_plus_days" {{ ($filters['aging_category'] ?? '') == '90_plus_days' ? 'selected' : '' }}>90+ Days</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Min Amount</label>
                        <input type="number" class="form-control" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}" step="1000">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Max Amount</label>
                        <input type="number" class="form-control" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}" step="1000">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All</option>
                            <option value="unpaid" {{ ($filters['status'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ ($filters['status'] ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="overdue" {{ ($filters['status'] ?? '') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Student</label>
                        <select class="form-select" name="student_id">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ ($filters['student_id'] ?? '') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->first_name }} {{ $student->user->last_name }} - {{ $student->registration_number }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-12 text-end">
                        <a href="{{ url()->current() }}" class="btn btn-light">
                            <i class="feather-x"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>