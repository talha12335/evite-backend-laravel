<?php

use App\Http\Controllers\api\AdminAuthController;
use App\Http\Controllers\api\AdminUserController;
use App\Http\Controllers\api\AdminClientController;
use App\Http\Controllers\api\AdminInvitationController;
use App\Http\Controllers\api\GuestController;
use App\Http\Controllers\api\InvitationController;
use App\Http\Controllers\api\LocationController;
use App\Http\Controllers\api\AdminAnalyticsController;
use App\Http\Controllers\api\AdminMailSettingsController;
use App\Http\Controllers\api\AdminPasswordResetController;
use App\Http\Controllers\api\EmailWebhookController;
use App\Http\Controllers\api\TemplateController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


//Route::group(['middleware' => 'cors'], function () {
Route::group(['prefix' => 'user'], function () {
    Route::post('/store', [UserController::class, 'store']);
    Route::get('/get', [UserController::class, 'index']);
});


Route::apiResource('template', TemplateController::class);
Route::apiResource('invitation', InvitationController::class);
Route::post('/invitation/update_invitation', [InvitationController::class, 'update']);
Route::apiResource('guest', GuestController::class);
Route::get('/location', [LocationController::class, 'index']);
Route::get('/location/{location}', [LocationController::class, 'show']);

Route::post('/create_new_template', [TemplateController::class, 'create_new_template']);

Route::get('/send-test-email', [GuestController::class, 'sendTestEmail']);

Route::get('/admin_template', [TemplateController::class, 'admin_template']);

Route::middleware(['api.admin:1,2,3'])->group(function () {
    // role_id: 1 = global admin, 2 = studio admin, 3 = support
    Route::get('/admin/me', function (Request $request) {
        $user = $request->attributes->get('admin_user');
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'status' => 1,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'name' => $user->name ?? null,
                'location_id' => $user->location_id ?? null,
                'created_at' => $user->created_at,
            ],
        ]);
    });

    Route::put('/admin/me', function (Request $request) {
        $user = $request->attributes->get('admin_user');
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated) && !empty($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'status' => 1,
            'message' => 'Profile updated successfully.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'name' => $user->name ?? null,
                'location_id' => $user->location_id ?? null,
                'created_at' => $user->created_at,
            ],
        ]);
    });

    Route::get('/admin/overview', [AdminAnalyticsController::class, 'overview']);
    Route::get('/admin/reports/invitations', [AdminAnalyticsController::class, 'invitationReport']);
    // Admin user management (list for all admins; write protected per-method inside controller)
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    // Admin invitations (read-only list + detail)
    Route::get('/admin/invitations', [AdminInvitationController::class, 'index']);
    Route::get('/admin/invitations/{id}', [AdminInvitationController::class, 'show']);
    // Admin clients (regular users, read-only)
    Route::get('/admin/clients', [AdminClientController::class, 'index']);
    Route::get('/admin/clients/{id}', [AdminClientController::class, 'show']);
    Route::get('/admin/settings/mail', [AdminMailSettingsController::class, 'show']);
});

Route::middleware(['api.admin:1,2'])->group(function () {
    Route::post('/location', [LocationController::class, 'store']);
    Route::put('/location/{location}', [LocationController::class, 'update']);
    Route::delete('/location/{location}', [LocationController::class, 'destroy']);
    Route::put('/admin/settings/mail', [AdminMailSettingsController::class, 'update']);
    Route::post('/admin/settings/mail/test', [AdminMailSettingsController::class, 'test']);
});

// Admin authentication
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
Route::post('/admin/forgot-password', [AdminPasswordResetController::class, 'forgotPassword']);
Route::post('/admin/reset-password', [AdminPasswordResetController::class, 'resetPassword']);

Route::post('/webhooks/sendgrid', [EmailWebhookController::class, 'sendgrid']);
//});