<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $customerName;

    public ?string $orderNumber;

    public ?string $productName;

    public ?float $quantity;

    public ?float $unitPrice;

    public ?float $subtotal;

    public ?float $tax;

    public ?float $deliveryFee;

    public ?float $total;

    public ?string $paymentMethod;

    public ?string $orderId;

    public ?string $pdfPath;

    public function __construct(
        ?string $customerName = 'Customer',
        ?string $orderNumber = 'N/A',
        ?string $productName = 'Fuel Product',
        ?float $quantity = 0.0,
        ?float $unitPrice = 0.0,
        ?float $subtotal = 0.0,
        ?float $tax = 0.0,
        ?float $deliveryFee = 0.0,
        ?float $total = 0.0,
        ?string $paymentMethod = 'Online',
        ?string $orderId = 'N/A',
        ?string $pdfPath = null
    ) {
        $this->customerName = $customerName ?? 'Customer';
        $this->orderNumber = $orderNumber ?? 'N/A';
        $this->productName = $productName ?? 'Fuel Product';
        $this->quantity = (float) ($quantity ?? 0.0);
        $this->unitPrice = (float) ($unitPrice ?? 0.0);
        $this->subtotal = (float) ($subtotal ?? 0.0);
        $this->tax = (float) ($tax ?? 0.0);
        $this->deliveryFee = (float) ($deliveryFee ?? 0.0);
        $this->total = (float) ($total ?? 0.0);
        $this->paymentMethod = $paymentMethod ?? 'Online';
        $this->orderId = $orderId ?? 'N/A';
        $this->pdfPath = $pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tax Invoice / Receipt - '.$this->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as('invoice-'.$this->orderId.'.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
