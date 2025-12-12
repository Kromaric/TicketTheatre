<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route protégée pour récupérer l'utilisateur connecté
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'me']);
