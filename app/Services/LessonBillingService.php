<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LessonBillingService
{
    public function complete(
        Lesson $lesson
    ): Lesson {
        $completedLesson =
            DB::transaction(
                function () use (
                    $lesson
                ): Lesson {
                    $lockedLesson =
                        Lesson::query()
                            ->with(
                                'tutorStudent'
                            )
                            ->lockForUpdate()
                            ->findOrFail(
                                $lesson->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Cancelled lessons never become billable
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedLesson
                            ->status ===
                        'cancelled'
                    ) {
                        return
                            $lockedLesson;
                    }

                    $relationship =
                        $lockedLesson
                            ->tutorStudent;

                    if (! $relationship) {
                        return
                            $lockedLesson;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Snapshot current financial settings
                    |--------------------------------------------------------------------------
                    |
                    | We only write these values once.
                    |
                    | If the tutor changes lesson_price or billing_type later,
                    | this completed lesson keeps its original values.
                    |
                    */

                    if (
                        $lockedLesson
                            ->billing_amount ===
                        null
                    ) {
                        $lockedLesson
                            ->billing_amount =
                            $relationship
                                ->lesson_price;
                    }

                    if (
                        ! $lockedLesson
                            ->billing_type
                    ) {
                        $billingType =
                            in_array(
                                $relationship
                                    ->billing_type,
                                [
                                    'per_lesson',
                                    'monthly',
                                ],
                                true
                            )
                                ? $relationship
                                    ->billing_type
                                : 'monthly';

                        $lockedLesson
                            ->billing_type =
                            $billingType;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Complete lesson
                    |--------------------------------------------------------------------------
                    */

                    $lockedLesson
                        ->status =
                        'completed';

                    $lockedLesson
                        ->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Per-lesson billing
                    |--------------------------------------------------------------------------
                    |
                    | Monthly lessons do NOT create a payment here.
                    | They will be grouped by the monthly billing command.
                    |
                    */

                    if (
                        $lockedLesson
                            ->billing_type ===
                            'per_lesson'
                        &&
                        $lockedLesson
                            ->payment_id ===
                            null
                        &&
                        (float) $lockedLesson
                            ->billing_amount > 0
                    ) {
                        $lessonDate =
                            $lockedLesson
                                ->start_time
                                ?->copy()
                                ->utc();

                        $payment =
                            Payment::create([
                                'tutor_student_id' =>
                                    $relationship
                                        ->id,

                                'amount' =>
                                    $lockedLesson
                                        ->billing_amount,

                                'currency' =>
                                    'pln',

                                'period' =>
                                    $lessonDate
                                        ?->format(
                                            'F j, Y'
                                        )
                                    ?? 'Lesson',

                                'billing_type' =>
                                    'per_lesson',

                                'period_start' =>
                                    $lessonDate
                                        ?->toDateString(),

                                'period_end' =>
                                    $lessonDate
                                        ?->toDateString(),

                                'lesson_count' =>
                                    1,

                                'status' =>
                                    'pending',

                                'payment_method' =>
                                    null,
                            ]);

                        $lockedLesson
                            ->payment_id =
                            $payment->id;

                        $lockedLesson
                            ->save();
                    }

                    return
                        $lockedLesson
                            ->fresh([
                                'tutorStudent',
                                'payment',
                            ]);
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Clear dashboard caches
        |--------------------------------------------------------------------------
        */

        $relationship =
            $completedLesson
                ->tutorStudent;

        if ($relationship) {
            Cache::forget(
                "tutor_dashboard_{$relationship->tutor_id}"
            );

            Cache::forget(
                "student_dashboard_{$relationship->student_id}"
            );
        }

        return
            $completedLesson;
    }
}