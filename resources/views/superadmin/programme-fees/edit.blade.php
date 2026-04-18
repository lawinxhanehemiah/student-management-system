@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-edit text-primary"></i> 
            Edit Fee for: {{ $programme->name }} ({{ $programme->code }})
        </h1>
        <a href="{{ route('superadmin.programmes.fees.index', $programme) }}" class="btn btn-secondary">
            <i class="feather-arrow-left"></i> Back to Fees
        </a>
    </div>

    <!-- Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Fee Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('superadmin.programmes.fees.update', [$programme, $fee]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Academic Year -->
                    <div class="col-md-6 mb-3">
                        <label for="academic_year_id" class="form-label">Academic Year *</label>
                        <select class="form-control @error('academic_year_id') is-invalid @enderror" 
                                id="academic_year_id" 
                                name="academic_year_id" 
                                required>
                            <option value="">Select Academic Year</option>
                            @php
                                $academicYears = \App\Models\AcademicYear::active()->get();
                            @endphp
                            @if($academicYears->count() > 0)
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" 
                                        {{ old('academic_year_id', $fee->academic_year_id) == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }} ({{ $year->year }})
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>No academic years found</option>
                            @endif
                        </select>
                        @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Level -->
                    <div class="col-md-6 mb-3">
                        <label for="level" class="form-label">Level *</label>
                        <select class="form-control @error('level') is-invalid @enderror" 
                                id="level" 
                                name="level" 
                                required>
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" 
                                    {{ old('level', $fee->level) == $level ? 'selected' : '' }}>
                                    Level {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Registration Fee -->
                    <div class="col-md-4 mb-3">
                        <label for="registration_fee" class="form-label">Registration Fee *</label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" 
                                   class="form-control @error('registration_fee') is-invalid @enderror" 
                                   id="registration_fee" 
                                   name="registration_fee" 
                                   value="{{ old('registration_fee', $fee->registration_fee) }}" 
                                   min="0" 
                                   step="0.01"
                                   required>
                        </div>
                        @error('registration_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Semester 1 Fee -->
                    <div class="col-md-4 mb-3">
                        <label for="semester_1_fee" class="form-label">Semester 1 Fee *</label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" 
                                   class="form-control @error('semester_1_fee') is-invalid @enderror" 
                                   id="semester_1_fee" 
                                   name="semester_1_fee" 
                                   value="{{ old('semester_1_fee', $fee->semester_1_fee) }}" 
                                   min="0" 
                                   step="0.01"
                                   required>
                        </div>
                        @error('semester_1_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Semester 2 Fee -->
                    <div class="col-md-4 mb-3">
                        <label for="semester_2_fee" class="form-label">Semester 2 Fee *</label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" 
                                   class="form-control @error('semester_2_fee') is-invalid @enderror" 
                                   id="semester_2_fee" 
                                   name="semester_2_fee" 
                                   value="{{ old('semester_2_fee', $fee->semester_2_fee) }}" 
                                   min="0" 
                                   step="0.01"
                                   required>
                        </div>
                        @error('semester_2_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Total Calculation -->
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-secondary">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Registration:</strong> 
                                    <span id="registrationDisplay">0</span>/=
                                </div>
                                <div class="col-md-3">
                                    <strong>Semester 1:</strong> 
                                    <span id="semester1Display">0</span>/=
                                </div>
                                <div class="col-md-3">
                                    <strong>Semester 2:</strong> 
                                    <span id="semester2Display">0</span>/=
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Year:</strong> 
                                    <span id="totalYearDisplay">0</span>/=
                                </div>
                                <div class="col-md-12 mt-2">
                                    <strong class="text-success">Grand Total:</strong> 
                                    <span id="grandTotalDisplay" class="text-success h5">0</span>/=
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Checkbox -->
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', $fee->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Fee is Active
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Information -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="feather-info"></i>
                            <strong>Fee Information:</strong><br>
                            Programme: <strong>{{ $programme->name }} ({{ $programme->code }})</strong><br>
                            Level: <strong>Level {{ $fee->level }}</strong><br>
                            Academic Year: <strong>{{ $fee->academicYear->name ?? 'N/A' }}</strong><br>
                            Created: {{ $fee->created_at->format('d M Y, H:i') }}<br>
                            Last Updated: {{ $fee->updated_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-secondary">
                                <i class="feather-refresh-ccw"></i> Reset Changes
                            </button>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save"></i> Update Fee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Format number with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        // Calculate totals
        function calculateTotals() {
            const regFee = parseFloat($('#registration_fee').val()) || 0;
            const sem1Fee = parseFloat($('#semester_1_fee').val()) || 0;
            const sem2Fee = parseFloat($('#semester_2_fee').val()) || 0;
            const totalYear = sem1Fee + sem2Fee;
            const grandTotal = regFee + totalYear;
            
            // Update displays
            $('#registrationDisplay').text(formatNumber(regFee.toFixed(0)));
            $('#semester1Display').text(formatNumber(sem1Fee.toFixed(0)));
            $('#semester2Display').text(formatNumber(sem2Fee.toFixed(0)));
            $('#totalYearDisplay').text(formatNumber(totalYear.toFixed(0)));
            $('#grandTotalDisplay').text(formatNumber(grandTotal.toFixed(0)));
        }
        
        // Calculate on input
        $('#registration_fee, #semester_1_fee, #semester_2_fee').on('input', calculateTotals);
        
        // Initial calculation
        calculateTotals();
        
        // Confirm reset
        $('button[type="reset"]').click(function(e) {
            if (!confirm('Are you sure you want to reset all changes?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection