<?php

namespace App\Jobs;

use App\Models\EmailEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGuestInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    private string $email;
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private string $subject;
    private string $htmlContent;
    private string $plainContent;

    public function __construct(
        string $email,
        string $apiKey,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $plainContent
    ) {
        $this->email = $email;
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->plainContent = $plainContent;
    }

    public function handle()
    {
        $bounced = EmailEvent::where('email', strtolower(trim($this->email)))
            ->whereIn('event_type', ['bounce', 'dropped', 'spamreport'])
            ->exists();

        if ($bounced) {
            Log::info('Skipping email to ' . $this->email . ' (previously bounced/spam)');
            return;
        }

        $response = Http::withToken($this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post('https://api.sendgrid.com/v3/mail/send', [
                'personalizations' => [[
                    'to' => [['email' => $this->email]],
                ]],
                'from' => [
                    'email' => $this->fromEmail,
                    'name' => $this->fromName,
                ],
                'reply_to' => [
                    'email' => $this->fromEmail,
                    'name' => $this->fromName,
                ],
                'subject' => $this->subject,
                'content' => [
                    ['type' => 'text/plain', 'value' => $this->plainContent],
                    ['type' => 'text/html', 'value' => $this->htmlContent],
                ],
                'headers' => [
                    'List-Unsubscribe' => '<mailto:' . $this->fromEmail . '?subject=unsubscribe>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                    'X-Priority' => '3',
                ],
                'tracking_settings' => [
                    'open_tracking' => ['enable' => true],
                    'click_tracking' => ['enable' => false],
                ],
            ]);

        if (!$response->successful()) {
            $body = $response->body();
            Log::error('SendGrid failed for ' . $this->email . ': ' . $body);
            throw new \Exception('SendGrid HTTP ' . $response->status() . ': ' . $body);
        }

        Log::info('Email sent to ' . $this->email);
    }
}
