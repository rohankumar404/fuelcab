<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Delivery Complete — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your fuel order has been **delivered successfully**.")
            ->line("**Order ID:** {$this->order->id}")
            ->line("**Total Paid:** ₹" . number_format($this->order->total_amount, 2))
            ->line("**Delivered At:** " . ($this->order->delivered_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A')))
            ->action('View Invoice', url("/orders/{$this->order->id}/invoice"))
            ->line('Thank you for choosing FuelCab. We look forward to serving you again!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'order_delivered',
            'order_id'     => $this->order->id,
            'message'      => "Your fuel order #{$this->order->id} has been delivered. Thank you!",
            'delivered_at' => $this->order->delivered_at,
        ];
    }
}
