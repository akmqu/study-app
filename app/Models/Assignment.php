<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    protected $fillable = [
        'tutor_student_id',
        'title',
        'instructions',
        'file_path',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function tutorStudent(): BelongsTo
    {
        return $this->belongsTo(TutorStudent::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            AssignmentAttachment::class,
            'assignment_id'
        );
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(
            Submission::class,
            'assignment_id'
        );
    }

    public function latestSubmission(): HasOne
    {
        return $this
            ->hasOne(
                Submission::class,
                'assignment_id'
            )
            ->latestOfMany();
    }
}