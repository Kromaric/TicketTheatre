<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spectacle;
use Illuminate\Http\Request;

class SpectacleController extends Controller
{
    /**
     * Display a listing of spectacles.
     */
    public function index(Request $request)
    {
        $query = Spectacle::with('category');

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par publication
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Recherche par titre
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $spectacles = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $spectacles
        ]);
    }

    /**
     * Store a newly created spectacle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'language' => 'string|max:10',
            'age_restriction' => 'nullable|integer|min:0|max:18',
            'category_id' => 'nullable|exists:categories,id',
            'director' => 'nullable|string|max:255',
            'actors' => 'nullable|array',
            'actors.*' => 'string|max:255',
            'is_published' => 'boolean',
            'status' => 'in:upcoming,ongoing,finished,cancelled',
        ]);

        $spectacle = Spectacle::create($validated);
        $spectacle->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Spectacle créé avec succès',
            'data' => $spectacle
        ], 201);
    }

    /**
     * Display the specified spectacle.
     */
    public function show(Spectacle $spectacle)
    {
        $spectacle->load([
            'category',
            'seances' => function ($query) {
                $query->where('date_seance', '>=', now())
                      ->where('status', 'scheduled')
                      ->orderBy('date_seance', 'asc')
                      ->with('hall');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $spectacle
        ]);
    }

    /**
     * Update the specified spectacle.
     */
    public function update(Request $request, Spectacle $spectacle)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'base_price' => 'sometimes|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'language' => 'sometimes|string|max:10',
            'age_restriction' => 'nullable|integer|min:0|max:18',
            'category_id' => 'nullable|exists:categories,id',
            'director' => 'nullable|string|max:255',
            'actors' => 'nullable|array',
            'actors.*' => 'string|max:255',
            'is_published' => 'boolean',
            'status' => 'in:upcoming,ongoing,finished,cancelled',
        ]);

        $spectacle->update($validated);
        $spectacle->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Spectacle mis à jour avec succès',
            'data' => $spectacle
        ]);
    }

    /**
     * Remove the specified spectacle.
     */
    public function destroy(Spectacle $spectacle)
    {
        // Vérifier s'il y a des séances à venir
        $upcomingSeances = $spectacle->seances()
            ->where('date_seance', '>=', now())
            ->count();

        if ($upcomingSeances > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un spectacle avec des séances à venir'
            ], 422);
        }

        $spectacle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Spectacle supprimé avec succès'
        ]);
    }

    /**
     * Get upcoming spectacles.
     */
    public function upcoming()
    {
        $spectacles = Spectacle::with(['category', 'seances' => function ($query) {
                $query->where('date_seance', '>=', now())
                      ->where('status', 'scheduled')
                      ->orderBy('date_seance', 'asc')
                      ->limit(5);
            }])
            ->where('is_published', true)
            ->where('status', 'upcoming')
            ->whereHas('seances', function ($query) {
                $query->where('date_seance', '>=', now())
                      ->where('status', 'scheduled');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $spectacles
        ]);
    }
}
