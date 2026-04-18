@extends('layouts.financecontroller')

@section('title', 'Request Asset Transfer')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Request Asset Transfer</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.transfers.index') }}">Transfers</a></li>
                <li class="breadcrumb-item active">New Transfer</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.asset.transfers.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Transfer Information -->
                    <div class="form-section">
                        <h5 class="section-title">Transfer Details</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_id" class="form-label required">Asset to Transfer</label>
                                <select class="form-select @error('asset_id') is-invalid @enderror" 
                                        id="asset_id" name="asset_id" required>
                                    <option value="">Select Asset...</option>
                                    @foreach($assets as $ast)
                                        <option value="{{ $ast->id }}" 
                                            {{ (old('asset_id', $asset->id ?? '') == $ast->id) ? 'selected' : '' }}
                                            data-department="{{ $ast->department_id }}"
                                            data-user="{{ $ast->assigned_to }}"
                                            data-location="{{ $ast->location }}">
                                            {{ $ast->asset_tag }} - {{ $ast->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transfer_date" class="form-label required">Transfer Date</label>
                                <input type="date" class="form-control @error('transfer_date') is-invalid @enderror" 
                                       id="transfer_date" name="transfer_date" 
                                       value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                                @error('transfer_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Current Location (Read-only) -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Current Location</h5>
                        <div class="row" id="currentInfo">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department</label>
                                <p class="form-control-plaintext" id="currentDepartment">-</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Assigned To</label>
                                <p class="form-control-plaintext" id="currentUser">-</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Location</label>
                                <p class="form-control-plaintext" id="currentLocation">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Destination -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Destination</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="to_department_id" class="form-label">Transfer to Department</label>
                                <select class="form-select @error('to_department_id') is-invalid @enderror" 
                                        id="to_department_id" name="to_department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('to_department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="to_user_id" class="form-label">Transfer to User</label>
                                <select class="form-select @error('to_user_id') is-invalid @enderror" 
                                        id="to_user_id" name="to_user_id">
                                    <option value="">Select User</option>
                                    @foreach($users as $usr)
                                        <option value="{{ $usr->id }}" {{ old('to_user_id') == $usr->id ? 'selected' : '' }}>
                                            {{ $usr->first_name }} {{ $usr->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="to_location" class="form-label">New Location</label>
                                <input type="text" class="form-control @error('to_location') is-invalid @enderror" 
                                       id="to_location" name="to_location" value="{{ old('to_location') }}"
                                       placeholder="e.g., Building A, Room 101">
                                @error('to_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Reason for Transfer</h5>
                        
                        <div class="mb-3">
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Transfer Process
                                </h6>
                                <ul class="small text-muted">
                                    <li>Transfer requests require approval</li>
                                    <li>Asset status becomes "transferred" after approval</li>
                                    <li>You can transfer to department, user, or location</li>
                                    <li>At least one destination field is required</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-paper-plane me-2"></i>Submit Transfer Request
                </button>
                <a href="{{ route('finance.asset.transfers.index') }}" class="btn btn-secondary">
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
    $('#asset_id').change(function() {
        const selected = $(this).find('option:selected');
        
        if (selected.val()) {
            const deptId = selected.data('department');
            const userId = selected.data('user');
            const location = selected.data('location');
            
            // Get department name from dropdown (you'd need to pass this data)
            $.get('/finance/asset/departments/' + deptId + '/name', function(data) {
                $('#currentDepartment').text(data.name || 'N/A');
            });
            
            $.get('/finance/asset/users/' + userId + '/name', function(data) {
                $('#currentUser').text(data.name || 'N/A');
            });
            
            $('#currentLocation').text(location || 'N/A');
        } else {
            $('#currentDepartment').text('-');
            $('#currentUser').text('-');
            $('#currentLocation').text('-');
        }
    });

    // Trigger on page load if asset is selected
    if ($('#asset_id').val()) {
        $('#asset_id').trigger('change');
    }
});
</script>
@endpush