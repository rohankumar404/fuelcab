<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderAcceptedNotification extends Notification implements ShouldQueue
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
            ->subject("Order Accepted — #{$this->order->id}")
            ->greeting("Great news, {$notifiable->name}!")
            ->line("Your fuel order has been accepted by the vendor.")
            ->line("**Order ID:** {$this->order->id}")
            ->line("A driver will be assigned to your order shortly.")
            ->action('Track Order', url("/orders/{$this->order->id}"))
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'order_accepted',
            'order_id' => $this->order->id,
            'message'  => "Your order #{$this->order->id} has been accepted. A driver will be assigned shortly.",
        ];
    }
}
