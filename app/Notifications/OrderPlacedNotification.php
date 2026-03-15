<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\InfobipSmsChannel;
use App\Notifications\Messages\InfobipSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', InfobipSmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order Confirmed - {$this->order->order_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your order #{$this->order->order_number} has been placed successfully.")
            ->line("Total: ₹{$this->order->total}")
            ->action('View Order', url("/orders/{$this->order->id}"))
            ->line('Thank you for shopping with SNACKZAR!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'message' => "Order #{$this->order->order_number} placed successfully.",
        ];
    }

    public function toInfobip(object $notifiable): InfobipSmsMessage
    {
        return new InfobipSmsMessage(
            "Snackzar: Your order {$this->order->order_number} is confirmed. Total amount: INR {$this->order->total}. Track it from your account."
        );
    }
}
