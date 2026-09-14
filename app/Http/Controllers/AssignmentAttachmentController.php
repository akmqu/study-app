<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentAttachmentController extends Controller
{
    public function show(
        Request $request,
        AssignmentAttachment $attachment
    ): StreamedResponse {
        $attachment->loadMissing(
            'assignment.tutorStudent'
        );

        $assignment = $attachment->assignment;

        $tutorStudent =
            $assignment?->tutorStudent;

        abort_unless(
            $assignment && $tutorStudent,
            404
        );

        $user = $request->user();

        $isTutor =
            $user->role === 'tutor'
            && (int) $tutorStudent->tutor_id
                === (int) $user->id;

        $isStudent =
            $user->role === 'student'
            && (int) $tutorStudent->student_id
                === (int) $user->id;

        abort_unless(
            $isTutor || $isStudent,
            403
        );

        abort_unless(
            $attachment->file_path
            && Storage::disk('public')
                ->exists($attachment->file_path),
            404
        );

        return Storage::disk('public')->response(
            $attachment->file_path,
            $attachment->original_name
                ?: basename($attachment->file_path),
            [],
            'inline'
        );
    }
}