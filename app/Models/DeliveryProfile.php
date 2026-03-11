<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'aadhar_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'upi_id',
        'is_available',
        'current_latitude',
        'current_longitude',
        'total_earnings',
        'pending_payout',
        'total_deliveries',
        'avg_rating',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'total_earnings' => 'decimal:2',
            'pending_payout' => 'decimal:2',
            'avg_rating' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
