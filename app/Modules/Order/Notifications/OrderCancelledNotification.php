<?php

declare(strict_types=1);

namespace App\Modules\Order\Notifications;

use App\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order   $order,
        public readonly ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Order Cancelled — #{$this->order->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your fuel order has been **cancelled**.")
            ->line("**Order ID:** {$this->order->id}");

        if ($this->reason) {
            $message->line("**Reason:** {$this->reason}");
        }

        return $message
            ->line("If this was unexpected, please contact our support team.")
            ->action('Contact Support', url('/support'))
            ->line('We apologize for any inconvenience caused.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'order_cancelled',
            'order_id' => $this->order->id,
            'reason'   => $this->reason,
            'message'  => "Your order #{$this->order->id} has been cancelled." . ($this->reason ? " Reason: {$this->reason}" : ''),
        ];
    }
}
