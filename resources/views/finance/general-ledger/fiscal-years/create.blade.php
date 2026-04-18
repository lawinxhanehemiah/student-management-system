@extends('layouts.financecontroller')

@section('title', 'Create Fiscal Year')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Create Fiscal Year</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.fiscal-years.index') }}">Fiscal Years</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.general-ledger.fiscal-years.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h5 class="section-title">Fiscal Year Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label required">Fiscal Year Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., FY 2024" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Use a unique, descriptive name</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label required">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label required">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Info Card -->
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Fiscal Year Rules
                                </h6>
                                <ul class="small text-muted">
                                    <li>Fiscal years cannot overlap</li>
                                    <li>End date must be after start date</li>
                                    <li>Name must be unique</li>
                                    <li>First fiscal year will be automatically set as active</li>
                                    <li>Cannot delete fiscal years with transactions</li>
                                </ul>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Typical fiscal year: Jan 1 - Dec 31
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Fiscal Year
                </button>
                <a href="{{ route('finance.general-ledger.fiscal-years.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate name based on dates
    $('#start_date, #end_date').change(function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if(startDate && endDate) {
            const startYear = new Date(startDate).getFullYear();
            const endYear = new Date(endDate).getFullYear();
            
            if(startYear === endYear) {
                $('#name').val('FY ' + startYear);
            } else {
                $('#name').val('FY ' + startYear + '-' + endYear);
            }
        }
    });
});
</script>
@endpush