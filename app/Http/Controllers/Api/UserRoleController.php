<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserRoleController extends Controller
{
    /**
     * Get all users
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        $query = User::with(['roles', 'roles.permissions']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->whereHas('roles', function($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        if ($status) {
            $query->where('verify_status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get all permissions for each user
        $users->getCollection()->transform(function ($user) {
            $permissions = $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->unique('id')
                ->values();
            $user->permissions = $permissions;
            return $user;
        });

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }

    /**
     * Get single user
     */
    public function show($id)
    {
        $user = User::with(['roles', 'roles.permissions'])->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
        $user->permissions = $permissions;

        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
    }

    /**
     * Create user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'verify_status' => 'nullable|string|in:pending,completed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'verify_status' => $request->verify_status ?? 'completed',
            'role' => 'user', // Keep for backward compatibility
        ]);

        if ($request->has('roles') && is_array($request->roles)) {
            $user->roles()->sync($request->roles);
        }

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'user' => $user->load(['roles', 'roles.permissions']),
        ], 201);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'verify_status' => 'nullable|string|in:pending,completed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only([
            'name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'verify_status'
        ]);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->has('roles') && is_array($request->roles)) {
            // Prevent user from revoking their own admin role
            $currentUser = $request->user();
            if ($currentUser && $currentUser->id == $user->id) {
                $hasAdmin = $user->hasRole('admin');
                $newRoles = Role::whereIn('id', $request->roles)->pluck('slug')->toArray();
                if ($hasAdmin && !in_array('admin', $newRoles)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cannot revoke your own admin role'
                    ], 400);
                }
            }
            $user->roles()->sync($request->roles);
        }

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'user' => $user->fresh(['roles', 'roles.permissions']),
        ]);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $currentUser = request()->user();
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent user from deleting themselves
        if ($currentUser && $currentUser->id == $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        // Prevent deletion of last admin
        if ($user->hasRole('admin')) {
            $adminCount = User::whereHas('roles', function($q) {
                $q->where('slug', 'admin');
            })->count();
            
            if ($adminCount <= 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete the last admin user'
                ], 400);
            }
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->assignRole($request->role_id);

        return response()->json([
            'status' => true,
            'message' => 'Role assigned successfully',
            'user' => $user->fresh(['roles', 'roles.permissions']),
        ]);
    }

    /**
     * Revoke role from user
     */
    public function revokeRole($id, $roleId)
    {
        $currentUser = request()->user();
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Prevent user from revoking their own admin role
        if ($currentUser && $currentUser->id == $user->id && $role->slug === 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot revoke your own admin role'
            ], 400);
        }

        // Prevent revoking last admin
        if ($role->slug === 'admin' && $user->hasRole('admin')) {
            $adminCount = User::whereHas('roles', function($q) {
                $q->where('slug', 'admin');
            })->count();
            
            if ($adminCount <= 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot revoke the last admin role'
                ], 400);
            }
        }

        $user->revokeRole($roleId);

        return response()->json([
            'status' => true,
            'message' => 'Role revoked successfully',
            'user' => $user->fresh(['roles', 'roles.permissions']),
        ]);
    }

    /**
     * Get user permissions
     */
    public function getPermissions($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();

        return response()->json([
            'status' => true,
            'permissions' => $permissions,
        ]);
    }
}
