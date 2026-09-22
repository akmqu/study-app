<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\TutorStudent;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(): Response
    {
        $tutor = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Automatically complete past lessons
        |--------------------------------------------------------------------------
        */

        $completedLessons =
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
                ->update([
                    'status' =>
                        'completed',
                ]);

        if ($completedLessons > 0) {
            $this->clearTutorDashboardCache(
                $tutor->id
            );
        }

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
                                $lesson->status,
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
                $validated['date']
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
                $validated['date']
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
        Lesson $lesson
    ): RedirectResponse {
        $this->ensureLessonBelongsToTutor(
            $request,
            $lesson
        );

        if (
            $lesson->status
            === 'scheduled'
        ) {
            $lesson->update([
                'status' =>
                    'completed',
            ]);

            $this->clearTutorDashboardCache(
                $request
                    ->user()
                    ->id
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
            $lesson->status
            === 'scheduled'
        ) {
            $lesson->update([
                'status' =>
                    'cancelled',
            ]);

            $this->clearTutorDashboardCache(
                $request
                    ->user()
                    ->id
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

        $tutorId =
            $request
                ->user()
                ->id;

        $lesson->delete();

        $this->clearTutorDashboardCache(
            $tutorId
        );

        return redirect()
            ->route(
                'tutor.calendar'
            )
            ->with(
                'success',
                'Lesson deleted successfully.'
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
}