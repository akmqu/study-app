<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_file_path',
        'student_answer',
        'status',
        'grade',
        'feedback',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            Assignment::class
        );
    }
}