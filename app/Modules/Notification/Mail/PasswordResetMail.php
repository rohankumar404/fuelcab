<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $name;
    public ?string $otp;
    public ?int $expiry;

    public function __construct(
        ?string $name = 'User',
        ?string $otp = '000000',
        ?int $expiry = 10
    ) {
        $this->name   = $name ?? 'User';
        $this->otp    = $otp ?? '000000';
        $this->expiry = $expiry ?? 10;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
        );
    }
}
