<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use Illuminate\Http\Request;

class SeanceController extends Controller
{
    /**
     * Display a listing of seances.
     */
    public function index(Request $request)
    {
        $query = Seance::with(['spectacle', 'hall']);

        // Filtre par spectacle
        if ($request->filled('spectacle_id')) {
            $query->where('spectacle_id', $request->spectacle_id);
        }

        // Filtre par salle
        if ($request->filled('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->where('date_seance', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_seance', '<=', $request->date_to);
        }

        // Seulement les séances à venir
        if ($request->boolean('upcoming_only')) {
            $query->where('date_seance', '>=', now())
                  ->where('status', 'scheduled');
        }

        $seances = $query->orderBy('date_seance', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $seances
        ]);
    }

    /**
     * Store a newly created seance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'spectacle_id' => 'required|exists:spectacles,id',
            'hall_id' => 'required|exists:halls,id',
            'date_seance' => 'required|date|after:now',
            'available_seats' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'in:scheduled,cancelled,completed',
        ]);

        // Vérifier que la salle n'est pas déjà occupée à cette date
        $conflictingSeance = Seance::where('hall_id', $validated['hall_id'])
            ->where('date_seance', $validated['date_seance'])
            ->exists();

        if ($conflictingSeance) {
            return response()->json([
                'success' => false,
                'message' => 'Cette salle est déjà occupée à cette date et heure'
            ], 422);
        }

        // Vérifier que le nombre de places ne dépasse pas la capacité de la salle
        $hall = \App\Models\Hall::find($validated['hall_id']);
        if ($validated['available_seats'] > $hall->capacity) {
            return response()->json([
                'success' => false,
                'message' => 'Le nombre de places disponibles dépasse la capacité de la salle (' . $hall->capacity . ')'
            ], 422);
        }

        $seance = Seance::create($validated);
        $seance->load(['spectacle', 'hall']);

        return response()->json([
            'success' => true,
            'message' => 'Séance créée avec succès',
            'data' => $seance
        ], 201);
    }

    /**
     * Display the specified seance.
     */
    public function show(Seance $seance)
    {
        $seance->load(['spectacle.category', 'hall', 'reservations']);

        // Calculer les places restantes
        $bookedSeats = $seance->reservations()
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        $seance->remaining_seats = $seance->available_seats - $bookedSeats;

        return response()->json([
            'success' => true,
            'data' => $seance
        ]);
    }

    /**
     * Update the specified seance.
     */
    public function update(Request $request, Seance $seance)
    {
        $validated = $request->validate([
            'spectacle_id' => 'sometimes|exists:spectacles,id',
            'hall_id' => 'sometimes|exists:halls,id',
            'date_seance' => 'sometimes|date',
            'available_seats' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'status' => 'in:scheduled,cancelled,completed',
        ]);

        // Si changement de salle ou date, vérifier les conflits
        if (isset($validated['hall_id']) || isset($validated['date_seance'])) {
            $hallId = $validated['hall_id'] ?? $seance->hall_id;
            $dateSeance = $validated['date_seance'] ?? $seance->date_seance;

            $conflictingSeance = Seance::where('hall_id', $hallId)
                ->where('date_seance', $dateSeance)
                ->where('id', '!=', $seance->id)
                ->exists();

            if ($conflictingSeance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette salle est déjà occupée à cette date et heure'
                ], 422);
            }
        }

        // Vérifier la capacité si changement de salle
        if (isset($validated['hall_id'])) {
            $hall = \App\Models\Hall::find($validated['hall_id']);
            $availableSeats = $validated['available_seats'] ?? $seance->available_seats;
            
            if ($availableSeats > $hall->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le nombre de places disponibles dépasse la capacité de la salle (' . $hall->capacity . ')'
                ], 422);
            }
        }

        $seance->update($validated);
        $seance->load(['spectacle', 'hall']);

        return response()->json([
            'success' => true,
            'message' => 'Séance mise à jour avec succès',
            'data' => $seance
        ]);
    }

    /**
     * Remove the specified seance.
     */
    public function destroy(Seance $seance)
    {
        // Vérifier s'il y a des réservations confirmées
        $confirmedReservations = $seance->reservations()
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedReservations > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une séance avec des réservations confirmées'
            ], 422);
        }

        $seance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Séance supprimée avec succès'
        ]);
    }

    /**
     * Get available seats for a seance.
     */
    public function availableSeats(Seance $seance)
    {
        $bookedSeats = $seance->reservations()
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        $remainingSeats = $seance->available_seats - $bookedSeats;

        return response()->json([
            'success' => true,
            'data' => [
                'total_seats' => $seance->available_seats,
                'booked_seats' => $bookedSeats,
                'remaining_seats' => max(0, $remainingSeats),
                'is_available' => $remainingSeats > 0
            ]
        ]);
    }
}
