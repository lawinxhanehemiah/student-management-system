@extends('layouts.financecontroller')

@section('title', 'Asset Categories')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Asset Categories</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Asset Management</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="{{ route('finance.asset.categories.create') }}" class="btn btn-added">
            <i class="fas fa-plus me-2"></i>Add Category
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filters -->
        <div class="table-filter mb-4">
            <form action="{{ route('finance.asset.categories.index') }}" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Category name or code..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-5 col-md-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('finance.asset.categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Depreciation Method</th>
                        <th>Useful Life (Years)</th>
                        <th>Assets Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><strong>{{ $category->code }}</strong></td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->depreciation_method_name }}</td>
                            <td>{{ $category->default_useful_life_years ?? 'N/A' }}</td>
                            <td>{{ $category->assets_count }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('finance.asset.categories.show', $category->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.asset.categories.edit', $category->id) }}" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($category->assets_count == 0)
                                    <form action="{{ route('finance.asset.categories.destroy', $category->id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No categories found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $categories->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection