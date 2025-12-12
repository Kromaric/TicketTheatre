<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HallController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SeanceController;
use App\Http\Controllers\Api\SpectacleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Support\Facades\Route;

// Route essentielle appelée par le Auth Service (DEPRECATED - plus nécessaire)
Route::post('/validate-credentials', [ValidationController::class, 'validateCredentials']);

// Route pour récupérer un utilisateur
Route::get('/users/{id}', [UserController::class, 'show']);

// Routes publiques (sans authentification)
Route::prefix('public')->group(function () {
    // Catégories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Spectacles
    Route::get('/spectacles', [SpectacleController::class, 'index']);
    Route::get('/spectacles/upcoming', [SpectacleController::class, 'upcoming']);
    Route::get('/spectacles/{spectacle}', [SpectacleController::class, 'show']);

    // Séances
    Route::get('/seances', [SeanceController::class, 'index']);
    Route::get('/seances/{seance}', [SeanceController::class, 'show']);
    Route::get('/seances/{seance}/available-seats', [SeanceController::class, 'availableSeats']);

    // Salles
    Route::get('/halls', [HallController::class, 'index']);
    Route::get('/halls/{hall}', [HallController::class, 'show']);

    // Réservation par référence (pour les clients)
    Route::get('/reservations/reference/{reference}', [ReservationController::class, 'getByReference']);
});

// Routes protégées (nécessitent authentification Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Catégories (Admin seulement)
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

    // Salles (Admin seulement)
    Route::apiResource('halls', HallController::class)->except(['index', 'show']);
    Route::get('halls/available', [HallController::class, 'getAvailable']);

    // Spectacles (Admin seulement)
    Route::apiResource('spectacles', SpectacleController::class)->except(['index', 'show']);

    // Séances (Admin seulement)
    Route::apiResource('seances', SeanceController::class)->except(['index', 'show']);

    // Réservations
    Route::apiResource('reservations', ReservationController::class);
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('reservations/{reservation}/confirm-payment', [ReservationController::class, 'confirmPayment']);
    Route::get('users/{userId}/reservations', [ReservationController::class, 'userReservations']);
});
