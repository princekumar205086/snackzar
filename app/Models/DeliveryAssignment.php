<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_profile_id',
        'status',
        'notes',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
        'delivery_proof',
        'earning',
    ];

    protected function casts(): array
    {
        return [
            'earning' => 'decimal:2',
            'accepted_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryProfile(): BelongsTo
    {
        return $this->belongsTo(DeliveryProfile::class);
    }
}
