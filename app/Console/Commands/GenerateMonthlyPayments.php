<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyPayments extends Command
{
    protected $signature =
        'payments:generate-monthly';

    protected $description =
        'Generate monthly payments for completed lessons';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | Only closed months
        |--------------------------------------------------------------------------
        |
        | Example:
        | today = October 15
        |
        | We may generate invoices for September and older,
        | but never for October because that month is still in progress.
        |
        */

        $currentMonthStart =
            now()
                ->startOfMonth();

        $groups =
            Lesson::query()
                ->where(
                    'status',
                    'completed'
                )
                ->where(
                    'billing_type',
                    'monthly'
                )
                ->whereNull(
                    'payment_id'
                )
                ->whereNotNull(
                    'billing_amount'
                )
                ->where(
                    'start_time',
                    '<',
                    $currentMonthStart
                )
                ->orderBy(
                    'start_time'
                )
                ->get([
                    'id',
                    'tutor_student_id',
                    'start_time',
                ])
                ->groupBy(
                    function (
                        Lesson $lesson
                    ): string {
                        return
                            $lesson
                                ->tutor_student_id
                            . '|'
                            . $lesson
                                ->start_time
                                ->format(
                                    'Y-m'
                                );
                    }
                );

        $createdPayments =
            0;

        $processedLessons =
            0;

        foreach (
            $groups
            as $group
        ) {
            $firstLesson =
                $group->first();

            if (! $firstLesson) {
                continue;
            }

            $monthStart =
                $firstLesson
                    ->start_time
                    ->copy()
                    ->startOfMonth();

            $monthEnd =
                $monthStart
                    ->copy()
                    ->endOfMonth();

            $lessonIds =
                $group
                    ->pluck(
                        'id'
                    )
                    ->all();

            DB::transaction(
                function () use (
                    $lessonIds,
                    $monthStart,
                    $monthEnd,
                    &$createdPayments,
                    &$processedLessons
                ): void {
                    /*
                    |--------------------------------------------------------------------------
                    | Lock lessons
                    |--------------------------------------------------------------------------
                    |
                    | This prevents two scheduler processes from generating
                    | the same monthly payment at the same time.
                    |
                    */

                    $lessons =
                        Lesson::query()
                            ->whereIn(
                                'id',
                                $lessonIds
                            )
                            ->where(
                                'status',
                                'completed'
                            )
                            ->where(
                                'billing_type',
                                'monthly'
                            )
                            ->whereNull(
                                'payment_id'
                            )
                            ->whereNotNull(
                                'billing_amount'
                            )
                            ->lockForUpdate()
                            ->get();

                    if (
                        $lessons->isEmpty()
                    ) {
                        return;
                    }

                    $amount =
                        $lessons->sum(
                            function (
                                Lesson $lesson
                            ): float {
                                return
                                    (float)
                                    $lesson
                                        ->billing_amount;
                            }
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Do not create zero-value invoices
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $amount <=
                        0
                    ) {
                        return;
                    }

                    $payment =
                        Payment::create([
                            'tutor_student_id' =>
                                $lessons
                                    ->first()
                                    ->tutor_student_id,

                            'amount' =>
                                $amount,

                            'currency' =>
                                'pln',

                            'period' =>
                                $monthStart
                                    ->format(
                                        'F Y'
                                    ),

                            'billing_type' =>
                                'monthly',

                            'period_start' =>
                                $monthStart
                                    ->toDateString(),

                            'period_end' =>
                                $monthEnd
                                    ->toDateString(),

                            'lesson_count' =>
                                $lessons
                                    ->count(),

                            'status' =>
                                'pending',

                            'payment_method' =>
                                null,
                        ]);

                    Lesson::query()
                        ->whereIn(
                            'id',
                            $lessons
                                ->pluck(
                                    'id'
                                )
                        )
                        ->update([
                            'payment_id' =>
                                $payment->id,
                        ]);

                    $createdPayments++;

                    $processedLessons +=
                        $lessons
                            ->count();
                }
            );
        }

        $this->info(
            "Created {$createdPayments} monthly payment(s) from {$processedLessons} lesson(s)."
        );

        return
            self::SUCCESS;
    }
}