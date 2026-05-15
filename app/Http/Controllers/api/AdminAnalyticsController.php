<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\EmailEvent;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\SystemAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    private function resolveRange(Request $request): array
    {
        try {
            $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
            $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;
        } catch (\Throwable $th) {
            $from = null;
            $to = null;
        }
        $filter = $request->query('filter');

        if ($from && $to) {
            return [$from, $to];
        }

        $now = Carbon::now();
        switch ($filter) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [
                    $now->copy()->subDay()->startOfDay(),
                    $now->copy()->subDay()->endOfDay(),
                ];
            case 'weekly':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'monthly':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            default:
                return [null, null];
        }
    }

    public function overview(Request $request)
    {
        $adminUser = $request->attributes->get('admin_user');
        [$from, $to] = $this->resolveRange($request);

        $invitationQuery = Invitation::query();

        if ($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id) {
            $invitationQuery->where('location_id', $adminUser->location_id);
        }

        if ($from && $to) {
            $invitationQuery->whereBetween('created_at', [$from, $to]);
        }

        $invitationCount = (clone $invitationQuery)->count();

        // Count unique guest emails using MySQL JSON_TABLE — avoids loading all rows into PHP
        $invitationIds = (clone $invitationQuery)->pluck('id');
        $guestCount = DB::table('guests')
            ->whereIn('invitation_id', $invitationIds)
            ->selectRaw('COUNT(DISTINCT LOWER(TRIM(je.email))) AS total')
            ->crossJoin(DB::raw('JSON_TABLE(guestEmail, \'$[*]\' COLUMNS (email VARCHAR(255) PATH \'$\')) AS je'))
            ->value('total') ?? 0;

        $activeLocationCountQuery = Location::where('status', 'active');
        if ($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id) {
            $activeLocationCountQuery->where('id', $adminUser->location_id);
        }
        $activeLocationCount = $activeLocationCountQuery->count();

        $deliveryStats = EmailEvent::select('event_type', DB::raw('COUNT(*) AS total'))
            ->whereIn('event_type', ['processed', 'delivered', 'open', 'click', 'bounce', 'spamreport', 'dropped'])
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $locationBreakdown = Location::select(
            'locations.id',
            'locations.name',
            DB::raw('COUNT(invitations.id) AS invitation_count')
        )
            ->leftJoin('invitations', function ($join) use ($from, $to) {
                $join->on('locations.id', '=', 'invitations.location_id');
                if ($from && $to) {
                    $join->whereBetween('invitations.created_at', [$from, $to]);
                }
            })
            ->when($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id, function ($query) use ($adminUser) {
                $query->where('locations.id', $adminUser->location_id);
            })
            ->groupBy('locations.id', 'locations.name')
            ->orderBy('invitation_count', 'desc')
            ->get();

        $recentInvitations = Invitation::with(['user', 'location'])->withCount('guests')
            ->when($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id, function ($query) use ($adminUser) {
                $query->where('location_id', $adminUser->location_id);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $activeAlerts = SystemAlert::where('is_resolved', false)
            ->orderBy('detected_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 1,
            'message' => 'Admin overview generated',
            'data' => [
                'kpis' => [
                    'total_users' => $adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id
                        ? User::whereNotIn('role_id', [1, 2, 3])
                            ->whereHas('invitations', function ($q) use ($adminUser) {
                                $q->where('location_id', $adminUser->location_id);
                            })->count()
                        : User::whereNotIn('role_id', [1, 2, 3])->count(),
                    'total_invitations' => $invitationCount,
                    'total_guests' => $guestCount,
                    'active_locations' => $activeLocationCount,
                ],
                'email_events' => [
                    'processed' => (int) ($deliveryStats['processed'] ?? 0),
                    'delivered' => (int) ($deliveryStats['delivered'] ?? 0),
                    'open' => (int) ($deliveryStats['open'] ?? 0),
                    'click' => (int) ($deliveryStats['click'] ?? 0),
                    'bounce' => (int) ($deliveryStats['bounce'] ?? 0),
                    'spamreport' => (int) ($deliveryStats['spamreport'] ?? 0),
                    'dropped' => (int) ($deliveryStats['dropped'] ?? 0),
                ],
                'system_alerts' => $activeAlerts,
                'locations' => $locationBreakdown,
                'recent_invitations' => $recentInvitations,
                'applied_filter' => [
                    'from' => $from ? $from->toDateTimeString() : null,
                    'to' => $to ? $to->toDateTimeString() : null,
                    'preset' => $request->query('filter', 'all'),
                ],
            ],
        ], 200);
    }

    public function invitationReport(Request $request)
    {
        $adminUser = $request->attributes->get('admin_user');

        $rows = Invitation::with(['user', 'location'])
            ->when($adminUser && (int) $adminUser->role_id === 2 && $adminUser->location_id, function ($query) use ($adminUser) {
                $query->where('location_id', $adminUser->location_id);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($invitation) {
                return [
                    'invitation_id' => $invitation->id,
                    'user_email' => optional($invitation->user)->email,
                    'occasion' => $invitation->occasion,
                    'date' => $invitation->date,
                    'time' => $invitation->time,
                    'location' => optional($invitation->location)->name,
                    'status' => $invitation->status,
                    'created_at' => $invitation->created_at,
                ];
            });

        if ($request->query('format') === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="invitation-report.csv"',
            ];

            $callback = function () use ($rows) {
                $stream = fopen('php://output', 'w');
                fputcsv($stream, ['invitation_id', 'user_email', 'occasion', 'date', 'time', 'location', 'status', 'created_at']);

                foreach ($rows as $row) {
                    fputcsv($stream, [
                        $row['invitation_id'],
                        $row['user_email'],
                        $row['occasion'],
                        $row['date'],
                        $row['time'],
                        $row['location'],
                        $row['status'],
                        $row['created_at'],
                    ]);
                }

                fclose($stream);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Invitation report generated',
            'data' => $rows,
        ], 200);
    }
}
