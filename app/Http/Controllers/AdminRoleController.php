<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RolePermissionSeederExporter;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class AdminRoleController extends Controller
{
    private const PROTECTED_ROLES = ['super-admin', 'admin'];

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super-admin']);
    }

    /**
     * Display a listing of roles
     */
    public function index()
    {
        $priority = User::roleDisplayPriority();

        $roles = Role::with('permissions', 'users')
            ->get()
            ->sortBy(function (Role $role) use ($priority) {
                $index = array_search($role->name, $priority, true);
                return $index === false ? 999 : $index;
            })
            ->values();

        $protectedRoles = self::PROTECTED_ROLES;

        return view('admin.roles.index', compact('roles', 'protectedRoles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[count($parts) - 1]; // Group by last word (facilities, users, etc.)
        });
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::transaction(function () use ($request) {
            $role = Role::create(['name' => $request->name]);
            
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }
        });

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[count($parts) - 1]; // Group by last word
        });
        
        $role->load('permissions');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->name]);
            
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }
        });

        $sync = app(RolePermissionSeederExporter::class)->syncFromRequest($request);
        $message = 'Role updated successfully.'.app(RolePermissionSeederExporter::class)->seederSyncMessage($sync);

        return redirect()->route('admin.roles.edit', $role)
            ->with('success', $message);
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of critical roles
        $protectedRoles = self::PROTECTED_ROLES;
        
        if (in_array($role->name, $protectedRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete protected role: ' . $role->name);
        }

        // Check if role is assigned to any users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role that is assigned to users. Please reassign users first.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Get role permissions for AJAX
     */
    public function getPermissions(Role $role)
    {
        return response()->json([
            'permissions' => $role->permissions->pluck('id')->toArray()
        ]);
    }

    public function syncSeeder(RolePermissionSeederExporter $exporter)
    {
        $sync = $exporter->sync();

        if (! empty($sync['synced'])) {
            return redirect()->back()->with(
                'success',
                'Role permission seeder updated with '.$sync['count'].' role(s). Commit database/seeders/data/role_permissions.json so migrate:fresh --seed restores these permissions.'
            );
        }

        return redirect()->back()->with(
            'error',
            'Seeder update failed: '.($sync['error'] ?? 'Unknown error')
        );
    }
}