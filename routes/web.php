<?php

use App\Http\Controllers\HomeworkAiController;
use App\Http\Controllers\InvitationImportController;
use App\Http\Controllers\AssignmentAttachmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorSettingController;
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

    return $user?->role === 'tutor'
        ? redirect()->route('tutor.dashboard')
        : redirect()->route('student.dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Shared authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/assignment-attachments/{attachment}',
        [AssignmentAttachmentController::class, 'show']
    )
        ->whereNumber('attachment')
        ->name('assignment.attachments.show');

    Route::get(
        '/submissions/{submission}/file',
        [SubmissionController::class, 'show']
    )
        ->whereNumber('submission')
        ->name('submissions.show');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Tutor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:tutor'])
    ->prefix('tutor')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [TutorController::class, 'dashboard'])
            ->name('tutor.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Calendar
        |--------------------------------------------------------------------------
        */

        Route::get('/calendar', [LessonController::class, 'index'])
            ->name('tutor.calendar');

        Route::post('/lessons', [LessonController::class, 'store'])
            ->name('tutor.lessons.store');

        Route::patch(
            '/lessons/{lesson}/complete',
            [LessonController::class, 'complete']
        )
            ->whereNumber('lesson')
            ->name('tutor.lessons.complete');

        Route::patch(
            '/lessons/{lesson}/cancel',
            [LessonController::class, 'cancel']
        )
            ->whereNumber('lesson')
            ->name('tutor.lessons.cancel');

        Route::delete(
            '/lessons/{lesson}',
            [LessonController::class, 'destroy']
        )
            ->whereNumber('lesson')
            ->name('tutor.lessons.destroy');


        /*
        |--------------------------------------------------------------------------
        | Assignments
        |--------------------------------------------------------------------------
        */

        Route::get('/assignments', [TutorController::class, 'assignments'])
            ->name('tutor.assignments');

        Route::post(
    '/assignments/generate-ai',
    [HomeworkAiController::class, 'generate']
)->name('tutor.assignments.generate-ai');

        Route::post('/assignments', [TutorController::class, 'storeAssignment'])
            ->name('tutor.assignments.store');

        Route::delete(
            '/assignments/{assignment}',
            [TutorController::class, 'destroyAssignment']
        )
            ->whereNumber('assignment')
            ->name('tutor.assignments.destroy');

        Route::patch(
            '/submissions/{submission}/grade',
            [SubmissionController::class, 'grade']
        )
            ->whereNumber('submission')
            ->name('tutor.submissions.grade');


        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        Route::get('/students', [TutorStudentController::class, 'index'])
            ->name('tutor.students');

        Route::get(
            '/students/{student}/report',
            [StudentReportController::class, 'download']
        )
            ->whereNumber('student')
            ->name('tutor.students.report');

        Route::delete(
            '/students/{student}',
            [TutorStudentController::class, 'destroy']
        )
            ->whereNumber('student')
            ->name('tutor.students.destroy');


        /*
        |--------------------------------------------------------------------------
        | Invitations
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/invitations',
            [TutorStudentController::class, 'storeInvitation']
        )
            ->name('tutor.invitations.store');

        Route::post(
    '/invitations/import',
    [InvitationImportController::class, 'store']
)->name('tutor.invitations.import');

        Route::delete(
            '/invitations/{invitation}',
            [TutorStudentController::class, 'destroyInvitation']
        )
            ->whereNumber('invitation')
            ->name('tutor.invitations.destroy');


        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments', [PaymentController::class, 'tutorIndex'])
            ->name('tutor.payments');

        Route::patch(
            '/payments/billing/{relationship}',
            [PaymentController::class, 'updateBilling']
        )
            ->whereNumber('relationship')
            ->name('tutor.payments.billing.update');


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [TutorSettingController::class, 'edit'])
            ->name('tutor.settings.edit');

        Route::patch('/settings', [TutorSettingController::class, 'update'])
            ->name('tutor.settings.update');
    });


/*
|--------------------------------------------------------------------------
| Student
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->group(function () {

        Route::get('/dashboard', [StudentController::class, 'dashboard'])
            ->name('student.dashboard');

        Route::post(
            '/invitations/redeem',
            [StudentController::class, 'redeemInvitation']
        )
            ->name('student.invitations.redeem');

        Route::get('/assignments', [StudentController::class, 'assignments'])
            ->name('student.assignments');

        Route::post(
            '/assignments/{assignment}/submission',
            [SubmissionController::class, 'store']
        )
            ->whereNumber('assignment')
            ->name('student.assignments.submit');


        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments', [PaymentController::class, 'studentIndex'])
            ->name('student.payments');

        Route::post(
            '/payments/{payment}/checkout',
            [StripeCheckoutController::class, 'store']
        )
            ->whereNumber('payment')
            ->name('student.payments.checkout');
    });


/*
|--------------------------------------------------------------------------
| Stripe Webhook
|--------------------------------------------------------------------------
|
| Must stay OUTSIDE auth/tutor/student middleware.
| Stripe itself sends requests here.
|
*/

Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class, 'handle']
)->name('stripe.webhook');


require __DIR__.'/auth.php';