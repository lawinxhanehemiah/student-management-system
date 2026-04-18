@extends('layouts.superadmin')

@section('title', 'Users & Roles Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="feather-users"></i> Users & Roles Management
                </h4>
                <ul class="nav nav-tabs card-header-tabs" id="rolesTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">
                            <i class="feather-user"></i> Users
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button">
                            <i class="feather-shield"></i> Roles
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button">
                            <i class="feather-key"></i> Permissions
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="tab-content" id="rolesTabContent">
                    
                    <!-- ========== USERS TAB ========== -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>User Management</h5>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignRoleModal">
                                        <i class="feather-plus"></i> Assign Role
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary mb-1">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#editUserRolesModal{{ $user->id }}">
                                                <i class="feather-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit User Roles Modal -->
                                    <div class="modal fade" id="editUserRolesModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('superadmin.config.roles.user.update', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Roles for {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Assign Roles</label>
                                                            @foreach($roles as $role)
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox" name="roles[]" 
                                                                       value="{{ $role->id }}" 
                                                                       class="form-check-input" 
                                                                       id="user{{ $user->id }}_role{{ $role->id }}"
                                                                       {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="user{{ $user->id }}_role{{ $role->id }}">
                                                                    {{ $role->name }}
                                                                </label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Roles</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            {{ $users->links() }}
                        </div>
                    </div>
                    
                    <!-- ========== ROLES TAB ========== -->
                    <div class="tab-pane fade" id="roles" role="tabpanel">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>Role Management</h5>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                        <i class="feather-plus"></i> Create Role
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($roles as $role)
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            {{ $role->name }}
                                            <span class="badge bg-secondary ms-2">{{ $role->guard_name }}</span>
                                        </h6>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                                <i class="feather-edit"></i>
                                            </button>
                                            @if(!in_array($role->name, ['superadmin', 'admin', 'student']))
                                            <form action="{{ route('superadmin.config.roles.destroy', $role->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Delete this role?')">
                                                    <i class="feather-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if($role->description)
                                            <p class="text-muted small">{{ $role->description }}</p>
                                        @endif
                                        <p><strong>Users:</strong> {{ $role->users->count() }}</p>
                                        
                                        <h6>Permissions:</h6>
                                        <div class="permission-list">
                                            @foreach($role->permissions as $permission)
                                                <span class="badge bg-info mb-1">{{ $permission->name }}</span>
                                            @endforeach
                                            @if($role->permissions->count() === 0)
                                                <span class="text-muted">No permissions assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- ========== PERMISSIONS TAB ========== -->
                    <div class="tab-pane fade" id="permissions" role="tabpanel">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>Permission Management</h5>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPermissionModal">
                                        <i class="feather-plus"></i> Create Permission
                                    </button>
                                </div>
                                <p class="text-muted">System permissions grouped by modules</p>
                            </div>
                        </div>

                        @foreach($permissionsGrouped as $module => $modulePermissions)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-uppercase">{{ $module }}</h6>
                                <span class="badge bg-primary">{{ count($modulePermissions) }} permissions</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <h6>{{ $permission->name }}</h6>
                                                <p class="text-muted small mb-2">
                                                    <strong>Guard:</strong> {{ $permission->guard_name }}
                                                </p>
                                                @if($permission->description)
                                                    <p class="small">{{ $permission->description }}</p>
                                                @endif
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">
                                                        Created: {{ $permission->created_at->format('M d, Y') }}
                                                    </small>
                                                    <form action="{{ route('superadmin.config.roles.permission.destroy', $permission->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Delete this permission?')">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== MODALS ========== -->

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('superadmin.config.roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="name" class="form-control" required>
                            <small class="text-muted">e.g., "Student Registrar", "Finance Officer"</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Guard Name *</label>
                            <select name="guard_name" class="form-control" required>
                                <option value="web" selected>Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <hr>
                    <h6>Assign Permissions</h6>
                    <div class="row">
                        @foreach($permissionsGrouped as $module => $modulePermissions)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header py-2">
                                    <strong>{{ $module }}</strong>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($modulePermissions as $permission)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               class="form-check-input"
                                               id="perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.config.roles.user.assign') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Role to User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select User *</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Choose user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Role *</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Choose role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.config.roles.permission.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Permission Name *</label>
                        <input type="text" name="name" class="form-control" required>
                        <small class="text-muted">e.g., "student create", "finance view"</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Guard Name *</label>
                        <select name="guard_name" class="form-control" required>
                            <option value="web" selected>Web</option>
                            <option value="api">API</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal Template -->
@foreach($roles as $role)
<div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('superadmin.config.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role: {{ $role->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Guard Name *</label>
                            <select name="guard_name" class="form-control" required>
                                <option value="web" {{ $role->guard_name == 'web' ? 'selected' : '' }}>Web</option>
                                <option value="api" {{ $role->guard_name == 'api' ? 'selected' : '' }}>API</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea>
                        </div>
                    </div>
                    
                    <hr>
                    <h6>Permissions</h6>
                    <div class="row">
                        @php
                            $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                        @endphp
                        @foreach($permissionsGrouped as $module => $modulePermissions)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header py-2">
                                    <strong>{{ $module }}</strong>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($modulePermissions as $permission)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               class="form-check-input"
                                               id="edit_perm_{{ $role->id }}_{{ $permission->id }}"
                                               {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="edit_perm_{{ $role->id }}_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script>
    // Simple tab switching
    document.addEventListener('DOMContentLoaded', function() {
        // Get tab from URL if present
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            const tabElement = document.getElementById(tabParam + '-tab');
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        }
    });
</script>
@endsection