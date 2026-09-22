<?php

namespace App\Console\Commands;

use App\Jobs\SendLessonReminder;
use App\Models\Lesson;
use App\Services\TutorSettingsService;
use Illuminate\Console\Command;

class SendLessonReminders extends Command
{
    protected $signature =
        'lessons:send-reminders';

    protected $description =
        'Queue reminder emails for upcoming lessons';

    public function __construct(
        private readonly TutorSettingsService $settingsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now =
            now();

        /*
        |--------------------------------------------------------------------------
        | Maximum reminder window
        |--------------------------------------------------------------------------
        |
        | Current allowed settings:
        | 15 / 30 / 60 / 120 minutes.
        |
        */

        $until =
            $now
                ->copy()
                ->addMinutes(
                    120
                );

        $lessons =
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
                ->with([
                    'tutorStudent:id,tutor_id',
                ])
                ->orderBy(
                    'start_time'
                )
                ->get();

        $queued =
            0;

        foreach (
            $lessons
            as $lesson
        ) {
            $tutorId =
                $lesson
                    ->tutorStudent
                    ?->tutor_id;

            if (! $tutorId) {
                continue;
            }

            $settings =
                $this
                    ->settingsService
                    ->get(
                        $tutorId
                    );

            if (
                ! $settings[
                    'lesson_reminders_enabled'
                ]
            ) {
                continue;
            }

            $reminderAt =
                $lesson
                    ->start_time
                    ->copy()
                    ->subMinutes(
                        $settings[
                            'lesson_reminder_minutes'
                        ]
                    );

            if (
                $now->lt(
                    $reminderAt
                )
            ) {
                continue;
            }

            SendLessonReminder::dispatch(
                $lesson->id
            );

            $queued++;
        }

        $this->info(
            "Queued {$queued} lesson reminder(s)."
        );

        return self::SUCCESS;
    }
}