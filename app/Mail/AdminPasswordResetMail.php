<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject('Reset your Honest Art admin password')
            ->view('admin.emails.password_reset')
            ->text('admin.emails.password_reset_plain')
            ->with('details', $this->details);
    }
}
