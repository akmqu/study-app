<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['tutor_student_id', 'amount', 'period', 'status', 'payment_method'];

    public function tutorStudent() { return $this->belongsTo(TutorStudent::class); }
}
