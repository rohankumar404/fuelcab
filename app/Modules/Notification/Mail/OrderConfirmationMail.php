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

    public ?string $customerName;

    public ?string $orderNumber;

    public ?string $productName;

    public ?float $quantity;

    public ?string $status;

    public ?float $total;

    public ?string $deliveryAddress;

    public ?string $orderId;

    public function __construct(
        ?string $customerName = 'Customer',
        ?string $orderNumber = 'N/A',
        ?string $productName = 'Fuel Product',
        ?float $quantity = 0.0,
        ?string $status = 'Pending',
        ?float $total = 0.0,
        ?string $deliveryAddress = 'N/A',
        ?string $orderId = 'N/A'
    ) {
        $this->customerName = $customerName ?? 'Customer';
        $this->orderNumber = $orderNumber ?? 'N/A';
        $this->productName = $productName ?? 'Fuel Product';
        $this->quantity = (float) ($quantity ?? 0.0);
        $this->status = $status ?? 'Pending';
        $this->total = (float) ($total ?? 0.0);
        $this->deliveryAddress = $deliveryAddress ?? 'N/A';
        $this->orderId = $orderId ?? 'N/A';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmed - '.$this->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
        );
    }
}
