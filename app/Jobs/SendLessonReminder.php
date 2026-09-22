<?php

namespace App\Jobs;

use App\Mail\LessonReminderMail;
use App\Models\Lesson;
use App\Services\TutorSettingsService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendLessonReminder implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $lessonId
    ) {
    }

    public function uniqueId(): string
    {
        return (string) $this->lessonId;
    }

    public function handle(
        TutorSettingsService $settingsService
    ): void {
        $lesson =
            Lesson::query()
                ->with([
                    'tutorStudent.student',
                    'tutorStudent.tutor',
                ])
                ->find(
                    $this->lessonId
                );

        if (! $lesson) {
            return;
        }

        if ($lesson->status !== 'scheduled') {
            return;
        }

        if ($lesson->reminder_sent_at) {
            return;
        }

        if (
            ! $lesson->start_time
            || $lesson->start_time->isPast()
        ) {
            return;
        }

        $tutorId =
            $lesson
                ->tutorStudent
                ?->tutor_id;

        if (! $tutorId) {
            return;
        }

        $settings =
            $settingsService->get(
                $tutorId
            );

        if (
            ! $settings[
                'lesson_reminders_enabled'
            ]
        ) {
            return;
        }

        $reminderMinutes =
            $settings[
                'lesson_reminder_minutes'
            ];

        $reminderAt =
            $lesson
                ->start_time
                ->copy()
                ->subMinutes(
                    $reminderMinutes
                );

        if (
            now()->lt(
                $reminderAt
            )
        ) {
            return;
        }

        $student =
            $lesson
                ->tutorStudent
                ?->student;

        if (
            ! $student
            || ! $student->email
        ) {
            return;
        }

        Mail::to(
            $student->email
        )->send(
            new LessonReminderMail(
                $lesson,
                $reminderMinutes
            )
        );

        $lesson->update([
            'reminder_sent_at' =>
                now(),
        ]);
    }
}