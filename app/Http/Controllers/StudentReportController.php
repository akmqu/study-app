<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\TutorStudent;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentReportController extends Controller
{
    public function download(
        Request $request,
        User $student
    ) {
        $tutor = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Tutor ↔ student relations
        |--------------------------------------------------------------------------
        */

        $relations =
            TutorStudent::query()
                ->where(
                    'tutor_id',
                    $tutor->id
                )
                ->where(
                    'student_id',
                    $student->id
                )
                ->get();

        abort_if(
            $relations->isEmpty(),
            404
        );

        $relationIds =
            $relations
                ->pluck('id');

        $subjects =
            $relations
                ->pluck('subject')
                ->filter()
                ->unique()
                ->sort()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Lessons statistics
        |--------------------------------------------------------------------------
        */

        $lessonCounts =
            Lesson::query()
                ->whereIn(
                    'tutor_student_id',
                    $relationIds
                )
                ->selectRaw(
                    'status, COUNT(*) as total'
                )
                ->groupBy(
                    'status'
                )
                ->pluck(
                    'total',
                    'status'
                );

        /*
        |--------------------------------------------------------------------------
        | Assignments
        |--------------------------------------------------------------------------
        */

        $assignments =
            Assignment::query()
                ->whereIn(
                    'tutor_student_id',
                    $relationIds
                )
                ->with([
                    'tutorStudent:id,subject',
                    'latestSubmission',
                ])
                ->orderByDesc(
                    'created_at'
                )
                ->get();

        $gradedSubmissions =
            $assignments
                ->pluck(
                    'latestSubmission'
                )
                ->filter(
                    fn ($submission) =>
                        $submission
                        && $submission->status === 'graded'
                        && $submission->grade !== null
                );

        $averageGrade =
            $gradedSubmissions->isEmpty()
                ? null
                : round(
                    (float) $gradedSubmissions
                        ->avg('grade'),
                    1
                );

        /*
        |--------------------------------------------------------------------------
        | Recent homework
        |--------------------------------------------------------------------------
        */

        $recentAssignments =
            $assignments
                ->take(10)
                ->map(
                    function (
                        Assignment $assignment
                    ) {
                        $submission =
                            $assignment
                                ->latestSubmission;

                        return [
                            'title' =>
                                $assignment->title,

                            'subject' =>
                                $assignment
                                    ->tutorStudent
                                    ?->subject,

                            'deadline' =>
                                $assignment
                                    ->deadline,

                            'status' =>
                                $submission
                                    ?->status
                                ?? 'todo',

                            'grade' =>
                                $submission
                                    ?->grade,
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'reports.student-progress',
                [
                    'student' =>
                        $student,

                    'tutor' =>
                        $tutor,

                    'subjects' =>
                        $subjects,

                    'generatedAt' =>
                        now(),

                    'linkedAt' =>
                        $relations
                            ->min(
                                'created_at'
                            ),

                    'lessonStats' => [
                        'total' =>
                            $lessonCounts
                                ->sum(),

                        'scheduled' =>
                            (int) (
                                $lessonCounts[
                                    'scheduled'
                                ]
                                ?? 0
                            ),

                        'completed' =>
                            (int) (
                                $lessonCounts[
                                    'completed'
                                ]
                                ?? 0
                            ),

                        'cancelled' =>
                            (int) (
                                $lessonCounts[
                                    'cancelled'
                                ]
                                ?? 0
                            ),
                    ],

                    'assignmentStats' => [
                        'total' =>
                            $assignments
                                ->count(),

                        'graded' =>
                            $gradedSubmissions
                                ->count(),

                        'averageGrade' =>
                            $averageGrade,
                    ],

                    'recentAssignments' =>
                        $recentAssignments,
                ]
            )
                ->setPaper(
                    'a4',
                    'portrait'
                );

        $fileName =
            'student-progress-'
            . Str::slug(
                $student->name
                ?: 'student'
            )
            . '.pdf';

        return $pdf->download(
            $fileName
        );
    }
}