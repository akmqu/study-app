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

        return Inertia::render('Tutor/Dashboard', [
            'stats' => [
                'activeStudents' => $students->count(),
                'pendingReviews' => 5,
            ],

            'students' => $students,
        ]);
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

        $tutorStudent = TutorStudent::query()
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
            $assignment = Assignment::create([
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
        /*
        |--------------------------------------------------------------------------
        | Check ownership
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Collect tutor attachment paths
        |--------------------------------------------------------------------------
        */

        $attachmentPaths = $assignment
            ->attachments()
            ->pluck('file_path')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Collect student submission paths
        |--------------------------------------------------------------------------
        */

        $submissionPaths = $assignment
            ->submissions()
            ->pluck('student_file_path')
            ->filter()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Delete database records
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $assignment
        ): void {
            $assignment
                ->attachments()
                ->delete();

            $assignment
                ->submissions()
                ->delete();

            $assignment->delete();
        });

        /*
        |--------------------------------------------------------------------------
        | Delete physical attachment files
        |--------------------------------------------------------------------------
        */

        if (! empty($attachmentPaths)) {
            Storage::disk('public')
                ->delete(
                    $attachmentPaths
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete physical submission files
        |--------------------------------------------------------------------------
        */

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