<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{
    public function store(
        Request $request,
        Payment $payment
    ) {
        $payment->load([
            'tutorStudent.student:id,name,email',
            'tutorStudent.tutor:id,name,email',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $payment
                ->tutorStudent
                ?->student_id ===
            $request
                ->user()
                ->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Payment must still be pending
        |--------------------------------------------------------------------------
        */

        if (
            $payment->status !==
            'pending'
        ) {
            throw ValidationException::withMessages([
                'payment' =>
                    'This payment has already been completed.',
            ]);
        }

        if (
            (float)
            $payment->amount <=
            0
        ) {
            throw ValidationException::withMessages([
                'payment' =>
                    'This payment has an invalid amount.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Stripe configuration
        |--------------------------------------------------------------------------
        */

        $secret =
            config(
                'services.stripe.secret'
            );

        if (! $secret) {
            throw ValidationException::withMessages([
                'payment' =>
                    'Stripe is not configured.',
            ]);
        }

        $stripe =
            new StripeClient(
                $secret
            );

        /*
        |--------------------------------------------------------------------------
        | Amount in smallest currency unit
        |--------------------------------------------------------------------------
        |
        | 100.00 PLN -> 10000
        |
        */

        $amountInCents =
            (int) round(
                (float)
                $payment->amount
                * 100
            );

        $subject =
            $payment
                ->tutorStudent
                ?->subject
            ?? 'Lesson';

        $period =
            $payment->period
            ?? 'Payment';

        /*
        |--------------------------------------------------------------------------
        | Create Stripe Checkout Session
        |--------------------------------------------------------------------------
        */

        $session =
            $stripe
                ->checkout
                ->sessions
                ->create([
                    'mode' =>
                        'payment',

                    'client_reference_id' =>
                        (string)
                        $payment->id,

                    'customer_email' =>
                        $request
                            ->user()
                            ->email,

                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' =>
                                    strtolower(
                                        $payment
                                            ->currency
                                        ?? 'pln'
                                    ),

                                'product_data' => [
                                    'name' =>
                                        $subject,

                                    'description' =>
                                        $period,
                                ],

                                'unit_amount' =>
                                    $amountInCents,
                            ],

                            'quantity' =>
                                1,
                        ],
                    ],

                    'metadata' => [
                        'payment_id' =>
                            (string)
                            $payment->id,
                    ],

                    'payment_intent_data' => [
                        'metadata' => [
                            'payment_id' =>
                                (string)
                                $payment->id,
                        ],
                    ],

                    'success_url' =>
                        route(
                            'student.payments',
                            absolute: true
                        )
                        . '?checkout=success',

                    'cancel_url' =>
                        route(
                            'student.payments',
                            absolute: true
                        )
                        . '?checkout=cancelled',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Remember current Stripe session
        |--------------------------------------------------------------------------
        */

        $payment->update([
            'stripe_checkout_session_id' =>
                $session->id,
        ]);

        return Inertia::location(
            $session->url
        );
    }
}