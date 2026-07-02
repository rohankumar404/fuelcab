<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverAssignedNotification extends Notification implements ShouldQueue
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
        $driver = $this->order->driver;

        return (new MailMessage)
            ->subject("Driver Assigned — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A driver has been assigned to your fuel order.")
            ->line("**Driver Name:** " . ($driver?->name ?? 'N/A'))
            ->line("**Order ID:** {$this->order->id}")
            ->action('Track Your Order', url("/orders/{$this->order->id}"))
            ->line('Your delivery is on its way!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'driver_assigned',
            'order_id'  => $this->order->id,
            'driver_id' => $this->order->driver_id,
            'message'   => "A driver has been assigned to your order #{$this->order->id}.",
        ];
    }
}
