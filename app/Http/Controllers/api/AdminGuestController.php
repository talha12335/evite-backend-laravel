<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGuestController extends Controller
{
    public function index(Request $request)
    {
        $adminUser = $request->attributes->get('admin_user');

        $rows = Guest::with(['invitation' => function ($q) {
            $q->select('id', 'user_id', 'occasion', 'date', 'location_id')
              ->with('user:id,email', 'location:id,name');
        }])->orderBy('id', 'desc');

        if ($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id) {
            $rows->whereHas('invitation', function ($q) use ($adminUser) {
                $q->where('location_id', $adminUser->location_id);
            });
        }

        if ($locationId = $request->query('location_id')) {
            $rows->whereHas('invitation', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($q = $request->query('q')) {
            $rows->where('guestEmail', 'like', '%' . $q . '%');
        }

        $totalEmails = (clone $rows)->get()->reduce(function ($carry, $guestRow) {
            $emails = json_decode($guestRow->guestEmail, true);
            return $carry + (is_array($emails) ? count($emails) : 1);
        }, 0);

        $perPage = min((int) ($request->query('per_page', 15)), 5000);
        $paginated = $rows->paginate($perPage);

        $flat = [];
        foreach ($paginated->items() as $guestRow) {
            $emails = json_decode($guestRow->guestEmail, true);
            if (!is_array($emails)) {
                $emails = [$guestRow->guestEmail];
            }

            $inv = $guestRow->invitation;
            foreach ($emails as $email) {
                $email = is_string($email) ? trim($email) : null;
                if (!$email) continue;

                $flat[] = [
                    'email' => $email,
                    'invitation_id' => $guestRow->invitation_id,
                    'client_email' => $inv && $inv->user ? $inv->user->email : null,
                    'occasion' => $inv ? $inv->occasion : null,
                    'location' => $inv && $inv->location ? $inv->location->name : null,
                    'event_date' => $inv ? $inv->date : null,
                    'added_at' => $guestRow->created_at ? $guestRow->created_at->toDateTimeString() : null,
                ];
            }
        }

        return response()->json([
            'status' => 1,
            'data' => $flat,
            'total_emails' => $totalEmails,
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
}
