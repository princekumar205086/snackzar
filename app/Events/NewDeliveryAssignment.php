<?php

namespace App\Events;

use App\Models\DeliveryAssignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDeliveryAssignment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly DeliveryAssignment $assignment
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("delivery.{$this->assignment->deliveryProfile->user_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'order_id' => $this->assignment->order_id,
            'earning' => (float) $this->assignment->earning,
        ];
    }

    public function broadcastAs(): string
    {
        return 'delivery.new.assignment';
    }
}
