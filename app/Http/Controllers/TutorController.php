<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\TutorStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->get(['users.id', 'users.name'])
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

public function storeAssignment(Request $request): RedirectResponse
{
    $tutor = $request->user();

    $validated = $request->validate([
        'student_id' => [
            'required',
            'integer',
            Rule::exists('tutor_student', 'student_id')
                ->where('tutor_id', $tutor->id),
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
        ->where('tutor_id', $tutor->id)
        ->where('student_id', $validated['student_id'])
        ->first();

    if (! $tutorStudent) {
        abort(404, 'Student is not linked to this tutor.');
    }

    $assignment = Assignment::create([
        'tutor_student_id' => $tutorStudent->id,
        'title' => $validated['title'],
        'instructions' => $validated['instructions'] ?? null,
        'deadline' => $validated['deadline'] ?? null,
    ]);

    foreach ($request->file('attachments', []) as $file) {
        $path = $file->store('assignment-attachments', 'public');

        $assignment->attachments()->create([
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    return redirect()
        ->back()
        ->with('success', 'Homework assigned successfully.');
}
}