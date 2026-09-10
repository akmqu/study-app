<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\RedeemInvitationRequest;
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
            ->get(['users.id', 'users.name', 'users.email'])
            ->map(fn ($tutor) => [
                'id' => $tutor->id,
                'name' => $tutor->name,
                'email' => $tutor->email,
            ]);

        return Inertia::render('Student/Dashboard', [
            'upcomingAssignments' => 3,
            'paymentStatus' => 'Pending',
            'pendingReviews' => 0,
            'tutors' => $tutors,
        ]);
    }

    public function redeemInvitation(RedeemInvitationRequest $request): RedirectResponse
    {
        $student = $request->user();
        $normalizedCode = Invitation::normalizeCode($request->validated('code'));

        DB::transaction(function () use ($student, $normalizedCode): void {
            $invitation = Invitation::query()
                ->whereRaw('UPPER(TRIM(code)) = ?', [$normalizedCode])
                ->lockForUpdate()
                ->first();

            if (! $invitation || ! $invitation->isAcceptableBy($student)) {
                throw ValidationException::withMessages([
                    'code' => 'That invitation code is not available.',
                ]);
            }

            $tutorIsValid = User::query()
                ->whereKey($invitation->tutor_id)
                ->where('role', 'tutor')
                ->exists();

            if (! $tutorIsValid) {
                throw ValidationException::withMessages([
                    'code' => 'That invitation code is not available.',
                ]);
            }

            $invitation->update([
                'status' => Invitation::STATUS_ACCEPTED,
                'student_id' => $student->id,
            ]);

            try {
                $student->tutors()->attach($invitation->tutor_id);
            } catch (UniqueConstraintViolationException) {
                // Relationship already exists; invitation is still marked accepted once.
            }
        });

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'You are now linked to your tutor.');
    }

    public function assignments(): Response
    {
        return Inertia::render('Student/Assignments', [
            'assignments' => [],
        ]);
    }
}
