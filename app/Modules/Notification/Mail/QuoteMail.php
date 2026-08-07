<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $type;
    public ?string $productName;
    public ?float $quantity;
    public ?string $deliveryDate;
    public ?float $price;
    public ?string $unit;
    public ?float $minQty;
    public ?string $validity;
    public ?string $dispatch;
    public ?string $terms;

    public function __construct(
        ?string $type = 'request',
        ?string $productName = 'Bulk Fuel',
        ?float $quantity = 0.0,
        ?string $deliveryDate = null,
        ?float $price = null,
        ?string $unit = null,
        ?float $minQty = null,
        ?string $validity = null,
        ?string $dispatch = null,
        ?string $terms = null
    ) {
        $this->type         = $type ?? 'request';
        $this->productName  = $productName ?? 'Bulk Fuel';
        $this->quantity     = (float) ($quantity ?? 0.0);
        $this->deliveryDate = $deliveryDate;
        $this->price        = $price !== null ? (float) $price : null;
        $this->unit         = $unit;
        $this->minQty       = $minQty !== null ? (float) $minQty : null;
        $this->validity     = $validity;
        $this->dispatch     = $dispatch;
        $this->terms        = $terms;
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'request'
            ? 'New Bulk Fuel Quote Request'
            : 'Quotation Received - B2B Bulk Fuel';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote',
        );
    }
}
