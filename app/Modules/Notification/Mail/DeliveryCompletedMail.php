<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryCompletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $customerName;

    public ?string $orderNumber;

    public ?string $productName;

    public ?float $quantity;

    public ?string $driverName;

    public ?string $licensePlate;

    public ?string $completedAt;

    public ?string $orderId;

    public function __construct(
        ?string $customerName = 'Customer',
        ?string $orderNumber = 'N/A',
        ?string $productName = 'Fuel Product',
        ?float $quantity = 0.0,
        ?string $driverName = 'Driver',
        ?string $licensePlate = 'N/A',
        ?string $completedAt = 'N/A',
        ?string $orderId = 'N/A'
    ) {
        $this->customerName = $customerName ?? 'Customer';
        $this->orderNumber = $orderNumber ?? 'N/A';
        $this->productName = $productName ?? 'Fuel Product';
        $this->quantity = (float) ($quantity ?? 0.0);
        $this->driverName = $driverName ?? 'Driver';
        $this->licensePlate = $licensePlate ?? 'N/A';
        $this->completedAt = $completedAt ?? 'N/A';
        $this->orderId = $orderId ?? 'N/A';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Delivery Completed for Order '.$this->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.delivery_completed',
        );
    }
}
