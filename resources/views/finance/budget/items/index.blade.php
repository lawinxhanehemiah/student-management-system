@extends('layouts.financecontroller')

@section('title', 'Budget Items')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="fs-4 fw-semibold mb-1">Budget Items</h1>
            <div class="small text-muted">
                <a href="{{ route('finance.dashboard') }}" class="text-muted">Finance</a> > 
                <a href="{{ route('finance.budget.years.index') }}" class="text-muted">Budget Years</a> > 
                <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="text-muted">{{ $budgetYear->name }}</a> > 
                <span>Items</span>
            </div>
        </div>
        <div class="btn-list">
            @if($budgetYear->isDraft())
            <a href="{{ route('finance.budget.items.create', $budgetYear->id) }}" class="btn btn-sm btn-primary">
                <i class="feather-plus"></i> Add Item
            </a>
            @endif
            <a href="{{ route('finance.budget.years.show', $budgetYear->id) }}" class="btn btn-sm btn-light">
                <i class="feather-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-semibold">Filter Items</h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('finance.budget.items.index', $budgetYear->id) }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Items</span>
                            <h4 class="mb-0 fw-semibold">{{ $items->total() }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-list text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Value</span>
                            <h4 class="mb-0 fw-semibold">TZS {{ number_format($items->sum('total_amount'), 0) }}</h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-dollar-sign text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Average Value</span>
                            <h4 class="mb-0 fw-semibold">
                                TZS {{ number_format($items->total() > 0 ? $items->sum('total_amount') / $items->total() : 0, 0) }}
                            </h4>
                        </div>
                        <div class="avatar avatar-sm bg-light">
                            <i class="feather-bar-chart-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Budget Items List</h6>
            <span class="badge bg-secondary">{{ $items->total() }} items</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                            @if($budgetYear->isDraft())
                            <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td>{{ $item->department->name }}</td>
                            <td>
                                <span class="badge bg-{{ $item->category->type_color }}">
                                    {{ $item->category->name }}
                                </span>
                            </td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 0) }}</td>
                            <td class="text-end">TZS {{ number_format($item->unit_price, 0) }}</td>
                            <td class="text-end fw-semibold">TZS {{ number_format($item->total_amount, 0) }}</td>
                            @if($budgetYear->isDraft())
                            <td>
                                <div class="hstack gap-1">
                                    <button class="btn btn-sm btn-icon btn-light" onclick="editItem({{ $item->id }})">
                                        <i class="feather-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light text-danger" onclick="deleteItem({{ $item->id }})">
                                        <i class="feather-trash-2"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $budgetYear->isDraft() ? '8' : '7' }}" class="text-center py-4">
                                <img src="{{ asset('assets/images/no-data.svg') }}" alt="No data" style="height: 80px;">
                                <p class="text-muted small mt-2">No budget items found</p>
                                @if($budgetYear->isDraft())
                                <a href="{{ route('finance.budget.items.create', $budgetYear->id) }}" 
                                   class="btn btn-sm btn-primary mt-2">
                                    Add First Item
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-end small">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteItem(itemId) {
    if (confirm('Are you sure you want to delete this budget item?')) {
        fetch(`/finance/budget/items/{{ $budgetYear->id }}/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function editItem(itemId) {
    // Redirect to edit page or show modal
    alert('Edit functionality coming soon');
}
</script>
@endpush
@endsection