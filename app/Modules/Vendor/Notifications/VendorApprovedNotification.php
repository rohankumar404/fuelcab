<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Notifications;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Vendor $vendor
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'fcm'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vendor Approved — Welcome to FuelCab!')
            ->greeting('Hello '.($notifiable->name ?? 'Partner').'!')
            ->line("Congratulations! Your registration request for '{$this->vendor->brand_name}' has been approved by the admin.")
            ->line("Your unique vendor code is: **{$this->vendor->vendor_code}**.")
            ->action('Login to Vendor Portal', url('/vendor/login'))
            ->line('We look forward to a successful partnership!');
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Vendor Account Approved 🎉',
            'body' => "Your registration request for '{$this->vendor->brand_name}' has been approved! You can now access the vendor portal.",
            'data' => [
                'type' => 'vendor_approved',
                'vendor_id' => $this->vendor->id,
                'vendor_code' => $this->vendor->vendor_code,
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vendor_approved',
            'vendor_id' => $this->vendor->id,
            'vendor_code' => $this->vendor->vendor_code,
            'message' => "Your vendor registration for '{$this->vendor->brand_name}' has been approved.",
        ];
    }
}
