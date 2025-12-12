<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Seance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        // Créer quelques réservations exemples
        $reservations = [
            // Réservation confirmée et payée
            [
                'user_id' => 2, // Jean Dupont
                'seance_id' => 1,
                'quantity' => 2,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_id' => 'PAY-' . Str::random(10),
                'confirmed_at' => now(),
            ],
            // Réservation en attente
            [
                'user_id' => 3, // Marie Martin
                'seance_id' => 2,
                'quantity' => 3,
                'status' => 'pending',
                'payment_status' => 'pending',
                'expires_at' => now()->addMinutes(15),
            ],
            // Réservation confirmée
            [
                'user_id' => 4, // Pierre Dubois
                'seance_id' => 3,
                'quantity' => 4,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_id' => 'PAY-' . Str::random(10),
                'confirmed_at' => now()->subDays(2),
            ],
            // Réservation annulée
            [
                'user_id' => 5, // Sophie Bernard
                'seance_id' => 4,
                'quantity' => 2,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'payment_id' => 'PAY-' . Str::random(10),
                'cancelled_at' => now()->subDay(),
                'cancellation_reason' => 'Empêchement de dernière minute',
            ],
            // Réservations supplémentaires confirmées
            [
                'user_id' => 2,
                'seance_id' => 5,
                'quantity' => 1,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_id' => 'PAY-' . Str::random(10),
                'confirmed_at' => now()->subDays(5),
            ],
            [
                'user_id' => 3,
                'seance_id' => 8,
                'quantity' => 2,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_id' => 'PAY-' . Str::random(10),
                'confirmed_at' => now()->subDays(3),
                'seats' => ['A12', 'A13'],
            ],
        ];

        foreach ($reservations as $reservationData) {
            $seance = Seance::find($reservationData['seance_id']);
            $totalPrice = $seance->price * $reservationData['quantity'];

            Reservation::create([
                'user_id' => $reservationData['user_id'],
                'seance_id' => $reservationData['seance_id'],
                'booking_reference' => 'TH-' . date('Y') . '-' . strtoupper(Str::random(6)),
                'seats' => $reservationData['seats'] ?? null,
                'quantity' => $reservationData['quantity'],
                'total_price' => $totalPrice,
                'status' => $reservationData['status'],
                'payment_status' => $reservationData['payment_status'],
                'payment_id' => $reservationData['payment_id'] ?? null,
                'expires_at' => $reservationData['expires_at'] ?? null,
                'confirmed_at' => $reservationData['confirmed_at'] ?? null,
                'cancelled_at' => $reservationData['cancelled_at'] ?? null,
                'cancellation_reason' => $reservationData['cancellation_reason'] ?? null,
            ]);
        }
    }
}
