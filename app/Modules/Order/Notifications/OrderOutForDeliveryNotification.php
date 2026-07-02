<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderOutForDeliveryNotification extends Notification implements ShouldQueue
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
            ->subject("Your Fuel Is On The Way — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your fuel delivery is now **out for delivery**.")
            ->line("**Order ID:** {$this->order->id}")
            ->line("Please ensure someone is available at the delivery address.")
            ->action('Track Live Location', url("/orders/{$this->order->id}/tracking"))
            ->line('Estimated delivery: within the scheduled window.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'order_out_for_delivery',
            'order_id' => $this->order->id,
            'message'  => "Your fuel order #{$this->order->id} is out for delivery. Track your driver in real-time.",
        ];
    }
}
