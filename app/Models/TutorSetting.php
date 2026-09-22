<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorSetting extends Model
{
    protected $fillable = [
        'tutor_id',
        'default_lesson_duration',
        'lesson_reminders_enabled',
        'lesson_reminder_minutes',
        'default_billing_type',
    ];

    protected function casts(): array
    {
        return [
            'lesson_reminders_enabled' => 'boolean',
            'default_lesson_duration' => 'integer',
            'lesson_reminder_minutes' => 'integer',
        ];
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'tutor_id'
        );
    }
}