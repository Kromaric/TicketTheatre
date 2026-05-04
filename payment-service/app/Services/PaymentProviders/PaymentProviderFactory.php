<?php

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProviderInterface;
use App\Models\Payment;
use InvalidArgumentException;

class PaymentProviderFactory
{
    /**
     * Create a payment provider instance.
     *
     * @param string $provider Provider name (stripe, paypal)
     * @return PaymentProviderInterface
     * @throws InvalidArgumentException
     */
    public function make(string $provider): PaymentProviderInterface
    {
        return match($provider) {
            'stripe' => app(StripePaymentProvider::class),
            'paypal' => app(PayPalPaymentProvider::class),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }

    /**
     * Create a payment provider instance from a Payment model.
     *
     * @param Payment $payment
     * @return PaymentProviderInterface
     * @throws InvalidArgumentException
     */
    public function makeForPayment(Payment $payment): PaymentProviderInterface
    {
        return $this->make($payment->provider);
    }
}
