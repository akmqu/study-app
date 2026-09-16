<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorStudent extends Model
{
    protected $table = 'tutor_student';
    protected $fillable = ['tutor_id', 'student_id', 'subject', 'lesson_price', 'billing_type', 'private_notes'];

    public function tutor() { return $this->belongsTo(User::class, 'tutor_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function lessons() { return $this->hasMany(Lesson::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
