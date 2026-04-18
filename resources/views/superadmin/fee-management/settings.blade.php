@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-settings text-primary"></i> 
            Fee Management Settings
        </h1>
        <a href="{{ route('superadmin.programmes.index') }}" class="btn btn-secondary">
            <i class="feather-arrow-left"></i> Back to Programmes
        </a>
    </div>

    <!-- Settings Cards -->
    <div class="row">
        <!-- Global Settings -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="feather-globe"></i> Global Fee Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Default Currency</label>
                            <select class="form-control" name="currency">
                                <option value="TZS" selected>Tanzanian Shilling (TZS)</option>
                                <option value="USD">US Dollar (USD)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Deadline (Days)</label>
                            <input type="number" class="form-control" name="payment_deadline" value="30" min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Late Payment Penalty (%)</label>
                            <input type="number" class="form-control" name="late_penalty" value="5" step="0.1" min="0">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_generate_invoice" checked>
                            <label class="form-check-label" for="auto_generate_invoice">
                                Auto-generate invoices
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Fee Categories -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="feather-list"></i> Fee Categories
                    </h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="feather-plus"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tuition Fee</td>
                                    <td>Main academic fee</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="feather-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="feather-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Registration Fee</td>
                                    <td>One-time registration</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="feather-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="feather-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Examination Fee</td>
                                    <td>Exam registration fee</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="feather-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="feather-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Programmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Programme::active()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-book-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Fee Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\ProgrammeFee::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Fees
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\ProgrammeFee::active()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(\App\Models\ProgrammeFee::sum('total_year_fee')) }}/=
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="feather-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Fee Updates -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="feather-clock"></i> Recent Fee Updates
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Programme</th>
                            <th>Level</th>
                            <th>Academic Year</th>
                            <th>Amount</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentFees = \App\Models\ProgrammeFee::with(['programme', 'academicYear'])
                                ->latest()
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($recentFees as $fee)
                        <tr>
                            <td>{{ $fee->updated_at->format('d M Y') }}</td>
                            <td>{{ $fee->programme->code ?? 'N/A' }}</td>
                            <td>Level {{ $fee->level }}</td>
                            <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                            <td>{{ number_format($fee->total_fee) }}/=</td>
                            <td>System</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No fee records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Fee Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Category</button>
            </div>
        </div>
    </div>
</div>
@endsection