<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tutor\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
            ->get(['users.id', 'users.name', 'users.email'])
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

    public function storeInvitation(StoreInvitationRequest $request): RedirectResponse
    {
        $tutor = $request->user();

        $invitation = $tutor->invitations()->create([
            'code' => Invitation::generateUniqueCode(),
            'student_name' => $request->validated('student_name'),
            'subject' => $request->validated('subject'),
            'price' => $request->validated('price'),
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDays(Invitation::DEFAULT_EXPIRY_DAYS),
        ]);

        return redirect()
            ->route('tutor.students')
            ->with('success', "Invitation code {$invitation->code} generated.")
            ->with('generated_code', $invitation->code);
    }

    public function destroyInvitation(Invitation $invitation): RedirectResponse
    {
        $tutor = Auth::user();

        if ($invitation->tutor_id !== $tutor->id) {
            abort(404);
        }

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'Only unused invitation codes can be deleted.',
            ]);
        }

        $invitation->update([
            'status' => Invitation::STATUS_REVOKED,
        ]);

        return redirect()
            ->route('tutor.students')
            ->with('success', 'Invitation code deleted.');
    }

    public function destroy(User $student): RedirectResponse
    {
        $tutor = Auth::user();

        $detached = $tutor->students()->detach($student->id);

        if ($detached === 0) {
            abort(404);
        }

        return redirect()
            ->route('tutor.students')
            ->with('success', 'Student unlinked successfully.');
    }
}
