<?php

namespace App\Notifications;

use App\Models\DeliveryAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly DeliveryAssignment $assignment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Delivery Assignment - SNACKZAR')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You have a new delivery assignment.')
            ->line("Order: #{$this->assignment->order->order_number}")
            ->line("Earning: ₹{$this->assignment->earning}")
            ->action('View Assignment', url('/delivery/assignments'))
            ->line('Please accept the assignment soon!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'delivery_assigned',
            'assignment_id' => $this->assignment->id,
            'order_id' => $this->assignment->order_id,
            'earning' => $this->assignment->earning,
            'message' => 'New delivery assignment received.',
        ];
    }
}
