<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route de connexion
Route::post('/login', [AuthController::class, 'login']);

// Exemple de route protégée (pour vérifier le token)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

