<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Jobs\SendGuestInvitationEmail;
use App\Models\AdminMailSetting;
use App\Models\EmailEvent;
use App\Models\Guest;
use App\Models\Invitation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    public function index()
    {
        $guest = Guest::all();
        if (count($guest) > 0) {
            $response = [
                'message' => count($guest) . ' Guest Found',
                'status' => 1,
                'guest_data' => $guest
            ];
        } else {
            $response = [
                'message' => count($guest) . ' Guest Found',
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }


    // send email and store data
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'guestEmail' => 'required|array',
            'guestEmail.*' => 'string|email',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 0,
                'errors' => $validate->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $validatedData = $validate->validated();
            if (Guest::where('invitation_id', $request->id)->exists()) {
                $guest = Guest::where('invitation_id', $request->id)->first();
                $guest->guestEmail = json_encode($validatedData['guestEmail']);
                $guest->save();
            } else {
                $guest = new Guest();
                $guest->guestEmail = json_encode($validatedData['guestEmail']);
                $guest->invitation_id = $request->id;
                $guest->save();
            }

            $guest = Guest::select('guestEmail')
                ->where('invitation_id', $request->id)
                ->first();

            if (!$guest) {
                throw new Exception('Guest not found');
            }

            $invitationRow = Invitation::with('location')
                ->where('id', $request->id)
                ->first();

            if (!$invitationRow) {
                throw new Exception('Invitation not found');
            }

            if (empty($invitationRow->image)) {
                throw new Exception('Invitation has no image yet; save your invite before emailing guests.');
            }

            $invitationImageUrl = url('uploads/' . $invitationRow->image);
            $imagePath = public_path('uploads/' . $invitationRow->image);
            $imageBase64 = null;
            if (file_exists($imagePath)) {
                $imageBase64 = base64_encode(file_get_contents($imagePath));
            }

            $guest_emails = json_decode($guest->guestEmail, true);
            if (!is_array($guest_emails)) {
                throw new Exception('Invalid guest email data');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Could not save guest list',
                'status' => 0,
                'error_message' => $e->getMessage(),
            ], 500);
        }

        $mailer = $this->resolveGuestInvitationSendGrid();
        if (!$mailer) {
            return response()->json([
                'message' => 'Email is not configured on the server',
                'status' => 0,
                'error_message' => 'Configure SendGrid in Admin → Mail settings, or set SENDGRID_API_KEY and MAIL_FROM_ADDRESS in .env.',
            ], 503);
        }

        $bouncedEmails = EmailEvent::whereIn('event_type', ['bounce', 'dropped', 'spamreport'])
            ->pluck('email')
            ->map(fn($e) => strtolower(trim($e)))
            ->unique()
            ->toArray();

        $sendable = [];
        $skipped = [];
        foreach ($guest_emails as $email) {
            if (in_array(strtolower(trim($email)), $bouncedEmails, true)) {
                $skipped[] = $email;
            } else {
                $sendable[] = $email;
            }
        }

        if (empty($sendable)) {
            return response()->json([
                'message' => 'Guest list saved but all emails were skipped (previously bounced or reported spam)',
                'status' => 0,
                'skipped_emails' => $skipped,
                'guest_data' => $guest,
            ], 200);
        }

        $ctx = $this->buildGuestInvitationMailContext($invitationRow, $invitationImageUrl);

        try {
            $htmlContent = View::make('emails.guest_invitation', $ctx['view'])->render();
            $plainContent = $ctx['plain_body'] !== '' ? $ctx['plain_body'] : 'Honest Art studio invitation — please open the HTML part of this email for full event details and the invitation preview.';
            $subject = $ctx['subject'];

            foreach ($sendable as $i => $email) {
                SendGuestInvitationEmail::dispatch(
                    $email,
                    $mailer['api_key'],
                    $mailer['from_email'],
                    $mailer['from_name'],
                    $subject,
                    $htmlContent,
                    $plainContent,
                    $imageBase64,
                    $invitationRow->image
                )->delay(now()->addSeconds($i * 1));
            }

            $responseMsg = 'Guest list saved. ' . count($sendable) . ' emails queued.';
            if (!empty($skipped)) {
                $responseMsg .= ' ' . count($skipped) . ' skipped (bounced/spam).';
            }

            return response()->json([
                'message' => $responseMsg,
                'status' => 1,
                'guest_data' => $guest,
                'email_stats' => [
                    'queued' => count($sendable),
                    'skipped_bounced' => $skipped,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Guest list was saved but the emails could not be queued',
                'status' => 0,
                'error_message' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Invitation fields are often stored as JSON with a "text" key; normalize to plain string.
     */
    private function invitationPlainFromDbField(?string $raw): ?string
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
        $out = trim(strip_tags($trimmed));

        return $out !== '' ? $out : null;
    }

    /**
     * Transactional-style copy, structured plain text, and a factual subject line.
     *
     * @return array{subject: string, plain_body: string, view: array<string, mixed>}
     */
    private function buildGuestInvitationMailContext(Invitation $invitation, string $imageUrl): array
    {
        $honoree = $this->invitationPlainFromDbField($invitation->honoree_name);
        $occasion = $this->invitationPlainFromDbField($invitation->occasion);
        $rawDate = $this->invitationPlainFromDbField($invitation->date);
        $date = $rawDate;
        if ($rawDate) {
            try {
                $date = \Carbon\Carbon::parse($rawDate)->format('F j, Y');
            } catch (\Throwable $th) {
                $date = $rawDate;
            }
        }
        $time = $this->invitationPlainFromDbField($invitation->time);
        $endTime = $invitation->end_time;
        $hostName = $this->invitationPlainFromDbField($invitation->host_name);
        $hostContact = $this->invitationPlainFromDbField($invitation->host_contact);

        $locationLine = null;
        if ($invitation->relationLoaded('location') && $invitation->location) {
            $loc = $invitation->location;
            $parts = array_filter([$loc->name ?? null, $loc->city ?? null]);
            $locationLine = $parts !== [] ? implode(', ', $parts) : null;
        }

        $detailRows = [];
        if ($locationLine) {
            $detailRows[] = ['label' => 'Studio Location', 'value' => $locationLine];
        }
        if ($honoree) {
            $detailRows[] = ['label' => 'Guest of Honor', 'value' => $honoree];
        }
        if ($occasion) {
            $detailRows[] = ['label' => 'Occasion', 'value' => $occasion];
        }
        if ($date) {
            $detailRows[] = ['label' => 'Date', 'value' => $date];
        }
        if ($time) {
            $detailRows[] = ['label' => 'Start Time', 'value' => $time];
        }
        if ($endTime) {
            $detailRows[] = ['label' => 'End Time', 'value' => $endTime];
        }
        if ($hostName) {
            $detailRows[] = ['label' => 'RSVP Name', 'value' => $hostName];
        }
        if ($hostContact) {
            $detailRows[] = ['label' => 'RSVP Phone', 'value' => $hostContact];
        }

        $subject = 'Honest Art studio invitation';
        $focus = $honoree ?: $occasion;
        if ($focus) {
            $subject = 'Honest Art — ' . Str::limit($focus, 48);
        }

        $preheader = 'Your studio invitation includes event details below and a visual preview.';
        if ($date && $time) {
            $preheader = Str::limit('Details: ' . $date . ' · ' . $time . ($locationLine ? ' · ' . $locationLine : ''), 115);
        } elseif ($date) {
            $preheader = Str::limit('Date: ' . $date . ($locationLine ? ' · ' . $locationLine : ''), 115);
        }

        $plainLines = [
            'HONEST ART — STUDIO INVITATION',
            '',
            'You are receiving this email because a host entered your address to send their Honest Art invitation.',
            'This message is transactional (not a newsletter).',
            '',
        ];
        foreach ($detailRows as $row) {
            $plainLines[] = $row['label'] . ': ' . $row['value'];
        }
        if ($detailRows === []) {
            $plainLines[] = '(No extra event fields were stored with this invitation.)';
            $plainLines[] = '';
        } else {
            $plainLines[] = '';
        }
        $plainLines[] = 'Invitation preview image URL:';
        $plainLines[] = $imageUrl;
        $plainLines[] = '';
        $plainLines[] = 'If you do not recognize this event, you may ignore this email.';
        $plainLines[] = '';
        $plainLines[] = '© ' . date('Y') . ' Honest Art';

        return [
            'subject' => $subject,
            'plain_body' => implode("\n", $plainLines),
            'view' => [
                'imageUrl' => $imageUrl,
                'detailRows' => $detailRows,
                'preheader' => $preheader,
                'hasDetails' => $detailRows !== [],
            ],
        ];
    }

    /**
     * Decrypt Laravel-encrypted secrets stored in admin_mail_settings.
     */
    private function decryptMailSecret(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * SendGrid for guest invites: prefer Admin → Mail (SendGrid API), else .env SENDGRID_API_KEY + MAIL_FROM_*.
     *
     * @return array{api_key: string, from_email: string, from_name: string}|null
     */
    private function resolveGuestInvitationSendGrid(): ?array
    {
        $settings = AdminMailSetting::first();
        if ($settings && $settings->provider === 'sendgrid_api') {
            $key = $this->decryptMailSecret($settings->sendgrid_api_key);
            if ($key && !empty($settings->from_email) && !empty($settings->from_name)) {
                return [
                    'api_key' => $key,
                    'from_email' => $settings->from_email,
                    'from_name' => $settings->from_name,
                ];
            }
        }

        $key = config('services.sendgrid.api_key');
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name') ?: 'Honest Art';

        if ($key && $fromEmail) {
            return [
                'api_key' => $key,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
            ];
        }

        return null;
    }


    public function show($id)
    {
        $guest = Guest::where("invitation_id", $id)->get();
        if (count($guest) > 0) {
            $response = [
                'message' => "Guest Found Successfully",
                'status' => 1,
                'guest_data' => $guest
            ];
        } else {
            $response = [
                'message' => 'No Guest is available against id = ' . $id,
                'status' => 0,
            ];
        }
        return response()->json($response, 200);
    }


    public function destroy($id)
    {
        $guest = Guest::find($id);
        if ($guest) {
            DB::beginTransaction();
            try {
                $guest->delete();
                DB::commit();
                $response = [
                    'message' => 'Guest Delete Successfully',
                    'status' => 1
                ];
                $response_code = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0,
                    'error_message' => $e->getMessage()
                ];
                $response_code = 500;
            }
        } else {
            $response = [
                'message' => 'No Guest Found',
                'status' => 0
            ];
            $response_code = 200;
        }
        return response()->json($response, $response_code);
    }

}
