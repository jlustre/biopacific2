<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of permissions
     */
    public function index()
    {
        $permissions = Permission::with('roles')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return ucfirst($parts[count($parts) - 1]); // Group by last word and capitalize
        });
        
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        $categories = [
            'facilities' => 'Facility Management',
            'users' => 'User Management', 
            'roles' => 'Role Management',
            'permissions' => 'Permission Management',
            'content' => 'Content Management',
            'communications' => 'Communications',
            'security' => 'Security & Monitoring',
            'settings' => 'System Settings',
            'reports' => 'Reports & Analytics',
            'panel' => 'Admin Panel Access'
        ];
        
        return view('admin.permissions.create', compact('categories'));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'category' => 'required|string'
        ]);

        $permissionName = $request->input('name');
        
        // If category is provided, format the permission name
        if ($request->category && $request->category !== 'custom') {
            $permissionName = strtolower($request->name) . ' ' . $request->category;
        }

        Permission::create(['name' => $permissionName]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission)
    {
        $categories = [
            'facilities' => 'Facility Management',
            'users' => 'User Management', 
            'roles' => 'Role Management',
            'permissions' => 'Permission Management',
            'content' => 'Content Management',
            'communications' => 'Communications',
            'security' => 'Security & Monitoring',
            'settings' => 'System Settings',
            'reports' => 'Reports & Analytics',
            'panel' => 'Admin Panel Access'
        ];
        
        return view('admin.permissions.edit', compact('permission', 'categories'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Prevent deletion of critical permissions
        $protectedPermissions = [
            'access admin panel',
            'manage users',
            'manage roles',
            'manage permissions'
        ];
        
        if (in_array($permission->name, $protectedPermissions)) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete protected permission: ' . $permission->name);
        }

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete permission that is assigned to roles. Please remove from roles first.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Bulk update permissions for a role
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($request->role_id);
        $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();
        
        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully for role: ' . $role->name
        ]);
    }
}