<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * List all admin users (role_id 1, 2, 3).
     * Only accessible by Global Admins (role_id 1).
     */
    public function index(Request $request)
    {
        $actingAdmin = $request->attributes->get('admin_user');

        if (!$actingAdmin || (int) $actingAdmin->role_id !== 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Only a Global Admin can view admin users.',
            ], 403);
        }

        $users = User::whereIn('role_id', [1, 2, 3])
            ->with('location:id,name')
            ->get(['id', 'email', 'role_id', 'location_id', 'created_at']);

        return response()->json([
            'status' => 1,
            'data' => $users,
        ]);
    }

    /**
     * Create a new admin / sub-admin user.
     * Only a global admin (role_id 1) may create other admins.
     */
    public function store(Request $request)
    {
        $actingAdmin = $request->attributes->get('admin_user');

        if (!$actingAdmin || (int) $actingAdmin->role_id !== 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Only a Global Admin can create admin users.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|in:1,2,3',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        if ((int) $validated['role_id'] === 2 && empty($validated['location_id'])) {
            return response()->json([
                'status' => 0,
                'message' => 'Studio Admin must be assigned to a studio location.',
            ], 422);
        }

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'location_id' => $validated['location_id'] ?? null,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Admin user created.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'location_id' => $user->location_id,
            ],
        ], 201);
    }

    /**
     * Update an admin user's role or location.
     */
    public function update(Request $request, $id)
    {
        $actingAdmin = $request->attributes->get('admin_user');

        if (!$actingAdmin || (int) $actingAdmin->role_id !== 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Only a Global Admin can modify admin users.',
            ], 403);
        }

        $target = User::whereIn('role_id', [1, 2, 3])->findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'sometimes|in:1,2,3',
            'location_id' => 'sometimes|nullable|exists:locations,id',
            'password' => 'sometimes|string|min:8',
        ]);

        $effectiveRoleId = $validated['role_id'] ?? $target->role_id;
        $effectiveLocationId = array_key_exists('location_id', $validated) ? $validated['location_id'] : $target->location_id;
        if ((int) $effectiveRoleId === 2 && empty($effectiveLocationId)) {
            return response()->json([
                'status' => 0,
                'message' => 'Studio Admin must be assigned to a studio location.',
            ], 422);
        }

        if (isset($validated['role_id'])) {
            $target->role_id = $validated['role_id'];
        }
        if (array_key_exists('location_id', $validated)) {
            $target->location_id = $validated['location_id'];
        }
        if (isset($validated['password'])) {
            $target->password = Hash::make($validated['password']);
        }

        $target->save();

        return response()->json([
            'status' => 1,
            'message' => 'Admin user updated.',
        ]);
    }

    /**
     * Delete an admin user. Cannot delete yourself.
     */
    public function destroy(Request $request, $id)
    {
        $actingAdmin = $request->attributes->get('admin_user');

        if (!$actingAdmin || (int) $actingAdmin->role_id !== 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Only a Global Admin can delete admin users.',
            ], 403);
        }

        if ((int) $actingAdmin->id === (int) $id) {
            return response()->json([
                'status' => 0,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $target = User::whereIn('role_id', [1, 2, 3])->findOrFail($id);
        $target->tokens()->delete();
        $target->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Admin user deleted.',
        ]);
    }
}
