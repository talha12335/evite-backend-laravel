<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAdminContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Accepts authentication via:
     *   1. Authorization: Bearer <sanctum-token>   (preferred, secure)
     *   2. X-Admin-User-Id header                  (legacy, header-only)
     *
     * Query-param admin_user_id is intentionally NOT accepted to prevent
     * token leakage through server logs and browser history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$allowedRoles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles)
    {
        $adminUser = null;

        // 1. Try Sanctum Bearer token
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenModel = PersonalAccessToken::findToken($bearerToken);
            if ($tokenModel) {
                $adminUser = $tokenModel->tokenable;
            }
        }

        // 2. Legacy header-only fallback (X-Admin-User-Id, NOT query param)
        if (!$adminUser) {
            $adminUserId = $request->header('X-Admin-User-Id');
            if ($adminUserId) {
                $adminUser = User::find($adminUserId);
            }
        }

        if (!$adminUser) {
            return response()->json([
                'status' => 0,
                'message' => 'Authentication required.',
            ], 401);
        }

        if (!empty($allowedRoles) && !in_array((string) $adminUser->role_id, $allowedRoles, true)) {
            return response()->json([
                'status' => 0,
                'message' => 'You are not authorized for this action.',
            ], 403);
        }

        $request->attributes->set('admin_user', $adminUser);

        return $next($request);
    }
}
