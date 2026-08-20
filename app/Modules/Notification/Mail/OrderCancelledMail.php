<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $customerName;

    public ?string $orderNumber;

    public ?string $productName;

    public ?float $quantity;

    public function __construct(
        ?string $customerName = 'Customer',
        ?string $orderNumber = 'N/A',
        ?string $productName = 'Fuel Product',
        ?float $quantity = 0.0
    ) {
        $this->customerName = $customerName ?? 'Customer';
        $this->orderNumber = $orderNumber ?? 'N/A';
        $this->productName = $productName ?? 'Fuel Product';
        $this->quantity = (float) ($quantity ?? 0.0);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Cancelled - '.$this->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled',
        );
    }
}
