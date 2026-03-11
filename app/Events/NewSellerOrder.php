<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSellerOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sellerId,
        public readonly string $orderNumber,
        public readonly int $orderItemId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("seller.{$this->sellerId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'seller_id' => $this->sellerId,
            'order_number' => $this->orderNumber,
            'order_item_id' => $this->orderItemId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'seller.new.order';
    }
}
