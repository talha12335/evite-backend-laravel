<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    /**
     * List all regular clients (role_id IS NULL or 0 / not in admin roles).
     * Query params:
     *   q          – search by email
     *   date_from  – filter by registration date >=
     *   date_to    – filter by registration date <=
     *   per_page   – items per page (default 15)
     *   page       – page number
     */
    public function index(Request $request)
    {
        $actingAdmin = $request->attributes->get('admin_user');

        $query = User::whereNotIn('role_id', [1, 2, 3])
            ->withCount('invitations')
            ->orderBy('id', 'desc');

        // Studio Admins (role_id=2) can only see clients who have invitations at their location
        if ($actingAdmin && (int) $actingAdmin->role_id === 2 && $actingAdmin->location_id) {
            $locationId = $actingAdmin->location_id;
            $query->whereHas('invitations', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            });
        }

        if ($locationId = $request->query('location_id')) {
            $query->whereHas('invitations', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $hasInvitations = $request->query('has_invitations');
        if ($hasInvitations === 'yes') {
            $query->has('invitations');
        } elseif ($hasInvitations === 'no') {
            $query->doesntHave('invitations');
        }

        $perPage = min((int) ($request->query('per_page', 15)), 5000);
        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(function ($user) {
            $latestInv = $user->invitations()->with('location:id,name')->orderBy('id', 'desc')->first();
            return [
                'id' => $user->id,
                'name' => $user->name ?: ($latestInv ? self::extractPlainText($latestInv->host_name) : null),
                'email' => $user->email,
                'phone' => $latestInv ? self::extractPlainText($latestInv->host_contact) : null,
                'location_name' => $latestInv && $latestInv->location ? $latestInv->location->name : null,
                'invitations_count' => $user->invitations_count,
                'created_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $items,
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ]);
    }

    /**
     * Show a single client with their invitations.
     */
    public function show($id)
    {
        $user = User::whereNotIn('role_id', [1, 2, 3])
            ->with([
                'invitations' => function ($q) {
                    $q->with('location:id,name')->orderBy('id', 'desc')->limit(50);
                }
            ])
            ->findOrFail($id);

        $invitations = $user->invitations->map(function ($inv) {
            return [
                'id' => $inv->id,
                'image_url' => $inv->image ? url('uploads/' . $inv->image) : null,
                'occasion' => $inv->occasion,
                'host_name' => $inv->host_name,
                'host_contact' => $inv->host_contact,
                'studio_location' => $inv->studio_location,
                'honoree_name' => $inv->honoree_name,
                'turning' => $inv->turning,
                'room' => $inv->room,
                'date' => $inv->date,
                'time' => $inv->time,
                'location' => $inv->location ? $inv->location->name : null,
                'created_at' => $inv->created_at ? $inv->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'invitations_count' => $user->invitations->count(),
                'invitations' => $invitations,
                'created_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
            ],
        ]);
    }

    private static function extractPlainText(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $trimmed = trim($raw);
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded) && isset($decoded['text']) && is_string($decoded['text'])) {
            $out = trim(strip_tags($decoded['text']));
            return $out !== '' ? $out : null;
        }
        return trim(strip_tags($trimmed)) ?: null;
    }
}
