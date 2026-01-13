<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Gérer le webhook du payment service
     */
    public function handlePaymentWebhook(Request $request)
    {
        // Log pour debug
        Log::info('Payment Webhook Received', $request->all());

        $event = $request->input('event');
        $paymentData = $request->input('payment');

        if (!$event || !$paymentData) {
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

        // Récupérer la réservation via le payment_id
        $reservation = Reservation::where('payment_id', $paymentData['id'])->first();

        if (!$reservation) {
            Log::warning('Reservation not found for payment', ['payment_id' => $paymentData['id']]);
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        // Traiter selon l'événement
        try {
            switch ($event) {
                case 'payment.succeeded':
                    $this->handlePaymentSucceeded($reservation, $paymentData);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($reservation, $paymentData);
                    break;

                case 'payment.refunded':
                    $this->handlePaymentRefunded($reservation, $paymentData);
                    break;

                case 'payment.canceled':
                    $this->handlePaymentCanceled($reservation, $paymentData);
                    break;

                default:
                    Log::info('Unhandled payment event', ['event' => $event]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing payment webhook', [
                'error' => $e->getMessage(),
                'event' => $event,
                'payment_id' => $paymentData['id']
            ]);

            return response()->json(['error' => 'Error processing webhook'], 500);
        }
    }

    /**
     * Traiter le succès d'un paiement
     */
    private function handlePaymentSucceeded(Reservation $reservation, array $paymentData)
    {
        $reservation->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        Log::info('Reservation confirmed', [
            'reservation_id' => $reservation->id,
            'booking_reference' => $reservation->booking_reference,
            'payment_id' => $paymentData['id']
        ]);

        // TODO: Envoyer email de confirmation au client
        // TODO: Envoyer notification push si applicable
    }

    /**
     * Traiter l'échec d'un paiement
     */
    private function handlePaymentFailed(Reservation $reservation, array $paymentData)
    {
        $reservation->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Échec du paiement',
        ]);

        Log::warning('Reservation payment failed', [
            'reservation_id' => $reservation->id,
            'booking_reference' => $reservation->booking_reference,
            'payment_id' => $paymentData['id']
        ]);

        // TODO: Envoyer email d'échec au client
    }

    /**
     * Traiter le remboursement d'un paiement
     */
    private function handlePaymentRefunded(Reservation $reservation, array $paymentData)
    {
        $reservation->update([
            'payment_status' => 'refunded',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Remboursement effectué',
        ]);

        Log::info('Reservation refunded', [
            'reservation_id' => $reservation->id,
            'booking_reference' => $reservation->booking_reference,
            'payment_id' => $paymentData['id'],
            'refund_amount' => $paymentData['amount'] ?? $reservation->total_price
        ]);

        // TODO: Envoyer email de remboursement au client
    }

    /**
     * Traiter l'annulation d'un paiement
     */
    private function handlePaymentCanceled(Reservation $reservation, array $paymentData)
    {
        $reservation->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Paiement annulé',
        ]);

        Log::info('Reservation canceled', [
            'reservation_id' => $reservation->id,
            'booking_reference' => $reservation->booking_reference,
            'payment_id' => $paymentData['id']
        ]);

        // TODO: Envoyer email d'annulation au client
    }
}
