<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Services\LessonBillingService;
use Illuminate\Console\Command;

class CompletePastLessons extends Command
{
    protected $signature =
        'lessons:complete-past';

    protected $description =
        'Complete past lessons and process their billing';

    public function handle(
        LessonBillingService $billingService
    ): int {
        $completed =
            0;

        Lesson::query()
            ->where(
                'status',
                'scheduled'
            )
            ->whereNotNull(
                'end_time'
            )
            ->where(
                'end_time',
                '<=',
                now()
            )
            ->orderBy(
                'id'
            )
            ->chunkById(
                100,
                function (
                    $lessons
                ) use (
                    $billingService,
                    &$completed
                ): void {
                    foreach (
                        $lessons
                        as $lesson
                    ) {
                        $billingService
                            ->complete(
                                $lesson
                            );

                        $completed++;
                    }
                }
            );

        $this->info(
            "Completed {$completed} past lesson(s)."
        );

        return
            self::SUCCESS;
    }
}