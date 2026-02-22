<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    const ADMIN_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_ACCEPTED, self::STATUS_REJECTED],
    ];

    const USER_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CANCELLED],
    ];

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canUserCancel(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canAdminTransitionTo(string $newStatus): bool
    {
        $allowed = self::ADMIN_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }
}