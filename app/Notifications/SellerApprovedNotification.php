<?php

namespace App\Notifications;

use App\Models\SellerProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly SellerProfile $profile
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seller Account Approved - SNACKZAR')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your seller account has been approved.')
            ->line("Business: {$this->profile->business_name}")
            ->action('Go to Seller Dashboard', url('/seller/dashboard'))
            ->line('You can now start listing your products!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'seller_approved',
            'profile_id' => $this->profile->id,
            'message' => 'Your seller account has been approved.',
        ];
    }
}
