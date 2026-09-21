<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'tutor_student_id',
        'start_time',
        'end_time',
        'status',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function tutorStudent(): BelongsTo
    {
        return $this->belongsTo(TutorStudent::class);
    }
}