<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\InfobipSmsChannel;
use App\Notifications\Messages\InfobipSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly string $oldStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', InfobipSmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is being processed.',
            'shipped' => 'Your order has been shipped!',
            'out_for_delivery' => 'Your order is out for delivery!',
            'delivered' => 'Your order has been delivered. Enjoy!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $message = $statusMessages[$this->order->status] ?? "Your order status changed to {$this->order->status}.";

        return (new MailMessage)
            ->subject("Order Update - {$this->order->order_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line($message)
            ->line("Order: #{$this->order->order_number}")
            ->action('View Order', url("/orders/{$this->order->id}"))
            ->line('Thank you for shopping with SNACKZAR!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->order->status,
            'message' => "Order #{$this->order->order_number} status changed to {$this->order->status}.",
        ];
    }

    public function toInfobip(object $notifiable): InfobipSmsMessage
    {
        $statusMessages = [
            'confirmed' => 'has been confirmed and is being prepared.',
            'processing' => 'is being processed.',
            'shipped' => 'has been shipped.',
            'out_for_delivery' => 'is out for delivery.',
            'delivered' => 'has been delivered.',
            'cancelled' => 'has been cancelled.',
        ];

        $message = $statusMessages[$this->order->status] ?? "status changed to {$this->order->status}.";

        return new InfobipSmsMessage(
            "Snackzar: Order {$this->order->order_number} {$message}"
        );
    }
}
