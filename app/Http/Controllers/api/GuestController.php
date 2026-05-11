<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AdminMailSetting;
use App\Models\Guest;
use App\Models\Invitation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

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

            $invitationRow = Invitation::select('image', 'honoree_name')
                ->where('id', $request->id)
                ->first();

            if (!$invitationRow) {
                throw new Exception('Invitation not found');
            }

            if (empty($invitationRow->image)) {
                throw new Exception('Invitation has no image yet; save your invite before emailing guests.');
            }

            $invitationImageUrl = url('uploads/' . $invitationRow->image);

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

        $introLine = 'Here is your invitation from Honest Art.';
        if (is_string($invitationRow->honoree_name) && $invitationRow->honoree_name !== '') {
            $decoded = json_decode($invitationRow->honoree_name, true);
            if (is_array($decoded) && !empty($decoded['text'])) {
                $introLine = 'Join us in celebrating ' . strip_tags($decoded['text']) . '!';
            }
        }

        try {
            $htmlContent = View::make('emails.guest_invitation', [
                'imageUrl' => $invitationImageUrl,
                'introLine' => $introLine,
            ])->render();

            $plainContent = trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlContent))));

            $toList = [];
            foreach ($guest_emails as $email) {
                $toList[] = ['email' => $email];
            }

            $subject = "You're invited — Honest Art";

            $sendGridResponse = Http::withToken($mailer['api_key'])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post('https://api.sendgrid.com/v3/mail/send', [
                    'personalizations' => [[
                        'to' => $toList,
                    ]],
                    'from' => [
                        'email' => $mailer['from_email'],
                        'name' => $mailer['from_name'],
                    ],
                    'subject' => $subject,
                    'content' => [
                        ['type' => 'text/plain', 'value' => $plainContent !== '' ? $plainContent : $introLine],
                        ['type' => 'text/html', 'value' => $htmlContent],
                    ],
                ]);

            if (!$sendGridResponse->successful()) {
                $body = $sendGridResponse->body();
                $decodedErr = json_decode($body, true);
                $msg = $body;
                if (is_array($decodedErr)) {
                    if (!empty($decodedErr['errors'][0]['message'])) {
                        $msg = $decodedErr['errors'][0]['message'];
                    } elseif (!empty($decodedErr['message'])) {
                        $msg = is_string($decodedErr['message']) ? $decodedErr['message'] : json_encode($decodedErr['message']);
                    }
                }
                throw new Exception('SendGrid HTTP ' . $sendGridResponse->status() . ': ' . $msg);
            }

            return response()->json([
                'message' => 'Guest list saved and invitation email sent',
                'status' => 1,
                'guest_data' => $guest,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Guest list was saved but the email could not be sent',
                'status' => 0,
                'error_message' => $e->getMessage(),
            ], 502);
        }
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
