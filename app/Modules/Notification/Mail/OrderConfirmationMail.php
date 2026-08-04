<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $customerName,
        public string $orderNumber,
        public string $productName,
        public float $quantity,
        public string $status,
        public float $total,
        public string $deliveryAddress,
        public string $orderId
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmed - ' . $this->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
        );
    }
}
