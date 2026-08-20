<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $name;

    public ?string $role;

    public function __construct(
        ?string $name = 'User',
        ?string $role = 'customer'
    ) {
        $this->name = $name ?? 'User';
        $this->role = $role ?? 'customer';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to FuelCab!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }
}
