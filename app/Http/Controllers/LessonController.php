<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Payment;
use App\Models\TutorStudent;
use App\Services\LessonBillingService;
use App\Services\TutorSettingsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(
        TutorSettingsService $settingsService
    ): Response {
        $tutor =
            Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        $settings =
            $settingsService->get(
                $tutor->id
            );

        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        $students =
            $tutor
                ->tutorStudents()
                ->with(
                    'student:id,name'
                )
                ->get()
                ->groupBy(
                    'student_id'
                )
                ->map(
                    function (
                        $relationships
                    ) {
                        $first =
                            $relationships
                                ->first();

                        return [
                            'id' =>
                                $first
                                    ->student_id,

                            'name' =>
                                $first
                                    ->student
                                    ->name,

                            'subjects' =>
                                $relationships
                                    ->pluck(
                                        'subject'
                                    )
                                    ->filter()
                                    ->unique()
                                    ->values(),
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Lessons
        |--------------------------------------------------------------------------
        */

        $lessons =
            Lesson::query()
                ->whereHas(
                    'tutorStudent',
                    function ($query) use ($tutor) {
                        $query->where(
                            'tutor_id',
                            $tutor->id
                        );
                    }
                )
                ->with([
                    'tutorStudent.student:id,name',
                ])
                ->orderBy(
                    'start_time'
                )
                ->get()
                ->map(
                    function (
                        Lesson $lesson
                    ) {
                        return [
                            'id' =>
                                $lesson->id,

                            'student_id' =>
                                $lesson
                                    ->tutorStudent
                                    ->student_id,

                            'student_name' =>
                                $lesson
                                    ->tutorStudent
                                    ->student
                                    ->name,

                            'subject' =>
                                $lesson
                                    ->tutorStudent
                                    ->subject,

                            'start_time' =>
                                $lesson
                                    ->start_time
                                    ?->utc()
                                    ->toIso8601String(),

                            'end_time' =>
                                $lesson
                                    ->end_time
                                    ?->utc()
                                    ->toIso8601String(),

                            'status' =>
                                $lesson
                                    ->status,
                        ];
                    }
                )
                ->values();

        return Inertia::render(
            'Tutor/Calendar',
            [
                'lessons' =>
                    $lessons,

                'students' =>
                    $students,

                'defaultLessonDuration' =>
                    $settings[
                        'default_lesson_duration'
                    ],
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $tutor =
            $request->user();

        $validated =
            $request->validate([
                'student_id' => [
                    'required',
                    'integer',
                ],

                'subject' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'date' => [
                    'required',
                    'date_format:Y-m-d',
                ],

                'start_time' => [
                    'required',
                    'date_format:H:i',
                ],

                'end_time' => [
                    'required',
                    'date_format:H:i',
                ],

                'timezone' => [
                    'required',
                    'timezone',
                ],
            ]);

        $tutorStudent =
            TutorStudent::query()
                ->where(
                    'tutor_id',
                    $tutor->id
                )
                ->where(
                    'student_id',
                    $validated[
                        'student_id'
                    ]
                )
                ->where(
                    'subject',
                    $validated[
                        'subject'
                    ]
                )
                ->first();

        if (! $tutorStudent) {
            throw ValidationException::withMessages([
                'student_id' =>
                    'This student and subject are not linked to your account.',
            ]);
        }

        $startTime =
            Carbon::createFromFormat(
                '!Y-m-d H:i',
                $validated[
                    'date'
                ]
                    . ' '
                    . $validated[
                        'start_time'
                    ],
                $validated[
                    'timezone'
                ]
            )->utc();

        $endTime =
            Carbon::createFromFormat(
                '!Y-m-d H:i',
                $validated[
                    'date'
                ]
                    . ' '
                    . $validated[
                        'end_time'
                    ],
                $validated[
                    'timezone'
                ]
            )->utc();

        if (
            $endTime
                ->lessThanOrEqualTo(
                    $startTime
                )
        ) {
            throw ValidationException::withMessages([
                'end_time' =>
                    'End time must be after start time.',
            ]);
        }

        Lesson::create([
            'tutor_student_id' =>
                $tutorStudent->id,

            'start_time' =>
                $startTime,

            'end_time' =>
                $endTime,

            'status' =>
                'scheduled',
        ]);

        $this->clearTutorDashboardCache(
            $tutor->id
        );

        return redirect()
            ->route(
                'tutor.calendar'
            )
            ->with(
                'success',
                'Lesson scheduled successfully.'
            );
    }

    public function complete(
        Request $request,
        Lesson $lesson,
        LessonBillingService $billingService
    ): RedirectResponse {
        $this->ensureLessonBelongsToTutor(
            $request,
            $lesson
        );

        if (
            $lesson->status ===
            'scheduled'
        ) {
            $billingService->complete(
                $lesson
            );
        }

        return redirect()
            ->route(
                'tutor.calendar'
            )
            ->with(
                'success',
                'Lesson marked as completed.'
            );
    }

    public function cancel(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $this->ensureLessonBelongsToTutor(
            $request,
            $lesson
        );

        if (
            $lesson->status ===
            'scheduled'
        ) {
            $lesson->update([
                'status' =>
                    'cancelled',
            ]);

            $relationship =
                $lesson
                    ->tutorStudent;

            $this->clearDashboardCaches(
                $relationship
                    ->tutor_id,

                $relationship
                    ->student_id
            );
        }

        return redirect()
            ->route(
                'tutor.calendar'
            )
            ->with(
                'success',
                'Lesson cancelled.'
            );
    }

    public function destroy(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $this->ensureLessonBelongsToTutor(
            $request,
            $lesson
        );

        $result =
            DB::transaction(
                function () use (
                    $lesson
                ): array {
                    /*
                    |--------------------------------------------------------------------------
                    | Lock lesson
                    |--------------------------------------------------------------------------
                    */

                    $lockedLesson =
                        Lesson::query()
                            ->with(
                                'tutorStudent'
                            )
                            ->lockForUpdate()
                            ->findOrFail(
                                $lesson->id
                            );

                    $relationship =
                        $lockedLesson
                            ->tutorStudent;

                    /*
                    |--------------------------------------------------------------------------
                    | Lock related payment
                    |--------------------------------------------------------------------------
                    */

                    $payment =
                        null;

                    if (
                        $lockedLesson
                            ->payment_id !==
                        null
                    ) {
                        $payment =
                            Payment::query()
                                ->lockForUpdate()
                                ->find(
                                    $lockedLesson
                                        ->payment_id
                                );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Paid financial history cannot be silently removed
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $payment
                        &&
                        $payment
                            ->status ===
                            'paid'
                    ) {
                        throw ValidationException::withMessages([
                            'lesson' =>
                                'This lesson has already been paid and cannot be deleted.',
                        ]);
                    }

                    $paymentChanged =
                        $payment !==
                        null;

                    $paymentId =
                        $payment
                            ?->id;

                    /*
                    |--------------------------------------------------------------------------
                    | Delete lesson
                    |--------------------------------------------------------------------------
                    */

                    $lockedLesson
                        ->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | Recalculate pending payment
                    |--------------------------------------------------------------------------
                    |
                    | Per lesson:
                    |   no lessons remain -> delete payment.
                    |
                    | Monthly:
                    |   recalculate amount and lesson_count from remaining lessons.
                    |
                    */

                    if (
                        $payment
                        &&
                        $paymentId
                    ) {
                        $remainingLessons =
                            Lesson::query()
                                ->where(
                                    'payment_id',
                                    $paymentId
                                )
                                ->where(
                                    'status',
                                    'completed'
                                )
                                ->orderBy(
                                    'id'
                                )
                                ->lockForUpdate()
                                ->get();

                        if (
                            $remainingLessons
                                ->isEmpty()
                        ) {
                            $payment
                                ->delete();
                        } else {
                            /*
                            |--------------------------------------------------------------------------
                            | Calculate in cents
                            |--------------------------------------------------------------------------
                            */

                            $amountInCents =
                                $remainingLessons
                                    ->sum(
                                        function (
                                            Lesson $remainingLesson
                                        ): int {
                                            return
                                                (int) round(
                                                    (float)
                                                    $remainingLesson
                                                        ->billing_amount
                                                    * 100
                                                );
                                        }
                                    );

                            $payment->update([
                                'amount' =>
                                    number_format(
                                        $amountInCents
                                            / 100,
                                        2,
                                        '.',
                                        ''
                                    ),

                                'lesson_count' =>
                                    $remainingLessons
                                        ->count(),
                            ]);
                        }
                    }

                    return [
                        'tutor_id' =>
                            $relationship
                                ->tutor_id,

                        'student_id' =>
                            $relationship
                                ->student_id,

                        'payment_changed' =>
                            $paymentChanged,
                    ];
                }
            );

        $this->clearDashboardCaches(
            $result[
                'tutor_id'
            ],

            $result[
                'student_id'
            ]
        );

        return redirect()
            ->route(
                'tutor.calendar'
            )
            ->with(
                'success',
                $result[
                    'payment_changed'
                ]
                    ? 'Lesson deleted and pending payment recalculated.'
                    : 'Lesson deleted successfully.'
            );
    }

    private function ensureLessonBelongsToTutor(
        Request $request,
        Lesson $lesson
    ): void {
        $belongsToTutor =
            TutorStudent::query()
                ->whereKey(
                    $lesson
                        ->tutor_student_id
                )
                ->where(
                    'tutor_id',
                    $request
                        ->user()
                        ->id
                )
                ->exists();

        abort_unless(
            $belongsToTutor,
            403
        );
    }

    private function clearTutorDashboardCache(
        int $tutorId
    ): void {
        Cache::forget(
            "tutor_dashboard_{$tutorId}"
        );
    }

    private function clearDashboardCaches(
        int $tutorId,
        int $studentId
    ): void {
        Cache::forget(
            "tutor_dashboard_{$tutorId}"
        );

        Cache::forget(
            "student_dashboard_{$studentId}"
        );
    }
}