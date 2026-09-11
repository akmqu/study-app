<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['tutor_student_id', 'start_time', 'status'];

    public function tutorStudent() { return $this->belongsTo(TutorStudent::class); }
}
