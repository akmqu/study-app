<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\TutorStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TutorController extends Controller
{
    public function dashboard(): Response
    {
        $tutor = auth()->user();

        $cacheKey =
            "tutor_dashboard_{$tutor->id}";

        $dashboardData =
            Cache::remember(
                $cacheKey,
                now()->addMinute(),
                function () use ($tutor): array {
                    $students =
                        TutorStudent::query()
                            ->where(
                                'tutor_id',
                                $tutor->id
                            )
                            ->with(
                                'student:id,name,email'
                            )
                            ->get()
                            ->groupBy(
                                'student_id'
                            )
                            ->map(
                                function (
                                    $relations
                                ) {
                                    $first =
                                        $relations
                                            ->first();

                                    return [
                                        'id' =>
                                            $first
                                                ->student_id,

                                        'name' =>
                                            $first
                                                ->student
                                                ->name,

                                        'email' =>
                                            $first
                                                ->student
                                                ->email,

                                        'subjects' =>
                                            $relations
                                                ->pluck(
                                                    'subject'
                                                )
                                                ->filter()
                                                ->unique()
                                                ->values()
                                                ->all(),
                                    ];
                                }
                            )
                            ->sortBy('name')
                            ->take(5)
                            ->values()
                            ->all();

                    $upcomingLessons =
                        Lesson::query()
                            ->whereHas(
                                'tutorStudent',
                                fn ($query) =>
                                    $query->where(
                                        'tutor_id',
                                        $tutor->id
                                    )
                            )
                            ->where(
                                'status',
                                'scheduled'
                            )
                            ->where(
                                'start_time',
                                '>=',
                                now()
                            )
                            ->with([
                                'tutorStudent.student:id,name',
                            ])
                            ->orderBy(
                                'start_time'
                            )
                            ->limit(3)
                            ->get()
                            ->map(
                                fn (
                                    Lesson $lesson
                                ) => [
                                    'id' =>
                                        $lesson->id,

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
                                ]
                            )
                            ->values()
                            ->all();

                    $pendingReviews =
                        Assignment::query()
                            ->whereHas(
                                'tutorStudent',
                                fn ($query) =>
                                    $query->where(
                                        'tutor_id',
                                        $tutor->id
                                    )
                            )
                            ->whereHas(
                                'latestSubmission',
                                fn ($query) =>
                                    $query->where(
                                        'status',
                                        'awaiting_review'
                                    )
                            )
                            ->with([
                                'tutorStudent.student:id,name',
                                'latestSubmission',
                            ])
                            ->get()
                            ->sortByDesc(
                                fn (
                                    Assignment $assignment
                                ) =>
                                    $assignment
                                        ->latestSubmission
                                        ?->created_at
                            )
                            ->take(3)
                            ->map(
                                fn (
                                    Assignment $assignment
                                ) => [
                                    'id' =>
                                        $assignment->id,

                                    'title' =>
                                        $assignment->title,

                                    'student_name' =>
                                        $assignment
                                            ->tutorStudent
                                            ?->student
                                            ?->name,

                                    'subject' =>
                                        $assignment
                                            ->tutorStudent
                                            ?->subject,

                                    'submitted_at' =>
                                        $assignment
                                            ->latestSubmission
                                            ?->created_at
                                            ?->toIso8601String(),
                                ]
                            )
                            ->values()
                            ->all();

                    return [
                        'students' =>
                            $students,

                        'upcomingLessons' =>
                            $upcomingLessons,

                        'pendingReviews' =>
                            $pendingReviews,
                    ];
                }
            );

        return Inertia::render(
            'Tutor/Dashboard',
            $dashboardData
        );
    }

    public function assignments(): Response
    {
        $tutor = auth()->user();

        $students =
            TutorStudent::query()
                ->where(
                    'tutor_id',
                    $tutor->id
                )
                ->with(
                    'student:id,name,email'
                )
                ->get()
                ->groupBy(
                    'student_id'
                )
                ->map(
                    function (
                        $relations
                    ) {
                        $first =
                            $relations
                                ->first();

                        return [
                            'id' =>
                                $first
                                    ->student_id,

                            'name' =>
                                $first
                                    ->student
                                    ->name,

                            'email' =>
                                $first
                                    ->student
                                    ->email,

                            'subjects' =>
                                $relations
                                    ->pluck(
                                        'subject'
                                    )
                                    ->filter()
                                    ->unique()
                                    ->values(),
                        ];
                    }
                )
                ->sortBy('name')
                ->values();

        $assignments =
            Assignment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'tutor_id',
                            $tutor->id
                        )
                )
                ->with([
                    'tutorStudent.student:id,name,email',
                    'attachments',
                    'latestSubmission',
                ])
                ->orderByRaw(
                    'CASE WHEN deadline IS NULL THEN 1 ELSE 0 END'
                )
                ->orderBy(
                    'deadline'
                )
                ->orderByDesc(
                    'created_at'
                )
                ->get()
                ->map(
                    function (
                        Assignment $assignment
                    ) {
                        $submission =
                            $assignment
                                ->latestSubmission;

                        $status =
                            $submission?->status
                            ?? 'todo';

                        if (
                            ! in_array(
                                $status,
                                [
                                    'todo',
                                    'awaiting_review',
                                    'graded',
                                ],
                                true
                            )
                        ) {
                            $status =
                                'todo';
                        }

                        $attachments =
                            $assignment
                                ->attachments
                                ->map(
                                    fn ($attachment) => [
                                        'id' =>
                                            $attachment
                                                ->id,

                                        'name' =>
                                            $attachment
                                                ->original_name
                                            ?: basename(
                                                $attachment
                                                    ->file_path
                                            ),

                                        'mime_type' =>
                                            $attachment
                                                ->mime_type,

                                        'url' =>
                                            route(
                                                'assignment.attachments.show',
                                                [
                                                    'attachment' =>
                                                        $attachment
                                                            ->id,
                                                ],
                                                false
                                            ),
                                    ]
                                )
                                ->values();

                        return [
                            'id' =>
                                $assignment->id,

                            'title' =>
                                $assignment->title,

                            'instructions' =>
                                $assignment
                                    ->instructions,

                            'subject' =>
                                $assignment
                                    ->tutorStudent
                                    ?->subject,

                            'student' => [
                                'id' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->student
                                        ?->id,

                                'name' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->student
                                        ?->name,

                                'email' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->student
                                        ?->email,
                            ],

                            'deadline' =>
                                $assignment
                                    ->deadline
                                    ?->toIso8601String(),

                            'created_at' =>
                                $assignment
                                    ->created_at
                                    ?->toIso8601String(),

                            'status' =>
                                $status,

                            'grade' =>
                                $submission?->grade,

                            'feedback' =>
                                $submission
                                    ?->feedback,

                            'attachments' =>
                                $attachments,

                            'submission' =>
                                $submission
                                    ? [
                                        'id' =>
                                            $submission
                                                ->id,

                                        'status' =>
                                            $submission
                                                ->status,

                                        'answer' =>
                                            $submission
                                                ->student_answer,

                                        'submitted_at' =>
                                            $submission
                                                ->created_at
                                                ?->toIso8601String(),

                                        'file_url' =>
                                            $submission
                                                ->student_file_path
                                                ? route(
                                                    'submissions.show',
                                                    [
                                                        'submission' =>
                                                            $submission
                                                                ->id,
                                                    ],
                                                    false
                                                )
                                                : null,
                                    ]
                                    : null,
                        ];
                    }
                )
                ->values();

        return Inertia::render(
            'Tutor/Assignments',
            [
                'assignments' =>
                    $assignments,

                'students' =>
                    $students,
            ]
        );
    }

    public function storeAssignment(
        Request $request
    ): RedirectResponse {
        $tutor =
            $request->user();

        $validated =
            $request->validate([
                'student_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'tutor_student',
                        'student_id'
                    )->where(
                        'tutor_id',
                        $tutor->id
                    ),
                ],

                'subject' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'instructions' => [
                    'nullable',
                    'string',
                ],

                'deadline' => [
                    'nullable',
                    'date',
                ],

                'attachments' => [
                    'nullable',
                    'array',
                ],

                'attachments.*' => [
                    'file',
                    'mimes:pdf,doc,docx',
                    'max:10240',
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
            abort(
                404,
                'Student and subject are not linked to this tutor.'
            );
        }

        DB::transaction(
            function () use (
                $request,
                $validated,
                $tutorStudent
            ): void {
                $assignment =
                    Assignment::create([
                        'tutor_student_id' =>
                            $tutorStudent
                                ->id,

                        'title' =>
                            $validated[
                                'title'
                            ],

                        'instructions' =>
                            $validated[
                                'instructions'
                            ]
                            ?? null,

                        'deadline' =>
                            $validated[
                                'deadline'
                            ]
                            ?? null,
                    ]);

                foreach (
                    $request->file(
                        'attachments',
                        []
                    ) as $file
                ) {
                    $path =
                        $file->store(
                            'assignment-attachments',
                            'public'
                        );

                    $assignment
                        ->attachments()
                        ->create([
                            'file_path' =>
                                $path,

                            'original_name' =>
                                $file
                                    ->getClientOriginalName(),

                            'mime_type' =>
                                $file
                                    ->getMimeType(),
                        ]);
                }
            }
        );

        $this->clearDashboardCache(
            $tutor->id,
            $tutorStudent->student_id
        );

        return redirect()
            ->route(
                'tutor.assignments'
            )
            ->with(
                'success',
                'Homework assigned successfully.'
            );
    }

    public function destroyAssignment(
        Request $request,
        Assignment $assignment
    ): RedirectResponse {
        $assignment->loadMissing(
            'tutorStudent'
        );

        abort_unless(
            $assignment->tutorStudent
            && (int) $assignment
                ->tutorStudent
                ->tutor_id
                === (int) $request
                    ->user()
                    ->id,
            403
        );

        $tutorId =
            $assignment
                ->tutorStudent
                ->tutor_id;

        $studentId =
            $assignment
                ->tutorStudent
                ->student_id;

        $attachmentPaths =
            $assignment
                ->attachments()
                ->pluck(
                    'file_path'
                )
                ->filter()
                ->values()
                ->all();

        $submissionPaths =
            $assignment
                ->submissions()
                ->pluck(
                    'student_file_path'
                )
                ->filter()
                ->values()
                ->all();

        DB::transaction(
            function () use (
                $assignment
            ): void {
                $assignment
                    ->attachments()
                    ->delete();

                $assignment
                    ->submissions()
                    ->delete();

                $assignment->delete();
            }
        );

        if (
            ! empty(
                $attachmentPaths
            )
        ) {
            Storage::disk(
                'public'
            )->delete(
                $attachmentPaths
            );
        }

        if (
            ! empty(
                $submissionPaths
            )
        ) {
            Storage::disk(
                'public'
            )->delete(
                $submissionPaths
            );
        }

        $this->clearDashboardCache(
            $tutorId,
            $studentId
        );

        return redirect()
            ->route(
                'tutor.assignments'
            )
            ->with(
                'success',
                'Homework deleted successfully.'
            );
    }

    private function clearDashboardCache(
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