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
        'status',
        'is_registration_blocked',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'is_registration_blocked' => 'boolean',
    ];

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
