<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['tutor_id', 'code', 'subject', 'status'];

    public function tutor() { return $this->belongsTo(User::class, 'tutor_id'); }
}
