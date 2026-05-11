<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;

class AdminInvitationController extends Controller
{
    /**
     * List all invitations with search, filters, and pagination.
     * Query params:
     *   q            – search in host_name, occasion, host_contact
     *   location_id  – filter by location
     *   occasion     – filter by occasion type
     *   date_from    – filter by event date >=
     *   date_to      – filter by event date <=
     *   per_page     – items per page (default 15)
     *   page         – page number
     */
    public function index(Request $request)
    {
        $query = Invitation::with(['user:id,email', 'location:id,name', 'guests'])
            ->orderBy('id', 'desc');

        // Full-text search across key fields
        if ($q = $request->query('q')) {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('host_name', 'like', $like)
                    ->orWhere('occasion', 'like', $like)
                    ->orWhere('host_contact', 'like', $like)
                    ->orWhere('honoree_name', 'like', $like);
            });
        }

        if ($locationId = $request->query('location_id')) {
            $query->where('location_id', $locationId);
        }

        if ($occasion = $request->query('occasion')) {
            $query->where('occasion', $occasion);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $paginated = $query->paginate($perPage);

        // Build clean formatted items
        $items = $paginated->getCollection()->map(function ($inv) {
            $guestEmails = [];
            foreach ($inv->guests as $g) {
                $decoded = json_decode($g->guestEmail, true);
                if (is_array($decoded)) {
                    $guestEmails = array_merge($guestEmails, $decoded);
                } elseif ($g->guestEmail) {
                    $guestEmails[] = $g->guestEmail;
                }
            }
            return [
                'id' => $inv->id,
                'occasion' => $inv->occasion,
                'host_name' => $inv->host_name,
                'host_contact' => $inv->host_contact,
                'honoree_name' => $inv->honoree_name,
                'date' => $inv->date,
                'time' => $inv->time,
                'room' => $inv->room,
                'turning' => $inv->turning,
                'location' => $inv->location ? ['id' => $inv->location->id, 'name' => $inv->location->name] : null,
                'client_email' => $inv->user ? $inv->user->email : null,
                'guest_count' => count($guestEmails),
                'guest_emails' => $guestEmails,
                'image_url' => $inv->image ? url('uploads/' . $inv->image) : null,
                'created_at' => $inv->created_at ? $inv->created_at->toDateTimeString() : null,
            ];
        });

        // Distinct occasions for filter dropdown
        $occasions = Invitation::distinct()->pluck('occasion')->filter()->values();

        return response()->json([
            'status' => 1,
            'data' => $items,
            'occasions' => $occasions,
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
     * Show a single invitation with full details.
     */
    public function show($id)
    {
        $inv = Invitation::with(['user:id,email', 'location:id,name', 'guests'])->findOrFail($id);

        $guestEmails = [];
        foreach ($inv->guests as $g) {
            $decoded = json_decode($g->guestEmail, true);
            if (is_array($decoded)) {
                $guestEmails = array_merge($guestEmails, $decoded);
            } elseif ($g->guestEmail) {
                $guestEmails[] = $g->guestEmail;
            }
        }

        return response()->json([
            'status' => 1,
            'data' => [
                'id' => $inv->id,
                'occasion' => $inv->occasion,
                'host_name' => $inv->host_name,
                'host_contact' => $inv->host_contact,
                'honoree_name' => $inv->honoree_name,
                'date' => $inv->date,
                'time' => $inv->time,
                'room' => $inv->room,
                'turning' => $inv->turning,
                'location' => $inv->location,
                'client_email' => $inv->user ? $inv->user->email : null,
                'guest_count' => count($guestEmails),
                'guest_emails' => $guestEmails,
                'image_url' => $inv->image ? url('uploads/' . $inv->image) : null,
                'created_at' => $inv->created_at ? $inv->created_at->toDateTimeString() : null,
                'updated_at' => $inv->updated_at ? $inv->updated_at->toDateTimeString() : null,
            ],
        ]);
    }
}
