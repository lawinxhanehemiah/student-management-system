@extends('layouts.financecontroller')

@section('title', 'Add Approval Level')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add Approval Level</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.procurement.approval-levels.index') }}">Approval Levels</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.procurement.approval-levels.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Level Order</label>
                    <input type="number" name="level_order" class="form-control @error('level_order') is-invalid @enderror" value="{{ old('level_order') }}" required>
                    @error('level_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Code</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label required">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Approver Type</label>
                    <select name="approver_type" class="form-select @error('approver_type') is-invalid @enderror" id="approverType" required>
                        <option value="">Select</option>
                        <option value="role" {{ old('approver_type') == 'role' ? 'selected' : '' }}>Role</option>
                        <option value="user" {{ old('approver_type') == 'user' ? 'selected' : '' }}>Specific User</option>
                        <option value="department_head" {{ old('approver_type') == 'department_head' ? 'selected' : '' }}>Department Head</option>
                    </select>
                    @error('approver_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required" id="approverValueLabel">Approver</label>
                    <select name="approver_value" class="form-select @error('approver_value') is-invalid @enderror" id="approverValue" required>
                        <option value="">Select</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" data-type="role" {{ old('approver_value') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-type="user" {{ old('approver_value') == $user->id ? 'selected' : '' }}>{{ $user->first_name }} {{ $user->last_name }}</option>
                        @endforeach
                    </select>
                    @error('approver_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Minimum Amount</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control @error('min_amount') is-invalid @enderror" value="{{ old('min_amount', 0) }}" required>
                    @error('min_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Maximum Amount</label>
                    <input type="number" step="0.01" name="max_amount" class="form-control @error('max_amount') is-invalid @enderror" value="{{ old('max_amount') }}">
                    @error('max_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Leave empty for unlimited</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Level</button>
                <a href="{{ route('finance.procurement.approval-levels.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('approverType').addEventListener('change', function() {
    const type = this.value;
    const options = document.querySelectorAll('#approverValue option');
    
    options.forEach(opt => opt.style.display = 'none');
    
    if (type === 'role') {
        document.querySelectorAll('#approverValue option[data-type="role"]').forEach(opt => opt.style.display = '');
        document.getElementById('approverValueLabel').textContent = 'Select Role';
    } else if (type === 'user') {
        document.querySelectorAll('#approverValue option[data-type="user"]').forEach(opt => opt.style.display = '');
        document.getElementById('approverValueLabel').textContent = 'Select User';
    } else if (type === 'department_head') {
        document.getElementById('approverValueLabel').textContent = 'Department Head';
    }
});
</script>
@endpush
@endsection