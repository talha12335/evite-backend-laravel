<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetMail;
use App\Models\AdminMailSetting;
use App\Models\User;
use App\Services\AdminTransactionalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminPasswordResetController extends Controller
{
    /** Enough time to open inbox and complete reset without DNS flakiness on validation. */
    private const RESET_EXPIRY_MINUTES = 60;

    private const GENERIC_FORGOT_RESPONSE = 'If an account exists for this email, a password reset link has been sent.';

    /** @var AdminTransactionalMailService */
    private $adminTransactionalMail;

    public function __construct(AdminTransactionalMailService $adminTransactionalMail)
    {
        $this->adminTransactionalMail = $adminTransactionalMail;
    }

    private function footerSupportEmail(): string
    {
        $row = AdminMailSetting::first();
        if ($row && !empty($row->from_email)) {
            return $row->from_email;
        }

        return (string) config('mail.from.address', 'hello@example.com');
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:filter|max:255',
        ]);

        $email = Str::lower(trim($validated['email']));
        $ipKey = 'admin-forgot-password:ip:' . $request->ip();
        $emailKey = 'admin-forgot-password:email:' . sha1($email);

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($emailKey, 3)) {
            return response()->json([
                'status' => 1,
                'message' => self::GENERIC_FORGOT_RESPONSE,
            ]);
        }

        RateLimiter::hit($ipKey, 15 * 60);
        RateLimiter::hit($emailKey, 60);

        $user = User::whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('role_id', [1, 2, 3])
            ->first();

        if ($user) {
            $plainToken = Str::random(64);
            $hashedToken = $this->hashToken($plainToken);

            DB::table('password_resets')->where('email', $user->email)->delete();
            DB::table('password_resets')->insert([
                'email' => $user->email,
                'token' => $hashedToken,
                'created_at' => now(),
            ]);

            try {
                $payload = [
                    'name' => $user->name ?: 'Admin',
                    'expires_in_minutes' => self::RESET_EXPIRY_MINUTES,
                    'reset_url' => $this->buildResetUrl($plainToken, $user->email),
                    'support_email' => $this->footerSupportEmail(),
                ];

                $this->adminTransactionalMail->sendPasswordReset(
                    $user->email,
                    AdminPasswordResetMail::SUBJECT_LINE,
                    $payload
                );
            } catch (\Throwable $exception) {
                Log::warning('Admin password reset email failed.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => 1,
            'message' => self::GENERIC_FORGOT_RESPONSE,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:filter|max:255',
            'token' => 'required|string|min:40|max:255',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:12',
                'max:64',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ]);

        $email = Str::lower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('role_id', [1, 2, 3])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'This password reset link is invalid or has expired.',
            ], 422);
        }

        $resetRow = DB::table('password_resets')->where('email', $user->email)->first();
        if (!$resetRow) {
            return response()->json([
                'status' => 0,
                'message' => 'This password reset link is invalid or has expired.',
            ], 422);
        }

        $createdAt = $resetRow->created_at ? Carbon::parse($resetRow->created_at) : null;
        $isExpired = !$createdAt || now()->greaterThan($createdAt->copy()->addMinutes(self::RESET_EXPIRY_MINUTES));
        $isTokenValid = hash_equals($resetRow->token, $this->hashToken($validated['token']));

        if ($isExpired || !$isTokenValid) {
            return response()->json([
                'status' => 0,
                'message' => 'This password reset link is invalid or has expired.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Force logout from all existing admin sessions.
        $user->tokens()->delete();

        DB::table('password_resets')->where('email', $user->email)->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Password has been reset successfully. Please sign in again.',
        ]);
    }

    private function hashToken(string $token): string
    {
        $pepper = config('app.key') ?: 'fallback-pepper';
        return hash_hmac('sha256', $token, $pepper);
    }

    private function buildResetUrl(string $token, string $email): string
    {
        $frontendBaseUrl = rtrim(
            env('FRONTEND_ADMIN_URL', env('FRONTEND_URL', 'http://localhost:3000')),
            '/'
        );

        return $frontendBaseUrl . '/admin/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email);
    }
}
