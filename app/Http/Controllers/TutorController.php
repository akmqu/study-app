<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class TutorController extends Controller
{
    public function dashboard(): Response
    {
        $tutor = auth()->user();

        return Inertia::render('Tutor/Dashboard', [
            'stats' => [
                'activeStudents' => $tutor->students()->count(),
                'pendingReviews' => 5,
            ],
        ]);
    }
}
