<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'scope', 'type', 'value', 'max_discount', 'min_order_amount',
        'max_uses', 'max_uses_per_user', 'used_count', 'expires_at',
        'is_active', 'description', 'label', 'prefix', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'max_discount'     => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active'        => 'boolean',
            'expires_at'       => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /** Individual coupon owner (scope=individual) */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Users assigned to this coupon (scope=bulk|enterprise) */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_users')
            ->withPivot(['used_count', 'is_active', 'assigned_at']);
    }

    // ── Validity ─────────────────────────────────────────────────────────────

    /**
     * Check if the coupon is globally valid (no user context).
     */
    public function isValid(float $orderAmount = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) return false;
        if ($orderAmount < $this->min_order_amount) return false;
        return true;
    }

    /**
     * Check if the coupon is valid for a specific user.
     * – public coupons: accessible to everyone
     * – individual: only the assigned user_id
     * – bulk/enterprise: user must be in coupon_users pivot and is_active=true
     */
    public function isValidForUser(User $user, float $orderAmount = 0): bool
    {
        if (!$this->isValid($orderAmount)) return false;

        if ($this->scope === 'public') {
            return true;
        }

        if ($this->scope === 'individual') {
            return $this->user_id === $user->id;
        }

        // bulk / enterprise — check pivot
        $pivot = $this->assignedUsers()
            ->where('user_id', $user->id)
            ->first()?->pivot;

        if (!$pivot || !$pivot->is_active) return false;

        // Per-user usage limit
        if ($this->max_uses_per_user > 0 && $pivot->used_count >= $this->max_uses_per_user) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->type === 'percent') {
            $discount = $orderAmount * ($this->value / 100);
            if ($this->max_discount) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            $discount = min((float) $this->value, $orderAmount);
        }
        return round($discount, 2);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('scope', 'public');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('scope', 'public')
              ->orWhere(fn ($q2) => $q2->where('scope', 'individual')->where('user_id', $userId))
              ->orWhereHas('assignedUsers', fn ($q2) => $q2->where('user_id', $userId)->where('coupon_users.is_active', true));
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function generateCode(string $prefix = 'SNACK', int $length = 6): string
    {
        do {
            $suffix = strtoupper(substr(bin2hex(random_bytes(4)), 0, $length));
            $code   = $prefix . $suffix;
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
