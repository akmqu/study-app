<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tutorStudents(): HasMany
    {
        return $this->hasMany(TutorStudent::class, 'tutor_id');
    }

    public function studentTutors(): HasMany
    {
        return $this->hasMany(TutorStudent::class, 'student_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tutor_student',
            'tutor_id',
            'student_id'
        )
            ->withPivot([
                'private_notes',
                'subject',
                'lesson_price',
                'billing_type',
            ])
            ->withTimestamps();
    }

    public function tutors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tutor_student',
            'student_id',
            'tutor_id'
        )
            ->withPivot([
                'private_notes',
                'subject',
                'lesson_price',
                'billing_type',
            ])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'tutor_id');
    }
}