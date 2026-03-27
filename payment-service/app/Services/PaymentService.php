<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session
     *
     * @param array $data
     * @return array
     * @throws ApiErrorException
     */
    public function createCheckoutSession(array $data): array
    {
        $amount = (int)($data['amount'] * 100); // Convert to cents

        // Convert metadata values to strings
        $metadata = [];
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            foreach ($data['metadata'] as $key => $value) {
                $metadata[$key] = is_array($value) || is_object($value)
                    ? json_encode($value)
                    : (string)$value;
            }
        }

        // Create checkout session
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $data['currency'] ?? 'eur',
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => $data['description'] ?? 'Réservation',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'customer_email' => $data['customer_email'] ?? null,
            'metadata' => $metadata,
        ]);

        // Store payment in database
        $payment = Payment::create([
            'stripe_payment_intent_id' => $session->payment_intent,
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'eur',
            'status' => 'pending',
            'customer_email' => $data['customer_email'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        // Create initial transaction record
        Transaction::create([
            'payment_id' => $payment->id,
            'stripe_transaction_id' => $session->id,
            'type' => 'charge',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'eur',
            'status' => 'pending',
        ]);

        return [
            'payment' => $payment,
            'session_id' => $session->id,
            'checkout_url' => $session->url,
        ];
    }

    /**
     * Create a payment intent with Stripe.
     *
     * @param array $data
     * @return array
     * @throws ApiErrorException
     */
    public function createPaymentIntent(array $data): array
    {
        $amount = (int)($data['amount'] * 100); // Convert to cents

        // Convert metadata values to strings (Stripe only accepts string values)
        $metadata = [];
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            foreach ($data['metadata'] as $key => $value) {
                $metadata[$key] = is_array($value) || is_object($value)
                    ? json_encode($value)
                    : (string)$value;
            }
        }

        // Create payment intent with Stripe
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'eur',
            'payment_method_types' => ['card'],
            'description' => $data['description'] ?? null,
            'metadata' => $metadata,
        ]);

        // Store payment in database
        $payment = Payment::create([
            'stripe_payment_intent_id' => $paymentIntent->id,
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'eur',
            'status' => 'pending',
            'customer_email' => $data['customer_email'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        // Create initial transaction record
        Transaction::create([
            'payment_id' => $payment->id,
            'stripe_transaction_id' => $paymentIntent->id,
            'type' => 'charge',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'eur',
            'status' => 'pending',
        ]);

        return [
            'payment' => $payment,
            'client_secret' => $paymentIntent->client_secret,
        ];
    }

    /**
     * Confirm a payment intent.
     *
     * @param string $paymentIntentId
     * @param string $paymentMethodId
     * @return array
     * @throws ApiErrorException
     */
    public function confirmPayment(string $paymentIntentId, string $paymentMethodId): array
    {
        $paymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId, [
            'payment_method' => $paymentMethodId,
        ]);

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->firstOrFail();

        if ($paymentIntent->status === 'succeeded') {
            $payment->markAsSucceeded();
            $payment->update(['payment_method' => $paymentMethodId]);

            // Update transaction status
            $payment->transactions()
                ->where('type', 'charge')
                ->where('stripe_transaction_id', $paymentIntentId)
                ->update(['status' => 'succeeded']);
        }

        return [
            'payment' => $payment->fresh(),
            'stripe_payment_intent' => $paymentIntent,
        ];
    }

    /**
     * Retrieve a payment intent from Stripe.
     *
     * @param string $paymentIntentId
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId)
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Cancel a payment intent.
     *
     * @param string $paymentIntentId
     * @return Payment
     * @throws ApiErrorException
     */
    public function cancelPayment(string $paymentIntentId): Payment
    {
        $this->stripe->paymentIntents->cancel($paymentIntentId);

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->firstOrFail();
        $payment->update(['status' => 'canceled']);

        // Update transaction status
        $payment->transactions()
            ->where('type', 'charge')
            ->where('stripe_transaction_id', $paymentIntentId)
            ->update(['status' => 'failed']);

        return $payment;
    }

    /**
     * Refund a payment.
     *
     * @param Payment $payment
     * @param float|null $amount
     * @param string|null $reason
     * @return array
     * @throws ApiErrorException
     */
    public function refundPayment(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        $refundAmount = $amount ? (int)($amount * 100) : null;

        $refundData = [
            'payment_intent' => $payment->stripe_payment_intent_id,
        ];

        if ($refundAmount) {
            $refundData['amount'] = $refundAmount;
        }

        if ($reason) {
            $refundData['reason'] = $reason;
        }

        $refund = $this->stripe->refunds->create($refundData);

        // Create refund transaction
        $transaction = Transaction::create([
            'payment_id' => $payment->id,
            'stripe_transaction_id' => $refund->id,
            'type' => $amount ? 'partial_refund' : 'refund',
            'amount' => $amount ?? $payment->amount,
            'currency' => $payment->currency,
            'status' => $refund->status === 'succeeded' ? 'succeeded' : 'pending',
            'reason' => $reason,
        ]);

        // Update payment status if full refund
        if (!$amount || $amount >= $payment->amount) {
            $payment->markAsRefunded();
        }

        return [
            'payment' => $payment->fresh(),
            'transaction' => $transaction,
            'stripe_refund' => $refund,
        ];
    }

    /**
     * Get payment by ID.
     *
     * @param int $paymentId
     * @return Payment
     */
    public function getPayment(int $paymentId): Payment
    {
        return Payment::with('transactions')->findOrFail($paymentId);
    }

    /**
     * Get payments for a user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPayments(int $userId)
    {
        return Payment::with('transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payment by Stripe payment intent ID.
     *
     * @param string $paymentIntentId
     * @return Payment
     */
    public function getPaymentByStripeId(string $paymentIntentId): Payment
    {
        return Payment::with('transactions')
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->firstOrFail();
    }
}
