<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\EmailEvent;
use Illuminate\Http\Request;

class EmailWebhookController extends Controller
{
    public function sendgrid(Request $request)
    {
        $events = $request->all();

        if (!is_array($events)) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid webhook payload',
            ], 422);
        }

        $stored = 0;

        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            EmailEvent::create([
                'provider' => 'sendgrid',
                'event_type' => $event['event'] ?? 'unknown',
                'email' => $event['email'] ?? null,
                'message_id' => $event['sg_message_id'] ?? ($event['smtp-id'] ?? null),
                'payload' => $event,
                'occurred_at' => isset($event['timestamp']) ? date('Y-m-d H:i:s', $event['timestamp']) : now(),
            ]);

            $stored++;
        }

        return response()->json([
            'status' => 1,
            'message' => 'Webhook received',
            'stored_events' => $stored,
        ], 200);
    }
}
