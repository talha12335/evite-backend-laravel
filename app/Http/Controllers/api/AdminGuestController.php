<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class AdminGuestController extends Controller
{
    public function index(Request $request)
    {
        $adminUser = $request->attributes->get('admin_user');

        $query = Guest::with(['invitation' => function ($q) {
            $q->select('id', 'user_id', 'occasion', 'date', 'location_id')
              ->with('user:id,email', 'location:id,name');
        }])->orderBy('id', 'desc');

        if ($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id) {
            $query->whereHas('invitation', function ($q) use ($adminUser) {
                $q->where('location_id', $adminUser->location_id);
            });
        }

        if ($locationId = $request->query('location_id')) {
            $query->whereHas('invitation', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        $searchQ = $request->query('q');
        if ($searchQ) {
            $query->where('guestEmail', 'like', '%' . $searchQ . '%');
        }

        $allRows = $query->get();

        $grouped = [];
        foreach ($allRows as $guestRow) {
            $emails = json_decode($guestRow->guestEmail, true);
            if (!is_array($emails)) {
                $emails = [$guestRow->guestEmail];
            }

            $inv = $guestRow->invitation;
            foreach ($emails as $email) {
                $email = is_string($email) ? strtolower(trim($email)) : null;
                if (!$email) continue;

                if ($searchQ && stripos($email, $searchQ) === false) continue;

                if (!isset($grouped[$email])) {
                    $grouped[$email] = [
                        'email' => $email,
                        'invitations_count' => 0,
                        'invitations' => [],
                    ];
                }

                $grouped[$email]['invitations_count']++;
                $grouped[$email]['invitations'][] = [
                    'invitation_id' => $guestRow->invitation_id,
                    'client_email' => $inv && $inv->user ? $inv->user->email : null,
                    'occasion' => $inv ? $inv->occasion : null,
                    'location' => $inv && $inv->location ? $inv->location->name : null,
                    'event_date' => $inv ? $inv->date : null,
                    'added_at' => $guestRow->created_at ? $guestRow->created_at->toDateTimeString() : null,
                ];
            }
        }

        $allGrouped = array_values($grouped);
        $total = count($allGrouped);
        $perPage = min((int) ($request->query('per_page', 15)), 5000);
        $currentPage = max((int) ($request->query('page', 1)), 1);
        $offset = ($currentPage - 1) * $perPage;
        $pageItems = array_slice($allGrouped, $offset, $perPage);

        return response()->json([
            'status' => 1,
            'data' => $pageItems,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : null,
                'to' => $total > 0 ? min($offset + $perPage, $total) : null,
            ],
        ]);
    }
}
