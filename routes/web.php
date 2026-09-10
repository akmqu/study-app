<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorStudentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user && $user->role === 'tutor') {
        return redirect()->route('tutor.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:tutor'])->prefix('tutor')->group(function () {
    Route::get('/dashboard', [TutorController::class, 'dashboard'])->name('tutor.dashboard');
    Route::get('/students', [TutorStudentController::class, 'index'])->name('tutor.students');
    Route::delete('/students/{student}', [TutorStudentController::class, 'destroy'])
        ->whereNumber('student')
        ->name('tutor.students.destroy');
    Route::post('/invitations', [TutorStudentController::class, 'storeInvitation'])->name('tutor.invitations.store');
    Route::delete('/invitations/{invitation}', [TutorStudentController::class, 'destroyInvitation'])
        ->whereNumber('invitation')
        ->name('tutor.invitations.destroy');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::post('/invitations/redeem', [StudentController::class, 'redeemInvitation'])->name('student.invitations.redeem');
    Route::get('/assignments', [StudentController::class, 'assignments'])->name('student.assignments');
});

require __DIR__.'/auth.php';
