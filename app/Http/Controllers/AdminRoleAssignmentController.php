<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class AdminRoleAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:web-admin|admin']);
    }

    /**
     * Display the role assignment interface
     */
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Filter by role if specified
        if ($request->filled('role')) {
            $query->role($request->role);
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        $users = $query->paginate(15);
        $roles = Role::all();
        
        return view('admin.role-assignments.index', compact('users', 'roles'));
    }

    /**
     * Show role assignment form for a specific user
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        
        return view('admin.role-assignments.edit', compact('user', 'roles'));
    }

    /**
     * Update user roles
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::transaction(function () use ($request, $user) {
            if ($request->has('roles')) {
                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles);
            } else {
                $user->syncRoles([]);
            }
        });

        return redirect()->route('admin.role-assignments.index')
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Bulk assign roles to multiple users
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'action' => 'required|in:assign,remove'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $role = Role::findOrFail($request->role_id);

        DB::transaction(function () use ($users, $role, $request) {
            foreach ($users as $user) {
                if ($request->action === 'assign') {
                    $user->assignRole($role);
                } else {
                    $user->removeRole($role);
                }
            }
        });

        $action = $request->action === 'assign' ? 'assigned to' : 'removed from';
        
        return redirect()->route('admin.role-assignments.index')
            ->with('success', "Role '{$role->name}' {$action} " . count($request->user_ids) . " user(s).");
    }

    /**
     * Quick role assignment via AJAX
     */
    public function quickAssign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'action' => 'required|in:assign,remove'
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        try {
            if ($request->action === 'assign') {
                $user->assignRole($role);
                $message = "Role '{$role->name}' assigned to {$user->name}";
            } else {
                $user->removeRole($role);
                $message = "Role '{$role->name}' removed from {$user->name}";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'user_roles' => $user->fresh()->roles->pluck('name')->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users for role assignment via AJAX
     */
    public function getUsersForRole(Role $role)
    {
        $usersWithRole = $role->users()->get(['id', 'name', 'email']);
        $usersWithoutRole = User::whereDoesntHave('roles', function($query) use ($role) {
            $query->where('role_id', $role->id);
        })->get(['id', 'name', 'email']);

        return response()->json([
            'users_with_role' => $usersWithRole,
            'users_without_role' => $usersWithoutRole
        ]);
    }

    /**
     * Show detailed role assignment statistics
     */
    public function statistics()
    {
        $roles = Role::withCount('users')->get();
        $totalUsers = User::count();
        $usersWithoutRoles = User::doesntHave('roles')->count();
        
        $roleDistribution = [];
        foreach ($roles as $role) {
            $roleDistribution[] = [
                'name' => $role->name,
                'count' => $role->users_count,
                'percentage' => $totalUsers > 0 ? round(($role->users_count / $totalUsers) * 100, 1) : 0
            ];
        }

        return view('admin.role-assignments.statistics', compact(
            'roles', 
            'totalUsers', 
            'usersWithoutRoles', 
            'roleDistribution'
        ));
    }
}