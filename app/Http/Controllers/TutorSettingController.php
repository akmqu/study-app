<?php

namespace App\Http\Controllers;

use App\Models\TutorSetting;
use App\Services\TutorSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TutorSettingController extends Controller
{
    public function edit(
        Request $request,
        TutorSettingsService $settingsService
    ): Response {
        $tutor =
            $request->user();

        $settings =
            $settingsService->get(
                $tutor->id
            );

        return Inertia::render(
            'Tutor/Settings',
            [
                'settings' =>
                    $settings,
            ]
        );
    }

    public function update(
        Request $request,
        TutorSettingsService $settingsService
    ): RedirectResponse {
        $tutor =
            $request->user();

        $validated =
            $request->validate([
                'default_lesson_duration' => [
                    'required',
                    'integer',
                    'in:30,45,60,90,120',
                ],

                'lesson_reminders_enabled' => [
                    'required',
                    'boolean',
                ],

                'lesson_reminder_minutes' => [
                    'required',
                    'integer',
                    'in:15,30,60,120',
                ],

                'default_billing_type' => [
                    'required',
                    'string',
                    'in:per_lesson,monthly',
                ],
            ]);

        TutorSetting::updateOrCreate(
            [
                'tutor_id' =>
                    $tutor->id,
            ],
            $validated
        );

        $settingsService->forget(
            $tutor->id
        );

        return redirect()
            ->route(
                'tutor.settings.edit'
            )
            ->with(
                'success',
                'Settings saved successfully.'
            );
    }
}