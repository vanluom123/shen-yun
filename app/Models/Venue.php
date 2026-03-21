<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venue extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'timezone',
    ];

    public function eventSessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    public function sessionTemplate(): HasOne
    {
        return $this->hasOne(SessionTemplate::class);
    }
}
