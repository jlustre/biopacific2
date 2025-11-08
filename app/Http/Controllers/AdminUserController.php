<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    // The route already uses 'role:admin' middleware, so no need for 'can:isAdmin'.

    public function index(Request $request)
    {
        $query = User::with(['roles', 'facility']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role') && $request->get('role') !== 'all') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        // Facility filter
        if ($request->filled('facility') && $request->get('facility') !== 'all') {
            if ($request->get('facility') === 'corporate') {
                $query->where('facility_id', 99);
            } else {
                $query->where('facility_id', $request->get('facility'));
            }
        }

        // Get filtered users with pagination
        $users = $query->paginate(10)->withQueryString();

        // Get data for filters
        $roles = \Spatie\Permission\Models\Role::all();
        $facilities = \App\Models\Facility::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'facilities'));
    }

    public function create()
    {
        $facilities = \App\Models\Facility::orderBy('name')->get();
        return view('admin.users.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        // Set default facility_id to 99 (Bio-Pacific Corporate) if not provided
        $facilityId = $request->facility_id ?: 99;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'facility_id' => $facilityId,
        ]);
        $user->assignRole($request->role);

    return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user = null)
    {
        if (!$user) {
            abort(404, 'User not found');
        }
        $facilities = \App\Models\Facility::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'facilities'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'facility_id' => $request->facility_id ?: 99, // Default to Bio-Pacific Corporate
        ]);
        $user->syncRoles([$request->role]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

    return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show users for the current user's facility (for facility-admin and facility-editor)
     */
    public function facilityUsers()
    {
        $user = Auth::user();
        
        // Admin can see all users, facility-admin/facility-editor see only their facility users
        if ($user->hasRole('admin')) {
            $users = User::with('roles', 'facility')->paginate(10);
        } else {
            $users = User::where('facility_id', $user->facility_id)
                         ->with('roles', 'facility')
                         ->paginate(10);
        }
        
        return view('admin.users.facility-users', compact('users'));
    }
}
