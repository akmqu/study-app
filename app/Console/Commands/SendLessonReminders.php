<?php

namespace App\Console\Commands;

use App\Jobs\SendLessonReminder;
use App\Models\Lesson;
use Illuminate\Console\Command;

class SendLessonReminders extends Command
{
    protected $signature =
        'lessons:send-reminders';

    protected $description =
        'Queue reminder emails for upcoming lessons';

    public function handle(): int
    {
        $now = now();

        $until =
            $now
                ->copy()
                ->addHour();

        $lessonIds =
            Lesson::query()
                ->where(
                    'status',
                    'scheduled'
                )
                ->whereNull(
                    'reminder_sent_at'
                )
                ->where(
                    'start_time',
                    '>',
                    $now
                )
                ->where(
                    'start_time',
                    '<=',
                    $until
                )
                ->orderBy(
                    'start_time'
                )
                ->pluck('id');

        foreach ($lessonIds as $lessonId) {
            SendLessonReminder::dispatch(
                $lessonId
            );
        }

        $this->info(
            "Queued {$lessonIds->count()} lesson reminder(s)."
        );

        return self::SUCCESS;
    }
}