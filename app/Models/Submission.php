<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['assignment_id', 'student_file_path', 'status', 'grade', 'feedback'];

    public function assignment() { return $this->belongsTo(Assignment::class); }
}
