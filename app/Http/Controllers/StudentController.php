<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\RedeemInvitationRequest;
use App\Models\Assignment;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function dashboard(): Response
    {
        $student = auth()->user();

        $tutors = $student
            ->tutors()
            ->orderBy('name')
            ->get([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->map(fn ($tutor) => [
                'id' =>
                    $tutor->id,

                'name' =>
                    $tutor->name,

                'email' =>
                    $tutor->email,

                'subject' =>
                    $tutor->pivot?->subject,
            ]);

        $upcomingAssignments =
            Assignment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'student_id',
                            $student->id
                        )
                )
                ->whereDoesntHave(
                    'submissions'
                )
                ->where(function ($query) {
                    $query
                        ->whereNull(
                            'deadline'
                        )
                        ->orWhere(
                            'deadline',
                            '>=',
                            now()
                        );
                })
                ->count();

        $pendingReviews =
            Assignment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'student_id',
                            $student->id
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
                ->count();

        return Inertia::render(
            'Student/Dashboard',
            [
                'upcomingAssignments' =>
                    $upcomingAssignments,

                'pendingReviews' =>
                    $pendingReviews,

                'tutors' =>
                    $tutors,
            ]
        );
    }

    public function redeemInvitation(
        RedeemInvitationRequest $request
    ): RedirectResponse {
        $student =
            $request->user();

        $normalizedCode =
            Invitation::normalizeCode(
                $request->validated(
                    'code'
                )
            );

        DB::transaction(
            function () use (
                $student,
                $normalizedCode
            ): void {
                $invitation =
                    Invitation::query()
                        ->whereRaw(
                            'UPPER(TRIM(code)) = ?',
                            [
                                $normalizedCode,
                            ]
                        )
                        ->lockForUpdate()
                        ->first();

                if (
                    ! $invitation
                    || ! $invitation
                        ->isAcceptableBy(
                            $student
                        )
                ) {
                    throw ValidationException::withMessages([
                        'code' =>
                            'That invitation code is not available.',
                    ]);
                }

                $tutorIsValid =
                    User::query()
                        ->whereKey(
                            $invitation
                                ->tutor_id
                        )
                        ->where(
                            'role',
                            'tutor'
                        )
                        ->exists();

                if (! $tutorIsValid) {
                    throw ValidationException::withMessages([
                        'code' =>
                            'That invitation code is not available.',
                    ]);
                }

                $invitation->update([
                    'status' =>
                        Invitation::STATUS_ACCEPTED,

                    'student_id' =>
                        $student->id,
                ]);

                $pivotData = [
                    'subject' =>
                        $invitation->subject,
                ];

                if (
                    $invitation->price !==
                    null
                ) {
                    $pivotData[
                        'lesson_price'
                    ] =
                        $invitation->price;
                }

                try {
                    $student
                        ->tutors()
                        ->attach(
                            $invitation
                                ->tutor_id,
                            $pivotData
                        );
                } catch (
                    UniqueConstraintViolationException
                ) {
                    // Relation already exists.
                }
            }
        );

        return redirect()
            ->route(
                'student.dashboard'
            )
            ->with(
                'success',
                'You are now linked to your tutor.'
            );
    }

    public function assignments(): Response
    {
        $student = auth()->user();

        $assignments =
            Assignment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'student_id',
                            $student->id
                        )
                )
                ->with([
                    'tutorStudent.tutor:id,name,email',
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
                                            $attachment->id,

                                        'name' =>
                                            $attachment
                                                ->original_name,

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

                            'tutor' => [
                                'id' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->tutor
                                        ?->id,

                                'name' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->tutor
                                        ?->name,

                                'email' =>
                                    $assignment
                                        ->tutorStudent
                                        ?->tutor
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
            'Student/Assignments',
            [
                'assignments' =>
                    $assignments,
            ]
        );
    }
}