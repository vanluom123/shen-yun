<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'venue_id',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TemplateSlot::class);
    }

    public static function rules(): array
    {
        return [
            'venue_id' => 'required|exists:venues,id|unique:session_templates,venue_id',
        ];
    }
}
