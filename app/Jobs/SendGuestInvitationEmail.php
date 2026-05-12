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
    private ?string $imageBase64;
    private ?string $imageFilename;

    public function __construct(
        string $email,
        string $apiKey,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $plainContent,
        ?string $imageBase64 = null,
        ?string $imageFilename = null
    ) {
        $this->email = $email;
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->plainContent = $plainContent;
        $this->imageBase64 = $imageBase64;
        $this->imageFilename = $imageFilename;
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

        $html = $this->htmlContent;
        if ($this->imageBase64) {
            $html = str_replace('src="cid:invitation-preview"', 'src="cid:invitation-preview"', $html);
        }

        $payload = [
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
                ['type' => 'text/html', 'value' => $html],
            ],
            'headers' => [
                'List-Unsubscribe' => '<mailto:' . $this->fromEmail . '?subject=unsubscribe>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'X-Priority' => '3',
            ],
            'tracking_settings' => [
                'open_tracking' => ['enable' => false],
                'click_tracking' => ['enable' => false],
            ],
        ];

        if ($this->imageBase64) {
            $ext = pathinfo($this->imageFilename ?? 'invitation.png', PATHINFO_EXTENSION) ?: 'png';
            $mimeType = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png';

            $payload['attachments'] = [[
                'content' => $this->imageBase64,
                'type' => $mimeType,
                'filename' => 'invitation.' . $ext,
                'disposition' => 'inline',
                'content_id' => 'invitation-preview',
            ]];
        }

        $response = Http::withToken($this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post('https://api.sendgrid.com/v3/mail/send', $payload);

        if (!$response->successful()) {
            $body = $response->body();
            Log::error('SendGrid failed for ' . $this->email . ': ' . $body);
            throw new \Exception('SendGrid HTTP ' . $response->status() . ': ' . $body);
        }

        Log::info('Email sent to ' . $this->email);
    }
}
