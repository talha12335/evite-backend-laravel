<?php

namespace App\Services;

use App\Models\AdminMailSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends admin transactional mail using the same SendGrid/SMTP settings
 * as Admin → Mail settings (DB). Falls back to Laravel .env mail when unset.
 */
class AdminTransactionalMailService
{
    private function defaultSettings(): AdminMailSetting
    {
        return AdminMailSetting::firstOrCreate(
            ['id' => 1],
            ['provider' => 'sendgrid_api']
        );
    }

    private function decrypt(?string $value): ?string
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

    private function sendgridConfigured(AdminMailSetting $s): bool
    {
        return (bool) $this->decrypt($s->sendgrid_api_key)
            && !empty($s->from_email)
            && !empty($s->from_name);
    }

    private function smtpConfigured(AdminMailSetting $s): bool
    {
        return $s->smtp_host
            && $s->smtp_port
            && $s->smtp_username
            && $s->smtp_password
            && !empty($s->from_email)
            && !empty($s->from_name);
    }

    private function applySmtpConfig(AdminMailSetting $settings): void
    {
        $smtpPassword = $this->decrypt($settings->smtp_password);
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
    }

    /**
     * @param  array<string, mixed>  $details  Passed to password_reset views as "details"
     *
     * @throws \Throwable
     */
    public function sendPasswordReset(string $to, string $subject, array $details): void
    {
        $settings = $this->defaultSettings();

        if ($settings->provider === 'sendgrid_api' && $this->sendgridConfigured($settings)) {
            $this->sendViaSendGridApi($to, $subject, $details, $settings);

            return;
        }

        if ($settings->provider === 'smtp' && $this->smtpConfigured($settings)) {
            $this->applySmtpConfig($settings);
            $this->sendViaLaravelViews($to, $subject, $details);

            return;
        }

        $this->sendViaLaravelViews($to, $subject, $details);
    }

    /**
     * @param  array<string, mixed>  $details
     *
     * @throws \RuntimeException
     */
    private function sendViaSendGridApi(string $to, string $subject, array $details, AdminMailSetting $settings): void
    {
        $apiKey = $this->decrypt($settings->sendgrid_api_key);
        $html = view('admin.emails.password_reset', ['details' => $details])->render();
        $text = view('admin.emails.password_reset_plain', ['details' => $details])->render();

        $res = Http::withToken($apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post('https://api.sendgrid.com/v3/mail/send', [
                'personalizations' => [[
                    'to' => [['email' => $to]],
                ]],
                'from' => [
                    'email' => $settings->from_email,
                    'name' => $settings->from_name,
                ],
                'subject' => $subject,
                'content' => [
                    ['type' => 'text/plain', 'value' => $text],
                    ['type' => 'text/html', 'value' => $html],
                ],
            ]);

        if (!$res->successful()) {
            Log::warning('SendGrid password reset failed.', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            throw new \RuntimeException('SendGrid rejected the message.');
        }
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function sendViaLaravelViews(string $to, string $subject, array $details): void
    {
        $html = view('admin.emails.password_reset', ['details' => $details])->render();
        $text = view('admin.emails.password_reset_plain', ['details' => $details])->render();

        Mail::send([], [], function ($message) use ($to, $subject, $html, $text) {
            $message->to($to)->subject($subject);
            $message->setBody($html, 'text/html');
            $message->addPart($text, 'text/plain');
        });
    }
}
