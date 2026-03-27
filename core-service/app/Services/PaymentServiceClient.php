<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentServiceClient
{
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = env('PAYMENT_SERVICE_URL', 'http://payment-service-app:80/api');
    }

    /**
     * Créer une session Stripe Checkout
     */
    public function createCheckoutSession(array $data): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/payments/checkout", [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'eur',
                    'user_id' => $data['user_id'],
                    'order_id' => $data['reservation_id'] ?? null,
                    'customer_email' => $data['customer_email'],
                    'description' => $data['description'] ?? 'Réservation TicketTheatre',
                    'success_url' => $data['success_url'],
                    'cancel_url' => $data['cancel_url'],
                    'metadata' => $data['metadata'] ?? [],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Payment Service Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment service error',
            ];
        } catch (\Exception $e) {
            Log::error('Payment Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    /**
     * Créer un payment intent
     */
    public function createPaymentIntent(array $data): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/payments", [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'eur',
                    'user_id' => $data['user_id'],
                    'order_id' => $data['reservation_id'] ?? null,
                    'customer_email' => $data['customer_email'],
                    'description' => $data['description'] ?? 'Réservation TicketTheatre',
                    'metadata' => $data['metadata'] ?? [],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Payment Service Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment service error',
            ];
        } catch (\Exception $e) {
            Log::error('Payment Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    /**
     * Récupérer un paiement
     */
    public function getPayment(int $paymentId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Payment Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment(string $paymentIntentId, string $paymentMethodId): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/payments/{$paymentIntentId}/confirm", [
                    'payment_method_id' => $paymentMethodId,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Payment confirmation failed',
            ];
        } catch (\Exception $e) {
            Log::error('Payment Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    /**
     * Annuler un paiement
     */
    public function cancelPayment(string $paymentIntentId): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/payments/{$paymentIntentId}/cancel");

            return [
                'success' => $response->successful(),
                'data' => $response->json('data'),
            ];
        } catch (\Exception $e) {
            Log::error('Payment Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    /**
     * Rembourser un paiement
     */
    public function refundPayment(int $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        try {
            $data = [];
            if ($amount) $data['amount'] = $amount;
            if ($reason) $data['reason'] = $reason;

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/payments/{$paymentId}/refund", $data);

            return [
                'success' => $response->successful(),
                'data' => $response->json('data'),
            ];
        } catch (\Exception $e) {
            Log::error('Payment Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable',
            ];
        }
    }

    /**
     * Récupérer les paiements d'un utilisateur
     */
    public function getUserPayments(int $userId): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/payments/user/{$userId}");

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Payment Service Error: ' . $e->getMessage());
            return [];
        }
    }
}
