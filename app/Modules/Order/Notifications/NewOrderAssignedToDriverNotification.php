<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAssignedToDriverNotification extends Notification implements ShouldQueue
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
            ->subject("New Delivery Assignment — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been assigned a new fuel delivery order.")
            ->line("**Order ID:** {$this->order->id}")
            ->line("**Delivery Address:** " . optional($this->order->deliveryAddress)->address_line_1)
            ->line("**Total Fuel:** {$this->order->items_count} items")
            ->action('View Delivery Details', url("/driver/orders/{$this->order->id}"))
            ->line('Please proceed to the vendor for pickup.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'driver_new_order',
            'order_id' => $this->order->id,
            'message'  => "You have been assigned delivery order #{$this->order->id}.",
        ];
    }
}
