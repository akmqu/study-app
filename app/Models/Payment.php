<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'tutor_student_id',
        'amount',
        'currency',
        'period',
        'billing_type',
        'period_start',
        'period_end',
        'lesson_count',
        'status',
        'payment_method',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' =>
                'decimal:2',

            'period_start' =>
                'date',

            'period_end' =>
                'date',

            'lesson_count' =>
                'integer',

            'paid_at' =>
                'datetime',
        ];
    }

    public function tutorStudent(): BelongsTo
    {
        return $this->belongsTo(
            TutorStudent::class
        );
    }
}