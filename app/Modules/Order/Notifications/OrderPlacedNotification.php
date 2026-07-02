<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
            ->subject("Order Confirmed — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your fuel order has been placed successfully.")
            ->line("**Order ID:** {$this->order->id}")
            ->line("**Total Amount:** ₹" . number_format($this->order->total_amount, 2))
            ->line("**Scheduled Delivery:** " . ($this->order->scheduled_delivery_at?->format('d M Y, h:i A') ?? 'As soon as possible'))
            ->action('View Order', url("/orders/{$this->order->id}"))
            ->line('Thank you for using FuelCab!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'order_placed',
            'order_id'   => $this->order->id,
            'message'    => "Your order #{$this->order->id} has been placed successfully.",
            'total'      => $this->order->total_amount,
        ];
    }
}
