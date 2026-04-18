@extends('layouts.financecontroller')

@section('title', 'Approval Levels')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Approval Levels</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                <li class="breadcrumb-item active">Approval Levels</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.procurement.approval-levels.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>Add Level
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.procurement.approval-levels.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="is_active" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('finance.procurement.approval-levels.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Levels Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Approver Type</th>
                        <th>Amount Range</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($levels as $level)
                        <tr>
                            <td><span class="badge bg-info">{{ $level->level_order }}</span></td>
                            <td><strong>{{ $level->code }}</strong></td>
                            <td>{{ $level->name }}</td>
                            <td>{{ $level->approver_name }}</td>
                            <td>
                                {{ number_format($level->min_amount) }} - 
                                {{ $level->max_amount ? number_format($level->max_amount) : '∞' }}
                            </td>
                            <td>
                                @if($level->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.procurement.approval-levels.edit', $level->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('finance.procurement.approval-levels.destroy', $level->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this level?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No approval levels found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $levels->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection