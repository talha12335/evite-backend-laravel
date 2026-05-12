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
        $query = User::whereNotIn('role_id', [1, 2, 3])
            ->withCount('invitations')
            ->orderBy('id', 'desc');

        if ($q = $request->query('q')) {
            $query->where('email', 'like', '%' . $q . '%');
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
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
}
