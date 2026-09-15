<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tutor\StoreInvitationRequest;
use App\Models\Assignment;
use App\Models\Invitation;
use App\Models\TutorStudent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TutorStudentController extends Controller
{
    public function index(): Response
    {
        $tutor = Auth::user();

        $students = $tutor
            ->students()
            ->orderBy('name')
            ->get([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'linked_at' => $student->pivot?->created_at,
            ]);

        $invitations = $tutor
            ->invitations()
            ->active()
            ->latest()
            ->get()
            ->map(fn (Invitation $invitation) => [
                'id' => $invitation->id,
                'code' => $invitation->code,
                'student_name' => $invitation->student_name,
                'subject' => $invitation->subject,
                'price' => $invitation->price,
                'expires_at' => $invitation->expires_at,
            ]);

        return Inertia::render('Tutor/Students', [
            'students' => $students,
            'invitations' => $invitations,
            'generatedCode' => session('generated_code'),
        ]);
    }

    public function show(User $student): Response
    {
        $tutor = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Make sure this student belongs to this tutor
        |--------------------------------------------------------------------------
        */

        $relation = TutorStudent::query()
            ->where('tutor_id', $tutor->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $relation) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Subjects
        |--------------------------------------------------------------------------
        */

        $subjects = TutorStudent::query()
            ->where('tutor_id', $tutor->id)
            ->where('student_id', $student->id)
            ->pluck('subject')
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Real assignments
        |--------------------------------------------------------------------------
        */

        $assignments = Assignment::query()
            ->whereHas(
                'tutorStudent',
                function ($query) use ($tutor, $student) {
                    $query
                        ->where('tutor_id', $tutor->id)
                        ->where('student_id', $student->id);
                }
            )
            ->with([
                'tutorStudent',
                'attachments',
                'latestSubmission',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Assignment $assignment) {
                $submission = $assignment->latestSubmission;

                $status = $submission?->status ?? 'todo';

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

                $attachments = $assignment
                    ->attachments
                    ->map(fn ($attachment) => [
                        'id' => $attachment->id,

                        'name' =>
                            $attachment->original_name
                            ?: basename($attachment->file_path),

                        'mime_type' =>
                            $attachment->mime_type,

                        'url' => route(
                            'assignment.attachments.show',
                            [
                                'attachment' =>
                                    $attachment->id,
                            ],
                            false
                        ),
                    ])
                    ->values();

                return [
                    'id' => $assignment->id,

                    'title' =>
                        $assignment->title,

                    'instructions' =>
                        $assignment->instructions,

                    'subject' =>
                        $assignment
                            ->tutorStudent
                            ?->subject,

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
                        $submission?->feedback,

                    'attachments' =>
                        $attachments,
                ];
            })
            ->values();

        return Inertia::render(
            'Tutor/StudentProfile',
            [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ],

                'privateNotes' =>
                    $relation->private_notes ?? '',

                'subjects' =>
                    $subjects,

                'assignments' =>
                    $assignments,
            ]
        );
    }

    public function updatePrivateNotes(
        Request $request,
        User $student
    ): RedirectResponse {
        $tutor = Auth::user();

        $isLinked = $tutor
            ->students()
            ->where(
                'users.id',
                $student->id
            )
            ->exists();

        if (! $isLinked) {
            abort(404);
        }

        $validated = $request->validate([
            'private_notes' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ]);

        $tutor
            ->students()
            ->updateExistingPivot(
                $student->id,
                [
                    'private_notes' =>
                        $validated['private_notes']
                        ?? null,
                ]
            );

        return back()->with(
            'success',
            'Private notes saved successfully.'
        );
    }

    public function storeInvitation(
        StoreInvitationRequest $request
    ): RedirectResponse {
        $tutor = $request->user();

        $invitation = $tutor
            ->invitations()
            ->create([
                'code' =>
                    Invitation::generateUniqueCode(),

                'student_name' =>
                    $request->validated(
                        'student_name'
                    ),

                'subject' =>
                    $request->validated(
                        'subject'
                    ),

                'price' =>
                    $request->validated(
                        'price'
                    ),

                'status' =>
                    Invitation::STATUS_PENDING,

                'expires_at' =>
                    now()->addDays(
                        Invitation::DEFAULT_EXPIRY_DAYS
                    ),
            ]);

        return redirect()
            ->route('tutor.students')
            ->with(
                'success',
                "Invitation code {$invitation->code} generated."
            )
            ->with(
                'generated_code',
                $invitation->code
            );
    }

    public function destroyInvitation(
        Invitation $invitation
    ): RedirectResponse {
        $tutor = Auth::user();

        if (
            $invitation->tutor_id !==
            $tutor->id
        ) {
            abort(404);
        }

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' =>
                    'Only unused invitation codes can be deleted.',
            ]);
        }

        $invitation->update([
            'status' =>
                Invitation::STATUS_REVOKED,
        ]);

        return redirect()
            ->route('tutor.students')
            ->with(
                'success',
                'Invitation code deleted.'
            );
    }

    public function destroy(
        User $student
    ): RedirectResponse {
        $tutor = Auth::user();

        $detached = $tutor
            ->students()
            ->detach($student->id);

        if ($detached === 0) {
            abort(404);
        }

        return redirect()
            ->route('tutor.students')
            ->with(
                'success',
                'Student unlinked successfully.'
            );
    }
}