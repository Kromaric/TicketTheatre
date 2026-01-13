<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook events.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Store webhook event
        $webhookEvent = WebhookEvent::create([
            'stripe_event_id' => $event->id,
            'type' => $event->type,
            'payload' => json_decode($payload, true),
            'processed' => false,
        ]);

        try {
            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                case 'payment_intent.canceled':
                    $this->handlePaymentIntentCanceled($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleChargeRefunded($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleDisputeCreated($event->data->object);
                    break;

                default:
                    Log::info('Unhandled webhook event type: ' . $event->type);
            }

            $webhookEvent->markAsProcessed();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage(), [
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);

            $webhookEvent->markAsFailed($e->getMessage());

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle payment intent succeeded event.
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment && $payment->status !== 'succeeded') {
            $payment->markAsSucceeded();

            // Update transaction
            $payment->transactions()
                ->where('type', 'charge')
                ->where('stripe_transaction_id', $paymentIntent->id)
                ->update(['status' => 'succeeded']);

            Log::info('Payment succeeded', ['payment_id' => $payment->id]);

            // Notify Core Service
            $this->notifyCoreService($payment, 'payment.succeeded');
        }
    }

    /**
     * Handle payment intent failed event.
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentFailed(object $paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->markAsFailed();

            // Update transaction
            $payment->transactions()
                ->where('type', 'charge')
                ->where('stripe_transaction_id', $paymentIntent->id)
                ->update(['status' => 'failed']);

            Log::info('Payment failed', [
                'payment_id' => $payment->id,
                'error' => $paymentIntent->last_payment_error ?? 'Unknown error',
            ]);

            // Notify Core Service
            $this->notifyCoreService($payment, 'payment.failed');
        }
    }

    /**
     * Handle payment intent canceled event.
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentCanceled(object $paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update(['status' => 'canceled']);

            Log::info('Payment canceled', ['payment_id' => $payment->id]);

            // Notify Core Service
            $this->notifyCoreService($payment, 'payment.canceled');
        }
    }

    /**
     * Handle charge refunded event.
     *
     * @param object $charge
     * @return void
     */
    protected function handleChargeRefunded(object $charge): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if ($payment) {
            $payment->markAsRefunded();

            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
                'refund_amount' => $charge->amount_refunded / 100,
            ]);

            // Notify Core Service
            $this->notifyCoreService($payment, 'payment.refunded');
        }
    }

    /**
     * Handle dispute created event.
     *
     * @param object $dispute
     * @return void
     */
    protected function handleDisputeCreated(object $dispute): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $dispute->payment_intent)->first();

        if ($payment) {
            // Create a dispute transaction
            $payment->transactions()->create([
                'stripe_transaction_id' => $dispute->id,
                'type' => 'dispute',
                'amount' => $dispute->amount / 100,
                'currency' => $dispute->currency,
                'status' => 'pending',
                'reason' => $dispute->reason ?? null,
                'metadata' => [
                    'evidence_due_by' => $dispute->evidence_details->due_by ?? null,
                ],
            ]);

            Log::warning('Dispute created', [
                'payment_id' => $payment->id,
                'dispute_id' => $dispute->id,
                'reason' => $dispute->reason,
            ]);
        }
    }

    /**
     * Notify Core Service about payment events
     *
     * @param Payment $payment
     * @param string $event
     * @return void
     */
    protected function notifyCoreService(Payment $payment, string $event): void
    {
        try {
            $coreWebhookUrl = env('CORE_SERVICE_WEBHOOK_URL');
            
            if (!$coreWebhookUrl) {
                Log::warning('Core webhook URL not configured');
                return;
            }

            \Illuminate\Support\Facades\Http::timeout(10)->post($coreWebhookUrl, [
                'event' => $event,
                'payment' => [
                    'id' => $payment->id,
                    'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'user_id' => $payment->user_id,
                    'order_id' => $payment->order_id,
                    'customer_email' => $payment->customer_email,
                    'metadata' => $payment->metadata,
                ],
            ]);

            Log::info('Core service notified', [
                'event' => $event,
                'payment_id' => $payment->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify core service: ' . $e->getMessage(), [
                'event' => $event,
                'payment_id' => $payment->id
            ]);
        }
    }
}
