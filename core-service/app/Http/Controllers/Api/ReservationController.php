<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Seance;
use App\Services\PaymentServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'seance.spectacle', 'seance.hall']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('booking_reference')) {
            $query->where('booking_reference', 'like', '%' . $request->booking_reference . '%');
        }

        $reservations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'seance_id' => 'required|exists:seances,id',
            'quantity' => 'required|integer|min:1|max:10',
            'seats' => 'nullable|array',
            'seats.*' => 'string|max:10',
        ]);

        $seance = Seance::findOrFail($validated['seance_id']);

        if ($seance->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Cette séance n\'est pas disponible'
            ], 422);
        }

        if ($seance->date_seance < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette séance est déjà passée'
            ], 422);
        }

        $bookedSeats = $seance->reservations()
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        $remainingSeats = $seance->available_seats - $bookedSeats;

        if ($validated['quantity'] > $remainingSeats) {
            return response()->json([
                'success' => false,
                'message' => 'Pas assez de places disponibles. Places restantes : ' . $remainingSeats
            ], 422);
        }

        $totalPrice = $seance->price * $validated['quantity'];
        $bookingReference = 'TH-' . date('Y') . '-' . strtoupper(Str::random(6));

        $reservation = Reservation::create([
            'user_id' => $validated['user_id'],
            'seance_id' => $validated['seance_id'],
            'booking_reference' => $bookingReference,
            'seats' => $validated['seats'] ?? null,
            'quantity' => $validated['quantity'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        $reservation->load(['seance.spectacle', 'seance.hall', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès',
            'data' => $reservation
        ], 201);
    }

    public function initiatePayment(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
        ]);

        if ($reservation->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a déjà été traitée'
            ], 422);
        }

        if ($reservation->expires_at && $reservation->expires_at < now()) {
            $reservation->update(['status' => 'expired']);
            
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a expiré'
            ], 422);
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        
        Log::info('Initiate payment', [
            'reservation_id' => $reservation->id,
            'amount' => $reservation->total_price,
            'frontend_url' => $frontendUrl
        ]);
        
        $paymentClient = new PaymentServiceClient();
        $result = $paymentClient->createCheckoutSession([
            'amount' => $reservation->total_price,
            'currency' => 'eur',
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'customer_email' => $validated['customer_email'],
            'description' => "Réservation {$reservation->booking_reference}",
            'success_url' => "{$frontendUrl}/confirmation-paiement/{$reservation->id}?session_id={{CHECKOUT_SESSION_ID}}",
            'cancel_url' => "{$frontendUrl}/paiement/{$reservation->id}?canceled=true",
            'metadata' => [
                'reservation_id' => (string)$reservation->id,
                'booking_reference' => $reservation->booking_reference,
                'seance_id' => (string)$reservation->seance_id,
                'quantity' => (string)$reservation->quantity,
            ],
        ]);

        Log::info('Payment service response', ['result' => $result]);

        if (!$result['success']) {
            Log::error('Payment creation failed', ['error' => $result['error']]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);
        }

        $paymentData = $result['data']['data'];
        $reservation->update([
            'payment_id' => $paymentData['id'],
        ]);

        $reservation->load(['seance.spectacle', 'seance.hall', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Session de paiement créée',
            'data' => [
                'reservation' => $reservation,
                'payment' => $paymentData,
                'checkout_url' => $result['data']['checkout_url'],
            ]
        ]);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'seance.spectacle.category', 'seance.hall']);

        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,cancelled,expired',
            'payment_status' => 'sometimes|in:pending,paid,refunded,failed',
            'payment_id' => 'nullable|string|max:255',
            'cancellation_reason' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'confirmed') {
            $validated['confirmed_at'] = now();
        }

        if (isset($validated['status']) && $validated['status'] === 'cancelled') {
            $validated['cancelled_at'] = now();
        }

        $reservation->update($validated);
        $reservation->load(['user', 'seance.spectacle', 'seance.hall']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation mise à jour avec succès',
            'data' => $reservation
        ]);
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation est déjà annulée'
            ], 422);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        $reservation->load(['user', 'seance.spectacle', 'seance.hall']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée avec succès',
            'data' => $reservation
        ]);
    }

    public function confirmPayment(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'payment_id' => 'required|string|max:255',
        ]);

        if ($reservation->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a déjà été traitée'
            ], 422);
        }

        if ($reservation->expires_at && $reservation->expires_at < now()) {
            $reservation->update(['status' => 'expired']);
            
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a expiré'
            ], 422);
        }

        $reservation->update([
            'payment_status' => 'paid',
            'payment_id' => $validated['payment_id'],
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $reservation->load(['user', 'seance.spectacle', 'seance.hall']);

        return response()->json([
            'success' => true,
            'message' => 'Paiement confirmé avec succès',
            'data' => $reservation
        ]);
    }

    public function userReservations($userId)
    {
        $reservations = Reservation::with(['seance.spectacle', 'seance.hall'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    public function getByReference($reference)
    {
        $reservation = Reservation::with(['user', 'seance.spectacle', 'seance.hall'])
            ->where('booking_reference', $reference)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }
}
