<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * Authenticate an admin user and return a Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Only role_id 1 (global admin), 2 (studio admin), 3 (support) allowed
        if (!in_array((int) $user->role_id, [1, 2, 3], true)) {
            return response()->json([
                'status' => 0,
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => 1,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ],
        ]);
    }

    /**
     * Revoke the current admin token (logout).
     */
    public function logout(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken) {
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            if ($tokenModel) {
                $tokenModel->delete();
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Logged out successfully.',
        ]);
    }
}
