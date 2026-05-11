<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Lightweight health check for load balancers / uptime monitors.
     * GET /api/health — no authentication.
     */
    public function index()
    {
        $payload = [
            'status' => 'ok',
            'service' => config('app.name'),
            'time' => now()->toIso8601String(),
            'checks' => [
                'app' => true,
                'database' => false,
            ],
        ];

        try {
            DB::connection()->getPdo();
            $payload['checks']['database'] = true;
        } catch (\Throwable $e) {
            $payload['status'] = 'degraded';
            $payload['checks']['database'] = false;
            $payload['database_error'] = config('app.debug') ? $e->getMessage() : 'unavailable';

            return response()->json($payload, 503);
        }

        return response()->json($payload, 200);
    }
}
