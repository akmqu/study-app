<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\TutorStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $students = $tutor
            ->students()
            ->orderBy('name')
            ->get([
                'users.id',
                'users.name',
            ])
            ->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
            ]);

        return Inertia::render(
            'Tutor/Dashboard',
            [
                'students' => $students,
            ]
        );
    }

    public function assignments(): Response
    {
        $tutor = auth()->user();

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
                ->orderBy('deadline')
                ->orderByDesc('created_at')
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
                            $status = 'todo';
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
                                                        $attachment->id,
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
                        ];
                    }
                )
                ->values();

        return Inertia::render(
            'Tutor/Assignments',
            [
                'assignments' =>
                    $assignments,
            ]
        );
    }

    public function storeAssignment(
        Request $request
    ): RedirectResponse {
        $tutor = $request->user();

        $validated = $request->validate([
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
                    $validated['student_id']
                )
                ->where(
                    'subject',
                    $validated['subject']
                )
                ->first();

        if (! $tutorStudent) {
            abort(
                404,
                'Student and subject are not linked to this tutor.'
            );
        }

        DB::transaction(function () use (
            $request,
            $validated,
            $tutorStudent
        ): void {
            $assignment =
                Assignment::create([
                    'tutor_student_id' =>
                        $tutorStudent->id,

                    'title' =>
                        $validated['title'],

                    'instructions' =>
                        $validated['instructions']
                        ?? null,

                    'deadline' =>
                        $validated['deadline']
                        ?? null,
                ]);

            foreach (
                $request->file(
                    'attachments',
                    []
                ) as $file
            ) {
                $path = $file->store(
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
        });

        return redirect()
            ->back()
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

        $attachmentPaths =
            $assignment
                ->attachments()
                ->pluck('file_path')
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

        if (! empty($attachmentPaths)) {
            Storage::disk('public')
                ->delete(
                    $attachmentPaths
                );
        }

        if (! empty($submissionPaths)) {
            Storage::disk('public')
                ->delete(
                    $submissionPaths
                );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Homework deleted successfully.'
            );
    }
}