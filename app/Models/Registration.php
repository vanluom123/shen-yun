<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'event_session_id',
        'full_name',
        'email',
        'phone',
        'adult_count',
        'ntl_count',
        'ntl_new_count',
        'child_count',
        'total_count',
        'attend_with_guest',
        'status',
    ];

    protected $casts = [
        'attend_with_guest' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function eventSession(): BelongsTo
    {
        return $this->belongsTo(EventSession::class);
    }
}
