<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSession extends Model
{
    protected $fillable = [
        'venue_id',
        'starts_at',
        'capacity_total',
        'capacity_reserved',
        'registration_status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function isOpen(): bool
    {
        return $this->registration_status === 'open';
    }

    public function isPaused(): bool
    {
        return $this->registration_status === 'paused';
    }

    public function isHidden(): bool
    {
        return $this->registration_status === 'hidden';
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public static function recalculateReserved(int $sessionId): void
    {
        $reserved = Registration::query()
            ->where('event_session_id', $sessionId)
            ->where('status', 'confirmed')
            ->sum('total_count');

        self::query()->whereKey($sessionId)->update(['capacity_reserved' => $reserved]);
    }
}
