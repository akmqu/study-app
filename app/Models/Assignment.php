<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['tutor_student_id', 'title', 'instructions', 'file_path', 'deadline'];

    public function tutorStudent() { return $this->belongsTo(TutorStudent::class); }
    public function submissions() { return $this->hasMany(Submission::class); }
}
