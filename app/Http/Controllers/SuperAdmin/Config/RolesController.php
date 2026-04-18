<?php

namespace App\Http\Controllers\SuperAdmin\Config;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id')->get();
        $permissions = Permission::all();
        
        // Group permissions by module (first word)
        $permissionsGrouped = [];
        foreach ($permissions as $permission) {
            $module = explode(' ', $permission->name)[0] ?? 'other';
            if (!isset($permissionsGrouped[$module])) {
                $permissionsGrouped[$module] = [];
            }
            $permissionsGrouped[$module][] = $permission;
        }
        
        $users = User::with('roles')->paginate(20);
        
        return view('superadmin.config.roles', compact(
            'roles', 
            'permissionsGrouped', 
            'users'
        ));
    }
    
    public function storeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|in:web,api',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name,
                'description' => $request->description ?? '',
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }
        });

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'required|string|in:web,api',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->name,
                'guard_name' => $request->guard_name,
                'description' => $request->description ?? '',
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }
        });

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Role updated successfully.');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of default roles
        if (in_array($role->name, ['superadmin', 'admin', 'student'])) {
            return redirect()->route('superadmin.config.roles')
                ->with('error', 'Cannot delete system default role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('superadmin.config.roles')
                ->with('error', 'Cannot delete role that has users assigned. Reassign users first.');
        }

        $role->delete();

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Role deleted successfully.');
    }

    public function assignUserRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('superadmin.config.roles')
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);
        
        $user->assignRole($role->name); // Use role name, not role object

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Role assigned to user successfully.');
    }

    public function updateUserRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('superadmin.config.roles')
                ->withErrors($validator)
                ->withInput();
        }

        // Get role names from IDs
        $roleNames = [];
        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $roleNames = $roles->pluck('name')->toArray();
        }
        
        // Sync roles by name
        $user->syncRoles($roleNames);

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'User roles updated successfully.');
    }

    public function storePermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'required|string|in:web,api',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('superadmin.config.roles')
                ->withErrors($validator)
                ->withInput();
        }

        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
            'description' => $request->description,
        ]);

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Permission created successfully.');
    }

    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Check if permission is used by any role
        if ($permission->roles()->count() > 0) {
            return redirect()->route('superadmin.config.roles')
                ->with('error', 'Cannot delete permission that is assigned to roles.');
        }

        $permission->delete();

        return redirect()->route('superadmin.config.roles')
            ->with('success', 'Permission deleted successfully.');
    }
}