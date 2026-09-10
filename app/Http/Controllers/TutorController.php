<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TutorController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Tutor/Dashboard', [
            'stats' => [
                'activeStudents' => 12,
                'pendingReviews' => 5,
            ]
        ]);
    }

    public function students()
    {
        return Inertia::render('Tutor/Students', [
            'students' => []
        ]);
    }
}