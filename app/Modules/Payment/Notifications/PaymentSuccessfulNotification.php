<?php

declare(strict_types=1);

namespace App\Modules\Payment\Notifications;

use App\Modules\Payment\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'fcm'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Confirmed — Transaction ID: " . ($this->payment->gateway_transaction_id ?? $this->payment->id))
            ->greeting("Hello " . ($notifiable->name ?? 'Customer') . "!")
            ->line("We have received your payment of ₹" . number_format((float) $this->payment->amount, 2) . " via {$this->payment->payment_gateway}.")
            ->line("Your transaction reference is: **" . ($this->payment->gateway_transaction_id ?? $this->payment->id) . "**.")
            ->line("This payment has been applied to order #{$this->payment->order_id}.")
            ->action('View Order Details', url("/orders/{$this->payment->order_id}"))
            ->line('Thank you for choosing FuelCab!');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => "Payment Confirmed ✅",
            'body'  => "Your payment of ₹" . number_format((float) $this->payment->amount, 2) . " was successfully verified.",
            'data'  => [
                'type'           => 'payment_verified',
                'payment_id'     => $this->payment->id,
                'order_id'       => $this->payment->order_id,
                'transaction_id' => $this->payment->gateway_transaction_id,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'payment_verified',
            'payment_id'     => $this->payment->id,
            'order_id'       => $this->payment->order_id,
            'transaction_id' => $this->payment->gateway_transaction_id,
            'amount'         => $this->payment->amount,
            'message'        => "Your payment of ₹" . number_format((float) $this->payment->amount, 2) . " has been verified.",
        ];
    }
}
