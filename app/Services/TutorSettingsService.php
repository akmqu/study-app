<?php

namespace App\Services;

use App\Models\TutorSetting;
use Illuminate\Support\Facades\Cache;

class TutorSettingsService
{
    public function get(int $tutorId): array
    {
        return Cache::remember(
            "tutor_settings_{$tutorId}",
            now()->addMinutes(10),
            function () use ($tutorId): array {
                $settings =
                    TutorSetting::firstOrCreate(
                        [
                            'tutor_id' =>
                                $tutorId,
                        ],
                        [
                            'default_lesson_duration' =>
                                60,

                            'lesson_reminders_enabled' =>
                                true,

                            'lesson_reminder_minutes' =>
                                60,

                            'default_billing_type' =>
                                'per_lesson',
                        ]
                    );

                return [
                    'default_lesson_duration' =>
                        (int) $settings
                            ->default_lesson_duration,

                    'lesson_reminders_enabled' =>
                        (bool) $settings
                            ->lesson_reminders_enabled,

                    'lesson_reminder_minutes' =>
                        (int) $settings
                            ->lesson_reminder_minutes,

                    'default_billing_type' =>
                        $settings
                            ->default_billing_type,
                ];
            }
        );
    }

    public function forget(
        int $tutorId
    ): void {
        Cache::forget(
            "tutor_settings_{$tutorId}"
        );
    }
}