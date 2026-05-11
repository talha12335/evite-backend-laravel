<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AdminMailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AdminMailSettingsController extends Controller
{
    private function getOrCreateSettings()
    {
        return AdminMailSetting::firstOrCreate(
            ['id' => 1],
            ['provider' => 'sendgrid_api']
        );
    }

    private function decryptValue($value)
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

    private function encryptValue($value)
    {
        if (!$value) {
            return null;
        }
        return Crypt::encryptString($value);
    }

    private function serializeForResponse(AdminMailSetting $settings)
    {
        return [
            'provider' => $settings->provider ?: 'sendgrid_api',
            'sendgrid_api_key' => $this->decryptValue($settings->sendgrid_api_key),
            'smtp_host' => $settings->smtp_host,
            'smtp_port' => $settings->smtp_port,
            'smtp_encryption' => $settings->smtp_encryption,
            'smtp_username' => $settings->smtp_username,
            'smtp_password' => $this->decryptValue($settings->smtp_password),
            'from_email' => $settings->from_email,
            'from_name' => $settings->from_name,
        ];
    }

    public function show()
    {
        $settings = $this->getOrCreateSettings();
        return response()->json([
            'status' => 1,
            'data' => $this->serializeForResponse($settings),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:sendgrid_api,smtp',
            'sendgrid_api_key' => 'nullable|string',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:tls,ssl,null,none,',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'from_email' => 'required|email:rfc,dns|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        if ($validated['provider'] === 'sendgrid_api' && empty($validated['sendgrid_api_key'])) {
            throw ValidationException::withMessages([
                'sendgrid_api_key' => ['SendGrid API key is required for SendGrid API mode.'],
            ]);
        }

        if ($validated['provider'] === 'smtp') {
            foreach (['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password'] as $field) {
                if (empty($validated[$field])) {
                    throw ValidationException::withMessages([
                        $field => ['This field is required for SMTP mode.'],
                    ]);
                }
            }
        }

        $settings = $this->getOrCreateSettings();
        $settings->provider = $validated['provider'];
        $settings->sendgrid_api_key = $this->encryptValue($validated['sendgrid_api_key'] ?? null);
        $settings->smtp_host = $validated['smtp_host'] ?? null;
        $settings->smtp_port = $validated['smtp_port'] ?? null;
        $settings->smtp_encryption = $validated['smtp_encryption'] ?? null;
        $settings->smtp_username = $validated['smtp_username'] ?? null;
        $settings->smtp_password = $this->encryptValue($validated['smtp_password'] ?? null);
        $settings->from_email = $validated['from_email'];
        $settings->from_name = $validated['from_name'];
        $settings->save();

        return response()->json([
            'status' => 1,
            'message' => 'Mail settings saved successfully.',
            'data' => $this->serializeForResponse($settings),
        ]);
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|email:rfc,dns|max:255',
        ]);

        $settings = $this->getOrCreateSettings();
        $recipient = $validated['recipient'];
        $subject = 'Admin Mail Settings Test';
        $body = 'This is a test email from Honest Art admin settings.';

        try {
            if ($settings->provider === 'smtp') {
                $smtpPassword = $this->decryptValue($settings->smtp_password);
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $settings->smtp_host,
                    'mail.mailers.smtp.port' => (int) $settings->smtp_port,
                    'mail.mailers.smtp.encryption' => in_array($settings->smtp_encryption, ['none', '', null], true)
                        ? null
                        : $settings->smtp_encryption,
                    'mail.mailers.smtp.username' => $settings->smtp_username,
                    'mail.mailers.smtp.password' => $smtpPassword,
                    'mail.from.address' => $settings->from_email,
                    'mail.from.name' => $settings->from_name,
                ]);

                Mail::mailer('smtp')->raw($body, function ($message) use ($recipient, $subject) {
                    $message->to($recipient)->subject($subject);
                });
            } else {
                $apiKey = $this->decryptValue($settings->sendgrid_api_key);
                if (!$apiKey) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'SendGrid API key is not configured.',
                    ], 422);
                }

                $res = Http::withToken($apiKey)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post('https://api.sendgrid.com/v3/mail/send', [
                        'personalizations' => [[
                            'to' => [[
                                'email' => $recipient,
                            ]],
                        ]],
                        'from' => [
                            'email' => $settings->from_email,
                            'name' => $settings->from_name,
                        ],
                        'subject' => $subject,
                        'content' => [[
                            'type' => 'text/plain',
                            'value' => $body,
                        ]],
                    ]);

                if (!$res->successful()) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'SendGrid test failed.',
                        'details' => $res->body(),
                    ], 422);
                }
            }

            return response()->json([
                'status' => 1,
                'message' => "Test email sent successfully to {$recipient}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Test connection failed.',
                'details' => $e->getMessage(),
            ], 422);
        }
    }
}

