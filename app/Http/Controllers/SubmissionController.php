<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function store(
        Request $request,
        Assignment $assignment
    ): RedirectResponse {
        $student = $request->user();

        $assignment->loadMissing(
            'tutorStudent'
        );

        /*
        |--------------------------------------------------------------------------
        | Make sure assignment belongs to student
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $assignment->tutorStudent
            && (int) $assignment
                ->tutorStudent
                ->student_id
                === (int) $student->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate submission while waiting for review
        |--------------------------------------------------------------------------
        */

        $latestSubmission =
            $assignment
                ->latestSubmission()
                ->first();

        if (
            $latestSubmission
            && $latestSubmission->status
                === 'awaiting_review'
        ) {
            throw ValidationException::withMessages([
                'submission' =>
                    'This assignment is already waiting for tutor review.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate text answer / file
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'answer' => [
                        'nullable',
                        'string',
                        'max:10000',
                        'required_without:file',
                    ],

                    'file' => [
                        'nullable',
                        'file',
                        'max:10240',
                        'required_without:answer',
                        'extensions:pdf,doc,docx',

                        'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,application/octet-stream',
                    ],
                ],
                [
                    'answer.required_without' =>
                        'Write an answer or attach a file.',

                    'file.required_without' =>
                        'Write an answer or attach a file.',

                    'file.extensions' =>
                        'The file must be PDF, DOC or DOCX.',

                    'file.mimetypes' =>
                        'The file must be PDF, DOC or DOCX.',

                    'file.max' =>
                        'The file must not be larger than 10 MB.',
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Store optional file
        |--------------------------------------------------------------------------
        */

        $path = null;

        if ($request->hasFile('file')) {
            $path =
                $request
                    ->file('file')
                    ->store(
                        "submissions/{$assignment->id}",
                        'public'
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | Create submission
        |--------------------------------------------------------------------------
        */

        Submission::create([
            'assignment_id' =>
                $assignment->id,

            'student_file_path' =>
                $path,

            'student_answer' =>
                filled(
                    $validated['answer']
                    ?? null
                )
                    ? trim(
                        $validated['answer']
                    )
                    : null,

            'status' =>
                'awaiting_review',

            'grade' =>
                null,

            'feedback' =>
                null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear dashboard cache
        |--------------------------------------------------------------------------
        */

        $this->clearDashboardCache(
            $assignment
                ->tutorStudent
                ->tutor_id,
            $assignment
                ->tutorStudent
                ->student_id
        );

        return redirect()
            ->route(
                'student.assignments'
            )
            ->with(
                'success',
                'Your work was submitted successfully.'
            );
    }

    public function grade(
        Request $request,
        Submission $submission
    ): RedirectResponse {
        $tutor = $request->user();

        $submission->loadMissing(
            'assignment.tutorStudent'
        );

        $tutorStudent =
            $submission
                ->assignment
                ?->tutorStudent;

        /*
        |--------------------------------------------------------------------------
        | Make sure tutor owns assignment
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $tutorStudent
            && (int) $tutorStudent
                ->tutor_id
                === (int) $tutor->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Validate grade and feedback
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'grade' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:100',
                ],

                'feedback' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Save review
        |--------------------------------------------------------------------------
        */

        $submission->update([
            'status' =>
                'graded',

            'grade' =>
                $validated['grade'],

            'feedback' =>
                $validated['feedback']
                ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear dashboard cache
        |--------------------------------------------------------------------------
        */

        $this->clearDashboardCache(
            $tutorStudent->tutor_id,
            $tutorStudent->student_id
        );

        return redirect()
            ->route(
                'tutor.assignments'
            )
            ->with(
                'success',
                'Submission graded successfully.'
            );
    }

    public function show(
        Request $request,
        Submission $submission
    ): StreamedResponse {
        $submission->loadMissing(
            'assignment.tutorStudent'
        );

        $assignment =
            $submission->assignment;

        $tutorStudent =
            $assignment
                ?->tutorStudent;

        abort_unless(
            $assignment
            && $tutorStudent,
            404
        );

        $user =
            $request->user();

        $isTutor =
            $user->role === 'tutor'
            && (int) $tutorStudent
                ->tutor_id
                === (int) $user->id;

        $isStudent =
            $user->role === 'student'
            && (int) $tutorStudent
                ->student_id
                === (int) $user->id;

        abort_unless(
            $isTutor || $isStudent,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | File is optional
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $submission
                ->student_file_path
            && Storage::disk(
                'public'
            )->exists(
                $submission
                    ->student_file_path
            ),
            404
        );

        $extension =
            pathinfo(
                $submission
                    ->student_file_path,
                PATHINFO_EXTENSION
            );

        $fileName =
            'submission-'
            . $submission->id
            . (
                $extension
                    ? ".{$extension}"
                    : ''
            );

        return Storage::disk(
            'public'
        )->download(
            $submission
                ->student_file_path,
            $fileName
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