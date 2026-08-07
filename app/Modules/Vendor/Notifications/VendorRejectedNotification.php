<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Notifications;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Vendor $vendor,
        public readonly ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'fcm'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Vendor Application Status Update")
            ->greeting("Hello " . ($notifiable->name ?? 'Partner') . ",")
            ->line("Unfortunately, your registration request for '{$this->vendor->brand_name}' has been rejected by the admin.");

        if ($this->reason) {
            $mail->line("**Reason for rejection:** {$this->reason}");
        }

        return $mail
            ->action('Contact Support', url('/support'))
            ->line('Please contact support if you have any questions.');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => "Vendor Application Update ⚠️",
            'body'  => "Unfortunately, your registration request for '{$this->vendor->brand_name}' was not approved." . ($this->reason ? " Reason: {$this->reason}" : ''),
            'data'  => [
                'type'      => 'vendor_rejected',
                'vendor_id' => $this->vendor->id,
                'reason'    => $this->reason,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'vendor_rejected',
            'vendor_id' => $this->vendor->id,
            'reason'    => $this->reason,
            'message'   => "Your vendor registration for '{$this->vendor->brand_name}' was not approved." . ($this->reason ? " Reason: {$this->reason}" : ''),
        ];
    }
}
