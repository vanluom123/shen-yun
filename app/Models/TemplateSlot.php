<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_template_id',
        'day_of_week',
        'time',
        'default_capacity',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'default_capacity' => 'integer',
    ];

    public function sessionTemplate(): BelongsTo
    {
        return $this->belongsTo(SessionTemplate::class);
    }

    public static function rules(): array
    {
        return [
            'session_template_id' => 'required|exists:session_templates,id',
            'day_of_week' => 'required|integer|between:0,6',
            'time' => 'required|date_format:H:i',
            'default_capacity' => 'required|integer|min:1',
        ];
    }
}
