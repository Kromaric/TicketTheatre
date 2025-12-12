<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\Request;

class HallController extends Controller
{
    /**
     * Display a listing of halls.
     */
    public function index(Request $request)
    {
        $query = Hall::query();

        // Filtrer par statut actif
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $halls = $query->withCount('seances')->get();

        return response()->json([
            'success' => true,
            'data' => $halls
        ]);
    }

    /**
     * Store a newly created hall.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image_url' => 'nullable|url|max:500',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
        ]);

        $hall = Hall::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Salle créée avec succès',
            'data' => $hall
        ], 201);
    }

    /**
     * Display the specified hall.
     */
    public function show(Hall $hall)
    {
        $hall->load(['seances' => function ($query) {
            $query->where('date_seance', '>=', now())
                  ->orderBy('date_seance', 'asc')
                  ->with('spectacle');
        }]);

        return response()->json([
            'success' => true,
            'data' => $hall
        ]);
    }

    /**
     * Update the specified hall.
     */
    public function update(Request $request, Hall $hall)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'image_url' => 'nullable|url|max:500',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
        ]);

        $hall->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Salle mise à jour avec succès',
            'data' => $hall
        ]);
    }

    /**
     * Remove the specified hall.
     */
    public function destroy(Hall $hall)
    {
        // Vérifier si la salle a des séances à venir
        $upcomingSeances = $hall->seances()->where('date_seance', '>=', now())->count();
        
        if ($upcomingSeances > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une salle avec des séances à venir'
            ], 422);
        }

        $hall->delete();

        return response()->json([
            'success' => true,
            'message' => 'Salle supprimée avec succès'
        ]);
    }

    /**
     * Get available halls for a specific date/time range.
     */
    public function getAvailable(Request $request)
    {
        $validated = $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
        ]);

        // Trouver les salles qui n'ont pas de séances dans cette plage
        $unavailableHallIds = \App\Models\Seance::whereBetween('date_seance', [
            $validated['date_start'],
            $validated['date_end']
        ])->pluck('hall_id')->unique();

        $availableHalls = Hall::where('is_active', true)
            ->whereNotIn('id', $unavailableHallIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $availableHalls
        ]);
    }
}
