<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public const SUBJECT_LINE = 'Reset your Honest Art admin password';

    public array $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject(self::SUBJECT_LINE)
            ->view('admin.emails.password_reset')
            ->text('admin.emails.password_reset_plain')
            ->with('details', $this->details);
    }
}
