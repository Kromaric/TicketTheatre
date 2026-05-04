<?php

namespace App\Contracts;

interface PaymentProviderInterface
{
    /**
     * Create a new payment.
     *
     * @param array $data Payment data including amount, currency, user_id, etc.
     * @return array Standardized response with provider_payment_id, status, etc.
     */
    public function createPayment(array $data): array;

    /**
     * Confirm/capture a payment.
     *
     * @param string $paymentId Provider-specific payment ID
     * @param array $data Additional data (e.g., payment_method_id for Stripe)
     * @return array Standardized response with status, provider_transaction_id, etc.
     */
    public function confirmPayment(string $paymentId, array $data = []): array;

    /**
     * Cancel a payment.
     *
     * @param string $paymentId Provider-specific payment ID
     * @return array Standardized response with status
     */
    public function cancelPayment(string $paymentId): array;

    /**
     * Refund a payment.
     *
     * @param string $paymentId Provider-specific payment ID
     * @param float|null $amount Amount to refund (null for full refund)
     * @param string|null $reason Reason for refund
     * @return array Standardized response with provider_transaction_id, status
     */
    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array;

    /**
     * Verify webhook signature.
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from webhook headers
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Get provider name.
     *
     * @return string Provider name (stripe, paypal, etc.)
     */
    public function getProviderName(): string;
}
