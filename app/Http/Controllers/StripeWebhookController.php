<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(
        Request $request
    ): JsonResponse {
        $webhookSecret =
            (string) config(
                'services.stripe.webhook_secret'
            );

        if ($webhookSecret === '') {
            return response()->json(
                [
                    'message' =>
                        'Stripe webhook secret is not configured.',
                ],
                500
            );
        }

        $payload =
            $request->getContent();

        $signature =
            $request->header(
                'Stripe-Signature'
            );

        if (! $signature) {
            return response()->json(
                [
                    'message' =>
                        'Missing Stripe signature.',
                ],
                400
            );
        }

        try {
            $event =
                Webhook::constructEvent(
                    $payload,
                    $signature,
                    $webhookSecret
                );
        } catch (
            UnexpectedValueException |
            SignatureVerificationException
            $exception
        ) {
            return response()->json(
                [
                    'message' =>
                        'Invalid webhook.',
                ],
                400
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Events that may represent a successful Checkout payment
        |--------------------------------------------------------------------------
        */

        if (
            ! in_array(
                $event->type,
                [
                    'checkout.session.completed',
                    'checkout.session.async_payment_succeeded',
                ],
                true
            )
        ) {
            return response()->json([
                'received' => true,
            ]);
        }

        $session =
            $event
                ->data
                ->object;

        /*
        |--------------------------------------------------------------------------
        | Do not mark unpaid sessions as paid
        |--------------------------------------------------------------------------
        */

        if (
            ($session->payment_status ?? null)
            !==
            'paid'
        ) {
            return response()->json([
                'received' => true,
            ]);
        }

        $paymentId =
            $session
                ->metadata
                ->payment_id
            ??
            $session
                ->client_reference_id
            ??
            null;

        if (! $paymentId) {
            return response()->json([
                'received' => true,
            ]);
        }

        DB::transaction(
            function () use (
                $paymentId,
                $session
            ): void {
                $payment =
                    Payment::query()
                        ->lockForUpdate()
                        ->find(
                            (int) $paymentId
                        );

                /*
                |--------------------------------------------------------------------------
                | Payment may already have been deleted from Calendar
                |--------------------------------------------------------------------------
                */

                if (! $payment) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Idempotency
                |--------------------------------------------------------------------------
                |
                | Stripe can send the same event more than once.
                |
                */

                if (
                    $payment->status ===
                    'paid'
                ) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Verify amount and currency
                |--------------------------------------------------------------------------
                */

                $expectedAmount =
                    (int) round(
                        (float)
                        $payment->amount
                        * 100
                    );

                $stripeAmount =
                    (int) (
                        $session
                            ->amount_total
                        ?? -1
                    );

                $expectedCurrency =
                    strtolower(
                        $payment->currency
                        ?? 'pln'
                    );

                $stripeCurrency =
                    strtolower(
                        (string) (
                            $session
                                ->currency
                            ?? ''
                        )
                    );

                if (
                    $expectedAmount !==
                    $stripeAmount
                    ||
                    $expectedCurrency !==
                    $stripeCurrency
                ) {
                    Log::warning(
                        'Stripe payment amount mismatch.',
                        [
                            'payment_id' =>
                                $payment->id,

                            'expected_amount' =>
                                $expectedAmount,

                            'stripe_amount' =>
                                $stripeAmount,

                            'expected_currency' =>
                                $expectedCurrency,

                            'stripe_currency' =>
                                $stripeCurrency,
                        ]
                    );

                    return;
                }

                $paymentIntentId =
                    null;

                if (
                    is_string(
                        $session
                            ->payment_intent
                    )
                ) {
                    $paymentIntentId =
                        $session
                            ->payment_intent;
                } elseif (
                    isset(
                        $session
                            ->payment_intent
                            ->id
                    )
                ) {
                    $paymentIntentId =
                        $session
                            ->payment_intent
                            ->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Mark payment as paid
                |--------------------------------------------------------------------------
                */

                $payment->update([
                    'status' =>
                        'paid',

                    'payment_method' =>
                        'stripe',

                    'stripe_checkout_session_id' =>
                        $session->id,

                    'stripe_payment_intent_id' =>
                        $paymentIntentId,

                    'paid_at' =>
                        now(),
                ]);
            }
        );

        return response()->json([
            'received' => true,
        ]);
    }
}