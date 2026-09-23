<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\TutorStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function tutorIndex(
        Request $request
    ): Response {
        $tutor =
            $request->user();

        $relationships =
            TutorStudent::query()
                ->where(
                    'tutor_id',
                    $tutor->id
                )
                ->with(
                    'student:id,name,email'
                )
                ->orderBy(
                    'student_id'
                )
                ->orderBy(
                    'subject'
                )
                ->get()
                ->map(
                    fn (
                        TutorStudent $relationship
                    ) => [
                        'id' =>
                            $relationship->id,

                        'student_name' =>
                            $relationship
                                ->student
                                ?->name,

                        'student_email' =>
                            $relationship
                                ->student
                                ?->email,

                        'subject' =>
                            $relationship
                                ->subject,

                        'lesson_price' =>
                            $relationship
                                ->lesson_price,

                        'billing_type' =>
                            $relationship
                                ->billing_type,
                    ]
                )
                ->values();

        $payments =
            Payment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'tutor_id',
                            $tutor->id
                        )
                )
                ->with([
                    'tutorStudent.student:id,name,email',
                ])
                ->latest()
                ->get()
                ->map(
                    fn (
                        Payment $payment
                    ) => [
                        'id' =>
                            $payment->id,

                        'student_name' =>
                            $payment
                                ->tutorStudent
                                ?->student
                                ?->name,

                        'student_email' =>
                            $payment
                                ->tutorStudent
                                ?->student
                                ?->email,

                        'subject' =>
                            $payment
                                ->tutorStudent
                                ?->subject,

                        'amount' =>
                            $payment->amount,

                        'currency' =>
                            strtoupper(
                                $payment->currency
                            ),

                        'period' =>
                            $payment->period,

                        'billing_type' =>
                            $payment
                                ->billing_type,

                        'lesson_count' =>
                            $payment
                                ->lesson_count,

                        'status' =>
                            $payment->status,

                        'payment_method' =>
                            $payment
                                ->payment_method,

                        'paid_at' =>
                            $payment
                                ->paid_at
                                ?->toIso8601String(),

                        'created_at' =>
                            $payment
                                ->created_at
                                ?->toIso8601String(),
                    ]
                )
                ->values();

        return Inertia::render(
            'Tutor/Payments',
            [
                'relationships' =>
                    $relationships,

                'payments' =>
                    $payments,
            ]
        );
    }

    public function updateBilling(
        Request $request,
        TutorStudent $relationship
    ): RedirectResponse {
        abort_unless(
            $relationship->tutor_id ===
                $request->user()->id,
            403
        );

        $validated =
            $request->validate([
                'lesson_price' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999.99',
                ],

                'billing_type' => [
                    'required',
                    'string',
                    'in:per_lesson,monthly',
                ],
            ]);

        $relationship->update([
            'lesson_price' =>
                $validated[
                    'lesson_price'
                ],

            'billing_type' =>
                $validated[
                    'billing_type'
                ],
        ]);

        Cache::forget(
            "tutor_dashboard_{$relationship->tutor_id}"
        );

        Cache::forget(
            "student_dashboard_{$relationship->student_id}"
        );

        return redirect()
            ->route(
                'tutor.payments'
            )
            ->with(
                'success',
                'Billing settings updated.'
            );
    }

    public function studentIndex(
        Request $request
    ): Response {
        $student =
            $request->user();

        $payments =
            Payment::query()
                ->whereHas(
                    'tutorStudent',
                    fn ($query) =>
                        $query->where(
                            'student_id',
                            $student->id
                        )
                )
                ->with([
                    'tutorStudent.tutor:id,name,email',
                ])
                ->latest()
                ->get()
                ->map(
                    fn (
                        Payment $payment
                    ) => [
                        'id' =>
                            $payment->id,

                        'tutor_name' =>
                            $payment
                                ->tutorStudent
                                ?->tutor
                                ?->name,

                        'tutor_email' =>
                            $payment
                                ->tutorStudent
                                ?->tutor
                                ?->email,

                        'subject' =>
                            $payment
                                ->tutorStudent
                                ?->subject,

                        'amount' =>
                            $payment->amount,

                        'currency' =>
                            strtoupper(
                                $payment->currency
                            ),

                        'period' =>
                            $payment->period,

                        'billing_type' =>
                            $payment
                                ->billing_type,

                        'lesson_count' =>
                            $payment
                                ->lesson_count,

                        'status' =>
                            $payment->status,

                        'payment_method' =>
                            $payment
                                ->payment_method,

                        'paid_at' =>
                            $payment
                                ->paid_at
                                ?->toIso8601String(),

                        'created_at' =>
                            $payment
                                ->created_at
                                ?->toIso8601String(),
                    ]
                )
                ->values();

        return Inertia::render(
            'Student/Payments',
            [
                'payments' =>
                    $payments,
            ]
        );
    }
}